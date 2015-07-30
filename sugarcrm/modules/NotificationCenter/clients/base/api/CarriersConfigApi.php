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
                'path' => array('NotificationCenter', 'config'),
                'pathVars' => array('', '', 'module'),
                'method' => 'getConfig',
                'shortHelp' => 'Function return list of all carriers with their',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
            'handleSave' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'config'),
                'pathVars' => array('', '', 'module'),
                'method' => 'handleSave',
                'shortHelp' => 'Handle save carriers statuses',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
        );
    }

    /**
     * Function return list of all carriers with their statuses
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function getConfig(ServiceBase $api, array $args)
    {
        $status = Status::getInstance();
        $registry = CarrierRegistry::getInstance();
        $data = array();
        foreach ($registry->getCarriers() as $module) {
            $data[$module] = $status->getCarrierStatus($module);
        }

        return $data;
    }

    /**
     * Save carriers statuses, if not exists carrier in $args thn lived old status
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function handleSave(ServiceBase $api, array $args)
    {
        $status = Status::getInstance();
        $registry = CarrierRegistry::getInstance();
        foreach ($registry->getCarriers() as $module) {
            if (!isset($args[$module])) {
                continue;
            }
            $status->setCarrierStatus($module, $args[$module]);
        }

        return $this->getConfig($api, $args);
    }

}
