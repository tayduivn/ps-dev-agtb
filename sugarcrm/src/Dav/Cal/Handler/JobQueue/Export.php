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
 * Class Export
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for export process initialization
 */
class Export implements RunnableInterface
{
    /**
     * @param string $moduleName
     * @param string $beanId
     * @param string $userId
     */
    public function __construct($moduleName, $beanId, $userId)
    {
        $this->moduleName = $moduleName;
        $this->beanId = $beanId;
        $this->userId = $userId;
    }

    /**
    * start export process for current bean if it extends from SugarBean
    * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException if bean not instance of SugarBean
    * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if bean doesn't have adapter
    * @return string
    */
    public function run()
    {
        $currentUser = $this->getCurrentUser();
        $GLOBALS['current_user'] = $this->getAssignedUser();

        $adapter = $this->getAdapterFactory();
        $bean = $this->getBean();
        if (!($bean instanceof \SugarBean)) {
            throw new JQInvalidArgumentException('Bean must be an instance of SugarBean. Instance of '. get_class($bean) .' given');
        }
        if (!$adapter->getAdapter($bean->module_name)) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }
        $handler = $this->getHandler();
        $handler->export($bean);
        $GLOBALS['current_user'] = $currentUser;
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * get bean for import process
     * @return null|\SugarBean
     */
    protected function getBean()
    {
        return \BeanFactory::getBean($this->moduleName, $this->beanId);
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }

    /**
     * return CalDav handler for export processing
     * @return Handler
     */
    protected function getHandler()
    {
        return new CalDavHandler();
    }

    /**
     * return user bean for assign user to bean
     * @return \User
     */
    protected function getAssignedUser()
    {
        return \BeanFactory::getBean('Users', $this->userId);
    }

    /**
     * @return \User
     */
    protected function getCurrentUser()
    {
        return $GLOBALS['current_user'];
    }
}
