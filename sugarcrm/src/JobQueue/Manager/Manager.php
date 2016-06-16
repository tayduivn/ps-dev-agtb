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

namespace Sugarcrm\Sugarcrm\JobQueue\Manager;

use Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface;
use Sugarcrm\Sugarcrm\JobQueue\Client\Gearman as GearmanClient;
use Sugarcrm\Sugarcrm\JobQueue\Client\MessageQueue as MessageQueueClient;
use Sugarcrm\Sugarcrm\JobQueue\Client\Immediate as ImmediateClient;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Client\PriorityMessageQueue as PriorityMessageQueueClient;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\Base64;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerializeSafe;
use Sugarcrm\Sugarcrm\JobQueue\Worker\PriorityMessageQueue as PriorityMessageQueueWorker;
use Sugarcrm\Sugarcrm\JobQueue\Dispatcher\DispatcherInterface;
use Sugarcrm\Sugarcrm\JobQueue\Dispatcher\Handler;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\RuntimeException;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\CacheFile;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\LockStrategyInterface;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\Stub;
use Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface;
use Sugarcrm\Sugarcrm\JobQueue\Runner\OD as ODRunner;
use Sugarcrm\Sugarcrm\JobQueue\Runner\Parallel;
use Sugarcrm\Sugarcrm\JobQueue\Runner\RunnerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Runner\Standard;
use Sugarcrm\Sugarcrm\JobQueue\Worker\Gearman as GearmanWorker;
use Sugarcrm\Sugarcrm\JobQueue\Worker\MessageQueue as MessageQueueWorker;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\OD as ODWorkload;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Handler\HandlerRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Manager
 * @package JobQueue
 * @method calDavHandler(string $calDavCollectionId)
 */
class Manager extends AbstractManager
{
    /**
     * Run jobs in demonized locked process.
     * @var array $defaultConfig
     */
    protected $defaultConfig = array(
        'adapter' => 'Sugar',
        'runner' => 'Standard',
        'lock' => 'CacheFile',
        'workload' => 'Workload',
        'serializer' => 'Base',
    );

    /**
     * Execute all jobs from queue without locking the process.
     * @var array $defaultODConfig
     */
    protected $defaultODConfig = array(
        'adapter' => 'Sugar',
        'runner' => 'OD',
        'lock' => 'Stub',
        'workload' => 'OD',
        'serializer' => 'Base',
    );

    /**
     * @var array $systemHandlers Of system handlers.
     */
    protected $systemHandlers = array(
        'MassUpdate' => 'Sugarcrm\Sugarcrm\JobQueue\Handler\MassUpdate',
        'ExportRecords' => 'Sugarcrm\Sugarcrm\JobQueue\Handler\ExportRecords',
        'ExportToCSV' => 'Sugarcrm\Sugarcrm\JobQueue\Handler\ExportToCSV',
        'CalDavHandler' => 'Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\Handler',
        'CalDavRebuild' => 'Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\JobQueue',
        'NotificationEvent' => 'Sugarcrm\Sugarcrm\Notification\Handler\EventHandler',
        'NotificationCarrierBulkMessage' => 'Sugarcrm\Sugarcrm\Notification\Handler\CarrierBulkMessageHandler',
        'NotificationSend' => 'Sugarcrm\Sugarcrm\Notification\Handler\SendHandler',
        'RecreateUserRemindersJob' => 'Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob',
    );

    /**
     * @var array $systemAdapters Of system adapters.
     */
    protected $systemAdapters = array(
        // Gearman is a special case represents a real job server.
        'Gearman' => '',
        // Message Queue adapters.
        'Amazon_SQS' => 'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AmazonSQS',
        'AMQP' => 'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AMQP',
        'Sugar' => 'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\Sugar',
    );

    /**
     * @var array $systemObservers Of system observers.
     */
    protected $systemObservers = array(
        array(
            'class' => 'Sugarcrm\Sugarcrm\JobQueue\Observer\Reflection',
            'priority' => 0,
            'on' => array(), // Handlers to perform, false - all.
            'off' => array(  // Handlers to exclude.
                'CalDavHandler',
                'NotificationEvent',
                'NotificationCarrierBulkMessage',
                'NotificationSend',
                'RecreateUserRemindersJob',
            ),
            'config' => null, // The second in constructor.
        ),
        array(
            'class' => 'Sugarcrm\Sugarcrm\JobQueue\Observer\Cache',
            'priority' => -10,
        ),
        array(
            'class' => 'Sugarcrm\Sugarcrm\JobQueue\Observer\State',
            'priority' => -100,
            'off' => array(  // Handlers to exclude.
                'CalDavHandler',
                'NotificationEvent',
                'NotificationCarrierBulkMessage',
                'NotificationSend',
                'RecreateUserRemindersJob',
            ),
        ),
        array(
            'class' => 'Sugarcrm\Sugarcrm\JobQueue\Observer\ExportRecordsObserver',
            'priority' => -200,
            'on' => array(
                'ExportRecords',
                'ExportToCSV',
            ),
        ),
    );

