<?php

namespace Ma27\ApiKeyAuthenticationBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\Events;
use Ma27\ApiKeyAuthenticationBundle\Event\OnAfterSessionCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnBeforeSessionCleanupEvent;
use Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthorizationHandlerInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Command which is responsible for the session cleanup
 */
class SessionCleanupCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AuthorizationHandlerInterface
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var string
     */
    private $lastActiveProperty;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param AuthorizationHandlerInterface $authorizationHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param $modelName
     * @param $lastActiveProperty
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationHandlerInterface $authorizationHandler,
        EventDispatcherInterface $eventDispatcher,
        $modelName,
        $lastActiveProperty,
        LoggerInterface $logger = null
    ) {
        $this->om = $om;
        $this->handler = $authorizationHandler;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelName = (string) $modelName;
        $this->lastActiveProperty = (string) $lastActiveProperty;

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
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dateTime = new \DateTime();
        if (null !== $this->logger) {
            $now = $dateTime->format('m/d/Y H:i:s');

            $this->logger->notice(sprintf('Starting session purge at %s', $now));
        }

        $allUsers = new ArrayCollection($this->om->getRepository($this->modelName)->findAll());

        $expressions = new ExpressionBuilder();
        $expr = $expressions->gte($this->lastActiveProperty, new \DateTime('-5 days'));

        $filterCriteria = new Criteria($expr);
        $filteredUsers = $allUsers->matching($filterCriteria);

        $processedObjects = 0;

        $affectedUsers = $filteredUsers->toArray();
        $event = new OnBeforeSessionCleanupEvent($affectedUsers);
        $this->eventDispatcher->dispatch(Events::BEFORE_CLEANUP, $event);

        foreach ($filteredUsers as $user) {
            if (!$user instanceof UserInterface) {
                if (null !== $this->logger) {
                    $this->logger->critical(
                        sprintf('Broken model found (%s)!', print_r($user, true))
                    );
                }

                $this->om->clear();

                throw new \RuntimeException('Cannot remove session of invalid user!');
            }

            if (!$user->getApiKey()) {
                if (null !== $this->logger) {
                    $this->logger->notice(sprintf('Skipping unauthorized user "%s"', $user->getUsername()));
                }

                continue;
            }

            $this->handler->removeSession($user, true);
            ++$processedObjects;
        }

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Processed %d items successfully', $processedObjects));
        }

        $afterEvent = new OnAfterSessionCleanupEvent($affectedUsers);
        $this->eventDispatcher->dispatch(Events::AFTER_CLEANUP, $afterEvent);

        $this->om->flush();

        if (null !== $this->logger) {
            $endTime = new \DateTime();
            $end = $endTime->format('m/d/Y H:i:s');

            $diff = $endTime->getTimestamp() - $dateTime->getTimestamp();

            $this->logger->notice(sprintf('Stopped cleanup at %s after %d seconds', $end, $diff));
        }
    }
}
