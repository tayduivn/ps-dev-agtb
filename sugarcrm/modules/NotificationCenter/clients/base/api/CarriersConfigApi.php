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
                'shortHelp' => 'Returns status & options of carriers',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
            'handleSave' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'config'),
                'pathVars' => array('', '', 'module'),
                'method' => 'handleSave',
                'shortHelp' => 'Saves status of carrier',
                'longHelp' => '',
                'acl' => 'adminOrDev',
            ),
        );
    }

    /**
     * Returns status & options of carriers
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function getConfig(ServiceBase $api, array $args)
    {
        $status = $this->getStatus();
        $registry = $this->getCarrierRegistry();
        $data = array();
        foreach ($registry->getCarriers() as $module) {
            $data[$module] = $status->getCarrierStatus($module);
        }

        return $data;
    }

    /**
     * Updates statuses of carriers
     *
     * @param ServiceBase $api
     * @param array $args
     * @return bool
     */
    public function handleSave(ServiceBase $api, array $args)
    {
        $status = $this->getStatus();
        $registry = $this->getCarrierRegistry();
        $result = true;
        foreach ($registry->getCarriers() as $module) {
            if (!array_key_exists($module, $args)) {
                continue;
            }
            $result = $result | $status->setCarrierStatus($module, !empty($args[$module]));
        }

        return $result;
    }

    /**
     * @see Status::getInstance
     */
    protected function getStatus()
    {
        return Status::getInstance();
    }

    /**
     * @see CarrierRegistry::getInstance
     */
    protected function getCarrierRegistry()
    {
        return CarrierRegistry::getInstance();
    }
}