    /**
     * @var int $observerDefaultPriority Default priority for observers queue.
     */
    protected $observerDefaultPriority = -150;

    /**
     * Save observers - handlers mapping.
     * @var array $observerMap [observerClass => [on => [handlerName], off => []]];
     */
    protected $observerHandlerMap = array();

    /**
     * @var array $systemConfig Cached config.
     */
    protected $systemConfig;

    /**
     * @var HandlerRegistry
     */
    protected $handlerRegistry;

    /**
     * @var AdapterRegistry
     */
    protected $adapterRegistry;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var WorkerInterface
     */
    protected $worker;

    /**
     * @var \SplPriorityQueue
     */
    protected $observer;

    /**
     * @var RunnerInterface
     */
    protected $runner;

    /**
     * @var array $context Context the manager is called.
     */
    protected $context = array();

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LockStrategyInterface
     */
    protected $lockStrategy;

    /**
     * @var Manager
     */
    protected static $instance;

    /**
     * SugarCRM dependent manager.
     * Load default handlers and adapters.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->handlerRegistry = new HandlerRegistry();
        $this->adapterRegistry = new AdapterRegistry();
        $this->initHandlers();
        $this->initAdapters();
    }

    /**
     * Get the Manager as service.
     * @return Manager
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @throws LogicException
     */
    public function getClient($config = null)
    {
        if ($this->client) {
            return $this->client;
        }
        $config = $this->getSystemConfig($config);
        $config['adapter'] = !empty($config['client']) ? $config['client'] : $config['adapter'];
        $serializer = $this->getSerializer();
        $client = null;

        switch (strtolower($config['adapter'])) {
            case 'priority':
                $client = new PriorityMessageQueueClient(
                    $config['priority'],
                    $this->adapterRegistry,
                    $serializer,
                    $this->logger
                );
                break;
            case 'gearman':
                $client = new GearmanClient($config['gearman'], $serializer, $this->logger);
                break;
            case 'immediate':
                $client = new ImmediateClient(array($this, 'proxyHandler'), $this->logger);
                break;
            // Message queue client.
            case 'amazon_sqs':
            case 'amqp':
            case 'sugar':
            default: // Custom adapters are MQ only.
                $adapter = $this->getMessageQueueAdapter($config);
                if (!$adapter) {
                    throw new LogicException(
                        "Cannot create a client, no config for the '{$config['adapter']}' adapter found."
                    );
                }
                $client = new MessageQueueClient($adapter, $serializer, $this->logger);
                break;
        }
        if (!$client) {
            throw new LogicException('Cannot instantiate a client.');
        }
        $this->logger->debug('Instantiate Client: ' . get_class($client));

        return $this->client = $client;
    }

    /**
     * {@inheritdoc}
     * @throws LogicException
     */
    public function getWorker($config = null)
    {
        if ($this->worker) {
            return $this->worker;
        }
        $config = $this->getSystemConfig($config);
        $serializer = $this->getSerializer();
        $config['adapter'] = !empty($config['worker']) ? $config['worker'] : $config['adapter'];

        switch (strtolower($config['adapter'])) {
            case 'priority':
                $this->worker = new PriorityMessageQueueWorker(
                    $config['priority'],
                    $this->adapterRegistry,
                    $serializer,
                    $this->logger
                );
                break;
            case 'gearman':
                $this->worker = new GearmanWorker($config['gearman'], $serializer, $this->logger);
                break;
            case 'immediate':
                throw new LogicException(
                    'Worker is not needed to run immediate job - run is performed in ImmediateClient.'
                );
                break;
            // Message queue worker.
            case 'amazon_sqs':
            case 'amqp':
            case 'sugar':
            default: // Custom workers are MQ only.
                $adapter = $this->getMessageQueueAdapter($config);
                if (!$adapter) {
                    throw new LogicException(
                        "Cannot create a worker, no config for the '{$config['adapter']}' adapter found."
                    );
                }
                $this->worker = new MessageQueueWorker($adapter, $serializer, $this->logger);
                break;
        }
        $this->logger->debug('Instantiate Worker: ' . get_class($this->worker));
        return $this->worker;
    }

