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

use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use Sugarcrm\Sugarcrm\Notification\Config\Status;
use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\Carrier\ConfigurableInterface;

require_once 'include/api/SugarApi.php';

/**
 * API work with Subscription Registry and with Carriers Status get/put configuration.
 *
 * Class GlobalConfigApi
 */
class GlobalConfigApi extends SugarApi
{
    /**
     * {@inheritDoc}
     */
    public function registerApiRest()
    {
        return array(
            'getConfig' => array(
                'reqType' => 'GET',
                'path' => array('NotificationCenter', 'config', 'global'),
                'pathVars' => array(),
                'method' => 'getConfig',
                'shortHelp' => 'Return configuration of subscriptions and carriers status.',
                'longHelp' => '',
            ),
            'putConfig' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'config', 'global'),
                'pathVars' => array(),
                'method' => 'updateConfig',
                'shortHelp' => 'Save configuration of subscriptions and carriers status.',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Update Subscription Registry and Carriers Status configurations.
     * @param ServiceBase $api
     * @param array $args
     * @return array configuration
     */
    public function updateConfig(ServiceBase $api, array $args)
    {
        $this->checkIsAdmin($api);
        $this->requireArgs($args, array('carriers', 'config'));
        $this->updateStatus($args['carriers']);
        $this->getSubscriptionsRegistry()->setGlobalConfiguration($args['config']);
        return $this->getConfig($api, $args);
    }

    /**
     * Check is api user is admin.
     *
     * @param ServiceBase $api
     * @throws SugarApiExceptionNotAuthorized throw exception in api user is not admin
     */
    protected function checkIsAdmin(ServiceBase $api)
    {
        if (!$api->user->isAdmin()) {
            throw new SugarApiExceptionNotAuthorized();
        }
    }

    /**
     * Updates statuses of carriers
     *
     * @param array $carriers
     */
    protected function updateStatus($carriers)
    {
        $status = $this->getStatus();
        $registry = $this->getCarrierRegistry();
        if (!empty($carriers)) {
            foreach ($registry->getCarriers() as $module) {
                if (!array_key_exists($module, $carriers)) {
                    continue;
                }
                $status->setCarrierStatus($module, !empty($carriers[$module]['status']));
            }
        }
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

    /**
     * Create instance of SubscriptionsRegistry
     * @return SubscriptionsRegistry
     */
    protected function getSubscriptionsRegistry()
    {
        return new SubscriptionsRegistry();
    }

    /**
     * Return Subscription Registry and Carriers Status configurations.
     * @param ServiceBase $api
     * @param array $args
     * @return array configuration
     */
    public function getConfig(ServiceBase $api, array $args)
    {
        $this->checkIsAdmin($api);
        return array(
            'carriers' => $this->getCarriersConfig(),
            'config' => $this->getSubscriptionsRegistry()->getGlobalConfiguration()
        );
    }

    /**
     * Return Carriers Status configurations.
     *
     * @return array configuration
     */
    protected function getCarriersConfig()
    {
        $status = $this->getStatus();
        $registry = $this->getCarrierRegistry();
        $carriers = array();
        foreach ($registry->getCarriers() as $module) {
            $carrier = $registry->getCarrier($module);
            $configurable = $carrier instanceof ConfigurableInterface;
            $isConfigured = true;
            $configLayout = null;
            if ($configurable) {
                $isConfigured = $carrier->isConfigured();
                $configLayout = $carrier->getConfigLayout();
            }

            $carriers[$module] = array(
                'status' => $status->getCarrierStatus($module),
                'configurable' => $configurable,
                'isConfigured' => $isConfigured,
                'configLayout' => $configLayout,
            );
        }

        return $carriers;
    }
}
