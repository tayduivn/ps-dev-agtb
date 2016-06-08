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

namespace Sugarcrm\Sugarcrm\Notification\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

/**
 * Class BaseHandler
 * @package Sugarcrm\Sugarcrm\Notification\JobQueue
 */
abstract class BaseHandler implements RunnableInterface
{
    /**
     * @var LoggerTransition
     */
    protected $logger;

    /**
     * Unserialize all function arguments for next throwing to method initialize.
     */
    public function __construct()
    {
        $this->logger = new LoggerTransition(\LoggerManager::getLogger());

        if (!method_exists($this, 'initialize')) {
            throw new \Exception("Initialize method is not implemented");
        }
        $arguments = func_get_args();

        // Set up global user and eliminate this argument for initialize.
        $this->setUpCurrentUser(array_shift($arguments));

        foreach ($arguments as $key => $argument) {
            if ($argument[0]) {
                require_once $argument[0];
            }
            
            if (!is_null($argument[1])) {
                $arguments[$key] = $argument[1]::unserialize($argument[2]);
            } else {
                $arguments[$key] = unserialize($argument[2]);
            }
        }

        call_user_func_array(array($this, 'initialize'), $arguments);
    }

    /**
     * Return Customized JobQueue Manager.
     * Manager helps unserialize classes in unsupported file paths.
     *
     * @return Manager
     */
    protected function getJobQueueManager()
    {
        return new Manager();
    }

    /**
     * Set up global current user for job execution.
     * If $userId is not set, we retrieve the first active admin user.
     *
     * @param string|null $userId id of the User to set up as current one.
     */
    protected function setUpCurrentUser($userId)
    {
        if (is_null($userId)) {
            $userBean = \BeanFactory::getBean('Users');
            $user = $userBean->getSystemUser();
        } else {
            $user = \BeanFactory::getBean('Users', $userId);
        }

        $this->logger->debug(
            "NC: Changing system current user from User({$GLOBALS['current_user']->id}) to User({$user->id})"
        );

        $GLOBALS['current_user'] = $user;
    }
}