    /**
     * {@inheritdoc}
     * @return \SplPriorityQueue
     */
    public function getObserver($config = null)
    {
        if ($this->observer) {
            // \SplPriorityQueue extracts elements while walking on the queue
            return clone $this->observer;
        }
        $config = $this->getSystemConfig($config);
        $this->observer = new \SplPriorityQueue();
        $this->observer->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        $resultConfig = array_merge(
            $this->getSystemObservers(),
            empty($config['observers']) ? array() : $config['observers']
        );

        foreach ($resultConfig as $obsConf) {
            $observer = $this->initObserver($obsConf['class'], isset($obsConf['config']) ? $obsConf['config'] : null);
            $this->observer->insert(
                $observer,
                isset($obsConf['priority']) ? $obsConf['priority'] : $this->observerDefaultPriority
            );
            $observerClass = get_class($observer);
            $this->logger->debug("Attach Observer: {$observerClass}");
            // Cache.
            $this->observerHandlerMap[$observerClass] = array(
                'on' => empty($obsConf['on']) ? array() : $obsConf['on'],
                'off' => empty($obsConf['off']) ? array() : $obsConf['off'],
            );
        }
        // \SplPriorityQueue extracts elements while walking on the queue.
        return clone $this->observer;
    }

    /**
     * Apply observers according to configs keys "on" and "off".
     * {@inheritdoc}
     */
    protected function applyObserver($observer, $handlerName)
    {
        $map = $this->observerHandlerMap[get_class($observer)];
        if (!empty($map['on']) && !in_array($handlerName, $map['on'])) {
            return false;
        }
        if (!empty($map['off']) && in_array($handlerName, $map['off'])) {
            return false;
        }
        return true;
    }

    /**
     * Return system defined observers.
     */
    protected function getSystemObservers()
    {
        return $this->systemObservers;
    }

