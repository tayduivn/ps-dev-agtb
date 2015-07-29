<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

use Sugarcrm\Sugarcrm\Notification\Config\Status;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;

require_once('include/api/SugarApi.php');

class CarriersConfigApi extends SugarApi
{

    public function registerApiRest()
    {
        return array(
            'getConfig' => array(
                'reqType' => 'GET',
                'path' => array('NotificationCenter', 'config', '?'),
                'pathVars' => array('', '', 'module'),
                'method' => 'getConfig',
                'shortHelp' => 'Get configuration for specific carrier',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
            'handleSave' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'config', '?'),
                'pathVars' => array('', '', 'module'),
                'method' => 'handleSave',
                'shortHelp' => 'Handle save configuration for specific carrier',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
        );
    }

    /**
     * Function return carrier status
     *
     * @param ServiceBase $api
     * @param array $args
     * @return bool
     */
    public function getConfig(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module'));

        $status = Status::getInstance();
        return $status->getCarrierStatus($args['module']);
    }

    /**
     * @param ServiceBase $api
     * @param array $args
     * @return array
     * @throws SugarApiExceptionNotFound
     */
    public function handleSave(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'status'));

        $status = Status::getInstance();
        $status->setCarrierStatus($args['module'], $args['status']);
        return $status->getCarrierStatus($args['module']);
    }

}
