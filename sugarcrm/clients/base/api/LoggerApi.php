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

require_once 'data/BeanFactory.php';
require_once 'include/api/SugarListApi.php';

class LoggerApi extends SugarApi
{

    public function registerApiRest()
    {
        return array(
            'logPost' => array(
                'reqType' => 'POST',
                'path' => array('logger'),
                'pathVars' => array(),
                'method' => 'logMessage',
                'shortHelp' => 'Writes a message out to the log prefaced by a channel name',
                'longHelp' => 'include/api/help/logger_help.html',
            ),
        );
    }

    /**
     * Gets the exceptions list for the exceptions help endpoint
     *
     * @param RestService $api The service object
     * @param array $args The request arguments
     * @return array status
     */
    public function logMessage($api, $args)
    {
        $log = LoggerManager::getLogger();

        $level = empty($args['level']) ? 'debug' : $args['level'];
        $message = empty($args['message']) ? ' ' : $args['message'];
        $channel = empty($args['channel']) ? 'LoggerApi' : $args['channel'];

        $log->$level("{$channel} - {$message}");

        return array('status' => true);
    }
}