    /**
     * Initialize observer.
     * @param string $class
     * @param mixed $config Observers configuration.
     * @return ObserverInterface
     * @throws InvalidArgumentException If the class does not exist.
     */
    protected function initObserver($class, $config = null)
    {
        if (empty($class) || !class_exists($class)) {
            throw new InvalidArgumentException(
                'Invalid observer: empty "class" config property or the class does not exist.'
            );
        }
        return new $class($this->logger, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getRunner($config = null)
    {
        if ($this->runner) {
            return $this->runner;
        }
        $config = $this->getSystemConfig($config);
        $worker = $this->getWorker();
        $lock = $this->getLock();
        $this->logger->info('Creating worker runner.');

        switch (strtolower($config['runner'])) {
            case 'od':
                $this->runner = new ODRunner($config, $worker, $lock, $this->logger);
                break;
            case 'parallel':
                $this->runner = new Parallel($config, $worker, $lock, $this->logger);
                break;
            case 'standard':
                $this->runner = new Standard($config, $worker, $lock, $this->logger);
                break;
            default:
                throw new LogicException("The runner '{$config['runner']}' is not found.");
        }
        $this->logger->debug('Instantiate Runner: ' . get_class($this->runner));
        return $this->runner;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializer($config = null)
    {
        if ($this->serializer) {
            return $this->serializer;
        }
        $config = $this->getSystemConfig($config);
        switch (strtolower($config['serializer'])) {
            default:
                $this->serializer = new PHPSerializeSafe($this->logger, new Base64($this->logger));
        }
        $this->logger->debug('Instantiate Serializer: ' . get_class($this->serializer));
        return $this->serializer;
    }

    /**
     * Get proper configuration data.
     * Read config once from SugarConfig, than read from internal cache.
     * @param mixed $config
     * @return array
     */
    public function getSystemConfig($config = null)
    {
        if ($this->systemConfig) {
            return $this->systemConfig;
        }
        if ($config) {
            return $this->systemConfig = $config;
        }
        $systemConfig = \SugarConfig::getInstance()->get('job_queue', []);
        if (!empty($systemConfig['od'])) {
            $this->logger->info('OD mode is enabled. Read system file config.');
            $config = array_merge($this->defaultODConfig, $systemConfig);
        } else {
            $this->logger->info('Read SchedulersJobs base config.');
            $config = array_merge(
                $this->defaultConfig,
                \BeanFactory::getBean('Administration')->getConfigForModule('SchedulersJobs', 'base'),
                $systemConfig
            );
        }
        return $this->systemConfig = $config;
    }

    /**
     * Get configured message queue adapter.
     * Because of message queues can work within unique exchange, or create a new queue, the special key
     * should be added here.
     *
     * @param array $config Message queue config.
     * @return AdapterInterface|null Configured adapter or null if an adapter cannot be found.
     * @throw LogicException If an adapter found but has no config.
     */
    protected function getMessageQueueAdapter($config)
    {
        $adapterConfig = array();
        $class = $this->adapterRegistry->get($config['adapter']);
        if (!$class) {
            return null;
        }
        if (isset($config[strtolower($config['adapter'])])) {
            $adapterConfig = $config[strtolower($config['adapter'])];
        }

        $this->logger->debug("Instantiate MessageQueue Adapter: {$class}");
        return new $class($adapterConfig, $this->logger);
    }

    /**
     * Return a specific dispatcher by name.
     *
     * @param string $handlerName In the register.
     * @return callable|null
     */
    protected function getDispatcher($handlerName)
    {
        $dispatcher = null;
        /**
         * @var string $name Handler name.
         * @var string $class Handler class.
         * @var DispatcherInterface $dispatcher Handler class.
         */
        extract($this->handlerRegistry->get($handlerName));

        $this->logger->debug("Dispatch handler '{$handlerName}'.");
        return $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getLock($config = null)
    {
        if ($this->lockStrategy) {
            return $this->lockStrategy;
        }
        $config = $this->getSystemConfig($config);
        switch (strtolower($config['lock'])) {
            case 'cachefile':
                $this->lockStrategy = new CacheFile();
                break;
            case 'stub':
                $this->lockStrategy = new Stub();
                break;
            default:
                throw new LogicException("Cannot setup the lock strategy '{$config['lock']}'.");
        }
        $this->logger->debug('Instantiate LockStrategy: ' . get_class($this->lockStrategy));
        return $this->lockStrategy;
    }

    /**
     * Create and return instance of Workload.
     *
     * @param string $handlerName
     * @param mixed $data
     * @param array $attributes
     * @return WorkloadInterface instance of workload adapter.
     */
    public function createWorkload($handlerName, $data, array $attributes = array())
    {
        $config = $this->getSystemConfig();
        $workload = null;

        switch (strtolower($config['workload'])) {
            case 'od':
                $workload = new ODWorkload($handlerName, $data, $attributes);
                break;
            case 'workload':
                $workload = new Workload($handlerName, $data, $attributes);
                break;
            default:
                $workload = new Workload($handlerName, $data, $attributes);
        }
        $this->logger->debug('Use workload ' . get_class($workload) . " for handler '{$handlerName}'.");
        return $workload;
    }

    /**
     * Start running. Endpoint to start manager.
     */
    public function run()
    {
        if (!count($this->handlerRegistry)) {
            throw new LogicException('No handlers found.');
        }
        $this->registerHandlersInWorker();
        parent::run();
    }

    /**
     * Register system handler in configured worker with proxy method.
     */
    protected function registerHandlersInWorker()
    {
        foreach ($this->handlerRegistry as $params) {
            $workload = $this->createWorkload($params['name'], array());
            $this->logger->debug("Register handler {$params['name']} in worker.");
            $this->getWorker()->registerHandler($workload->getRoute(), array($this, 'proxyHandler'));
        }
    }

    /**
     * Collect system adapters into adapter registry.
     * Currently message queue adapters are supported (implement AdapterInterface).
     */
    protected function initAdapters()
    {
        $config = $this->getSystemConfig();
        $resultConfig = array_merge(
            $this->systemAdapters,
            empty($config['adapters']) ? array() : $config['adapters']
        );
        foreach ($resultConfig as $name => $class) {
            if (empty($name) || !is_string($name)) {
                throw new InvalidArgumentException('Cannot register non-string adapter.');
            }
            $this->logger->debug("Register adapter '{$class}' as '{$name}'.");
            $this->adapterRegistry->add($name, $class);
        }
    }

    /**
     * Load handler registry and add system handlers.
     * @throw InvalidArgumentException
     */
    protected function initHandlers()
    {
        $config = $this->getSystemConfig();
        $resultConfig = array_merge(
            $this->systemHandlers,
            empty($config['handlers']) ? array() : $config['handlers']
        );
        foreach ($resultConfig as $name => $class) {
            if (empty($name) || !is_string($name)) {
                throw new InvalidArgumentException('Cannot register Handler for non-string route name.');
            }
            $this->registerHandler($name, $class);
        }
    }

    /**
     * Uses SchedulersJobs fail resolution.
     * {@inheritdoc}
     */
    protected function getFailMark()
    {
        return \SchedulersJob::JOB_FAILURE;
    }

    /**
     * Create a job using case-insensitive handler name.
     *
     * @param string $name
     * @param array $arguments
     * @return WorkloadInterface
     * @throws RuntimeException
     */
    public function __call($name, $arguments)
    {
        $handlerParams = $this->handlerRegistry->get($name);
        if (!$handlerParams) {
            throw new RuntimeException("The handler '{$name}' is not registered.");
        }
        $class = $handlerParams['class'];
        $attributes = array();
        $attributes['fallible'] = defined("{$class}::FALLIBLE") ? constant("{$class}::FALLIBLE") : false;
        $attributes['rerun'] = defined("{$class}::RERUN") ? constant("{$class}::RERUN") : false;
        // To validate handler creating.
        $handlerObj = new \ReflectionClass($class);
        $handlerObj->newInstanceArgs($arguments);
        $workload = $this->createWorkload($handlerParams['name'], $arguments, $attributes);
        $this->addJob($workload);

        return $workload;
    }

    /**
     * Attach observer.
     * {@inheritdoc}
     */
    public function addJob(WorkloadInterface $workload)
    {
        foreach ($this->context as $key => $val) {
            $workload->setAttribute($key, $val);
        }
        $this->logger->debug('Add job in context: ' . var_export($this->context, true));
        parent::addJob($workload);
    }

    /**
     * Shutdown handler.
     */
    public function shutdownHandler()
    {
        $this->getRunner()->shutdownHandler();
    }

    /**
     * Register a new observer in manager.
     * @param ObserverInterface $observer
     * @param int|null $priority SplPriorityQueue priority.
     */
    public function registerObserver(ObserverInterface $observer, $priority = null)
    {
        if ($priority === null) {
            $priority = $this->observerDefaultPriority;
        }
        $this->logger->info('Register Observer ' . get_class($observer));
        // Make sure the property "observer" is populated.
        $this->getObserver();
        // Because getObserver() return a clone need to insert to original queue.
        $this->observer->insert($observer, $priority);
    }

    /**
     * Register a new handler.
     *
     * @param string $name Handler name.
     * @param string $class Full class name with namespace.
     *
     * @throws LogicException
     */
    public function registerHandler($name, $class)
    {
        if (!class_exists($class)) {
            throw new LogicException("Handler '{$name}' '{$class}' does not exist.");
        }
        $interfaces = class_implements($class);
        if (!in_array('Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface', $interfaces)) {
            throw new LogicException('Handler should implement RunnableInterface.');
        }
        $dispatcher = new Handler($class, $this->logger);
        $this->logger->info("Register handler '{$class}' as '{$name}'.");
        $this->handlerRegistry->add($name, $class, $dispatcher);
    }

    /**
     * Register a new adapter.
     *
     * @param string $name Adapter name.
     * @param string $class Full class name with namespace.
     */
    public function registerAdapter($name, $class)
    {
        $this->logger->info("Register adapter '{$class}' as '{$name}'.");
        $this->adapterRegistry->add($name, $class);
    }

    /**
     * Set a context for manager.
     *
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->logger->debug('Set context for manager: ' . var_export($context, true));
        $this->context = $context;
    }

    /**
     * Get manager's context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get registered handler.
     *
     * @param $name
     * @return array|null
     */
    public function getRegisteredHandler($name)
    {
        $this->logger->info("Register handler '{$name}'.");
        return $this->handlerRegistry->get($name);
    }

    /**
     * Try to instantiate a client.
     * Throws an exception if passed adapter cannot be initialized.
     * @param string $name Client name.
     * @param array $config Configuration for the adapter.
     * @throws \Exception
     */
    public function validateClient($name, array $config = [])
    {
        $factoryConfig = [];
        $factoryConfig['client'] = $name;
        $factoryConfig[$name] = $config;
        $this->getClient($factoryConfig);
    }
}
