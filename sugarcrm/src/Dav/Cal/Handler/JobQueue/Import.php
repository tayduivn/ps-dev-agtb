<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Handler as CalDavHandler;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException as JQLogicException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as JQInvalidArgumentException;

/**
 * Class Import
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for import process initialization
 */
class Import implements RunnableInterface
{
    /**
     * @param \CalDavEvent $calDavBean
     */
    public function __construct($calDavBean)
    {
        $this->bean = $calDavBean;
    }

    /**
     * start imports process for current CalDavEvent object
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException if bean not instance of CalDavEvent
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if related bean doesn't have adapter
     * @return string
     */
    public function run()
    {
        $adapter = $this->getAdapterFactory();
        if (!($this->bean instanceof \CalDavEvent)) {
            throw new JQInvalidArgumentException('Bean must be an instance of CalDavEvent. Instance of '.get_class($this->bean).' given');
        }
        if (!$adapter->getAdapter($this->bean->getBean()->module_name)) {
            throw new JQLogicException('Bean '.$this->bean->getBean()->module_name.' does not have CalDav adapter');
        }

        $handler = $this->getHandler();
        $handler->import($this->bean);
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }

    /**
     * return CalDav handler for import processing
     * @return Handler
     */
    protected function getHandler()
    {
        return new CalDavHandler();
    }
}
