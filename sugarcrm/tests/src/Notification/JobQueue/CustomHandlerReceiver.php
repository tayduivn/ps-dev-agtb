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

namespace Sugarcrm\SugarcrmTests\Notification\JobQueue;

use Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler;

/**
 * Class CustomHandlerReceiver
 * Mock for testing Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler
 * @package Sugarcrm\SugarcrmTests\Notification\JobQueue
 */
class CustomHandlerReceiver extends BaseHandler
{

    public static $arguments = array();

    public function initialize()
    {
        self::$arguments = func_get_args();
    }

    public function run()
    {

    }
}
