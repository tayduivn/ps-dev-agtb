<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\JobQueue\Dispatcher;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Handler\SubtaskCapableInterface;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class Handler
 * @package JobQueue
 */
class Handler implements DispatcherInterface
{
    /**
     * @var string $class
     */
    protected $class;

     /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SugarCRM dependent dispatcher.
     * Should implement the Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface interface.
     * @param string $className Handler class.
     * @param LoggerInterface $logger
     * @throws InvalidArgumentException
     */
    public function __construct($className, LoggerInterface $logger)
    {
        if (class_exists($className)) {
            $interfaces = class_implements($className);
            if (!in_array('Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface', $interfaces)) {
                throw new InvalidArgumentException('Handler should implement RunnableInterface.');
            }
        } else {
            throw new InvalidArgumentException('Handler should be a class.');
        }
        $this->class = $className;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch()
    {
        $className = $this->class;
        $logger = $this->logger;
        return function (WorkloadInterface $workload) use ($className, $logger) {
            $reflector = new \ReflectionClass($className);
            $handler = $reflector->newInstanceArgs($workload->getData());

            // The interface populates the $client property with ClientInterface for creating child tasks in context.
            if ($handler instanceof SubtaskCapableInterface) {
                $this->logger->debug('Inject an instance of ClientInterface into handler.');
                $manager = new Manager();
                $manager->setContext($workload->getAttributes());

                if (!property_exists($handler, 'JQClient')) {
                    $handler->JQClient = $manager;
                } else {
                    $property = $reflector->getProperty('JQClient');
                    $property->setAccessible(true);
                    $property->setValue($handler, $manager);
                }
            }
            try {
                $this->logger->info("Run handler '{$className}'.");
                $result = $handler->run();
                \ActivityQueueManager::resetDuplicateCheck();
                return $result;
            } catch (\Exception $ex) {
                $errorMessage = $reflector->getName() . ' error: ' . $ex->getMessage();
                $this->logger->error($errorMessage);
                $workload->setAttribute('errorMessage', $errorMessage);
                return \SchedulersJob::JOB_FAILURE;
            }
        };
    }
}
