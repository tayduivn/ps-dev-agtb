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

use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;

require_once('include/api/SugarApi.php');

/**
 * API work with Subscription Registry get/put configuration.
 *
 * Class GlobalDeliveryConfigApi
 */
class GlobalDeliveryConfigApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'getConfig' => array(
                'reqType' => 'GET',
                'path' => array('NotificationCenter', 'global-delivery-config'),
                'pathVars' => array(),
                'method' => 'getConfig',
                'shortHelp' => 'Return configuration of subscriptions.',
                'longHelp' => '',
            ),
            'putConfig' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'global-delivery-config'),
                'pathVars' => array(),
                'method' => 'putConfig',
                'shortHelp' => 'Save configuration of subscriptions.',
                'longHelp' => '',
            ),
        );
    }

    /**
     * @see SubscriptionsRegistry::getGlobalConfiguration()
     * @param ServiceBase $api
     * @param array $args
     * @return array configuration
     */
    public function getConfig(ServiceBase $api, array $args)
    {
        $registry = $this->getSubscriptionsRegistry();
        $res = $registry->getGlobalConfiguration();
        return $res;
    }

    /**
     * @see SubscriptionsRegistry::setGlobalConfiguration()
     * @param ServiceBase $api
     * @param array $args
     * @return array saved configuration
     */
    public function putConfig(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('config'));
        $registry = $this->getSubscriptionsRegistry();
        $registry->setGlobalConfiguration($args['config']);
        $res = $registry->getGlobalConfiguration();
        return $res;
    }

    /**
     * Create instance of EmitterRegistry
     * @return EmitterRegistry
     */
    protected function getEmitterRegistry()
    {
        return EmitterRegistry::getInstance();
    }

    /**
     * Create instance of SubscriptionsRegistry
     * @return SubscriptionsRegistry
     */
    protected function getSubscriptionsRegistry()
    {
        return new SubscriptionsRegistry();
    }
}
