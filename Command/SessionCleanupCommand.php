<?php

namespace Ma27\ApiKeyAuthenticationBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\OnApiKeyCleanupErrorEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnBeforeSessionCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnSuccessfulCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Command which is responsible for the session cleanup.
 */
class SessionCleanupCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AuthenticationHandlerInterface
     */
    private $handler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var string
     */
    private $dateTimeRule;

    /**
     * Constructor.
     *
     * @param ObjectManager                  $om
     * @param AuthenticationHandlerInterface $authenticationHandler
     * @param EventDispatcherInterface       $eventDispatcher
     * @param string                         $modelName
     * @param ClassMetadata                  $classMetadata
     * @param string                         $dateTimeRule
     */
    public function __construct(
        ObjectManager $om,
        AuthenticationHandlerInterface $authenticationHandler,
        EventDispatcherInterface $eventDispatcher,
        $modelName,
        ClassMetadata $classMetadata,
        $dateTimeRule
    ) {
        $this->om = $om;
        $this->handler = $authenticationHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelName = (string) $modelName;
        $this->classMetadata = $classMetadata;
        $this->dateTimeRule = (string) $dateTimeRule;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ma27:auth:session-cleanup')
            ->setDescription('Cleans all outdated sessions')
            ->setHelp(<<<'EOF'
The <info>ma27:auth:session-cleanup</info> command purges all api keys of users that were inactive for at least 5 days

The usage is pretty simple:

    <info>php app/console ma27:auth:session-cleanup</info>

NOTE: you have to enable the cleanup section of that bundle (please review the docs for more information)

<info>It's recommended to use a cronjob that purges old api keys every day/two days</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $filteredUsers = $this->searchUsers();
            $processedObjects = 0;

            $affectedUsers = $filteredUsers->toArray();
            $event = new OnBeforeSessionCleanupEvent($affectedUsers);
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::BEFORE_CLEANUP, $event);

            // purge filtered users
            foreach ($filteredUsers as $user) {
                $this->handler->removeSession($user, true);
                ++$processedObjects;
            }

            $this->displaySuccess($processedObjects, $output);

            $afterEvent = new OnSuccessfulCleanupEvent($affectedUsers);
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::CLEANUP_SUCCESS, $afterEvent);

            $this->om->flush();
        } catch (\Exception $ex) {
            $this->eventDispatcher->dispatch(
                Ma27ApiKeyAuthenticationEvents::CLEANUP_ERROR,
                new OnApiKeyCleanupErrorEvent($ex)
            );

            throw $ex;
        }

        return 0;
    }

    /**
     * Search query for users with outdated api keys.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    private function searchUsers()
    {
        $latestActivationPropertyName = $this->classMetadata->getPropertyName(ClassMetadata::LAST_ACTION_PROPERTY);
        $criteria = Criteria::create()
            ->where(Criteria::expr()->lte($latestActivationPropertyName, new \DateTime($this->dateTimeRule)))
            ->andWhere(
                Criteria::expr()->neq(
                    $this->classMetadata->getPropertyName(ClassMetadata::API_KEY_PROPERTY),
                    null
                )
            );

        $repository = $this->om->getRepository($this->modelName);

        if ($repository instanceof Selectable) {
            // orm and mongodb have a Selectable implementation, so it is possible to query for old users
            $filteredUsers = $repository->matching($criteria);
        } else {
            // couchdb and phpcr unfortunately don't implement that feature,
            // so all users must be queried and filtered using the array collection
            $allUsers = new ArrayCollection($repository->findAll());
            $filteredUsers = $allUsers->matching($criteria);
        }

        return $filteredUsers;
    }

    /**
     * Outputs the suces after the cleanup.
     *
     * @param                 $processed
     * @param OutputInterface $output
     */
    private function displaySuccess($processed, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Processed %d items successfully</info>', $processed));
    }
}
