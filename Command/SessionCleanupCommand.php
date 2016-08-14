<?php

namespace Ma27\ApiKeyAuthenticationBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\OnAfterCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnApiKeyCleanupErrorEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnBeforeSessionCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnSuccessfulCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Psr\Log\LoggerInterface;
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
     * Constructor.
     *
     * @param ObjectManager                  $om
     * @param AuthenticationHandlerInterface $authenticationHandler
     * @param EventDispatcherInterface       $eventDispatcher
     * @param string                         $modelName
     * @param ClassMetadata                  $classMetadata
     */
    public function __construct(
        ObjectManager $om,
        AuthenticationHandlerInterface $authenticationHandler,
        EventDispatcherInterface $eventDispatcher,
        $modelName,
        ClassMetadata $classMetadata
    ) {
        $this->om = $om;
        $this->handler = $authenticationHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelName = (string) $modelName;
        $this->classMetadata = $classMetadata;

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
        // TODO add stats to the event structure
        try {
            $dateTime = new \DateTime();

            $now = $dateTime->format('m/d/Y H:i:s');
            $message = sprintf('Starting session purge at %s', $now);
            $output->writeln(sprintf('<info>%s</info>', $message));

            // search query
            $latestActivationPropertyName = $this->classMetadata->getPropertyName(ClassMetadata::LAST_ACTION_PROPERTY);
            $criteria = Criteria::create()
                ->where(Criteria::expr()->lte($latestActivationPropertyName, new \DateTime('-5 days')))
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

            $processedObjects = 0;

            $affectedUsers = $filteredUsers->toArray();
            $event = new OnBeforeSessionCleanupEvent($affectedUsers);
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::BEFORE_CLEANUP, $event);

            // purge filtered users
            foreach ($filteredUsers as $user) {
                $this->handler->removeSession($user, true);
                ++$processedObjects;
            }

            $message = sprintf('Processed %d items successfully', $processedObjects);
            $output->writeln(sprintf('<info>%s</info>', $message));

            $afterEvent = new OnSuccessfulCleanupEvent($affectedUsers);
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::CLEANUP_SUCCESS, $afterEvent);

            $this->om->flush();

            $endTime = new \DateTime();
            $end = $endTime->format('m/d/Y H:i:s');

            $diff = $endTime->getTimestamp() - $dateTime->getTimestamp();
            $output->writeln(sprintf('<info>%s</info>', sprintf('Stopped cleanup at %s after %d seconds', $end, $diff)));
        } catch (\Exception $ex) {
            $this->eventDispatcher->dispatch(
                Ma27ApiKeyAuthenticationEvents::CLEANUP_ERROR,
                new OnApiKeyCleanupErrorEvent($ex)
            );

            throw $ex;
        }

        $this->eventDispatcher->dispatch(
            Ma27ApiKeyAuthenticationEvents::AFTER_CLEANUP,
            new OnAfterCleanupEvent($affectedUsers)
        );

        return 0;
    }
}
