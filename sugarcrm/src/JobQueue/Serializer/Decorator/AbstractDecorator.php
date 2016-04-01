<?php

namespace Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

abstract class AbstractDecorator implements DecoratorInterface, SerializerInterface
{
    /**
     * @var DecoratorInterface
     */
    protected $component;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param DecoratorInterface|null $decorator
     */
    public function __construct(LoggerInterface $logger, DecoratorInterface $decorator = null)
    {
        $this->logger = $logger;
        $this->component = $decorator ? $decorator : null;
    }

    /**
     * @return DecoratorInterface
     */
    protected function getComponent()
    {
        return $this->component;
    }

    /**
     * {@inheritdoc}
     */
    public function decorate($data, WorkloadInterface $workload)
    {
        return $this->getComponent() ? $this->getComponent()->decorate($data, $workload) : $data;
    }

    /**
     * {@inheritdoc}
     */
    public function undecorate($data)
    {
        return $this->getComponent() ? $this->getComponent()->undecorate($data) : $data;
    }

    /**
     * Adapter for decorate.
     * {@inheritdoc}
     */
    final public function serialize(WorkloadInterface $data)
    {
        return $this->decorate($data, $data);
    }

    /**
     * Adapter for undecorate.
     * {@inheritdoc}
     */
    final public function unserialize($data)
    {
        return $this->undecorate($data);
    }
}
