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

require_once 'modules/NotificationCenter/clients/base/api/GlobalConfigApi.php';

/**
 * API work with Subscription Registry and with Carriers Status get/put configuration for user.
 *
 * Class NotificationCenterConfigApi
 */
class NotificationCenterConfigApi extends GlobalConfigApi
{
    const CARRIER_STATUS_NAME = 'carrierStatus';
    const CARRIER_STATUS_CATEGORY = 'notificationCenter';

    /**
     * {@inheritDoc}
     */
    public function registerApiRest()
    {
        return array(
            'getUserConfig' => array(
                'reqType' => 'GET',
                'path' => array('NotificationCenter', 'config'),
                'pathVars' => array(),
                'method' => 'getUserConfig',
                'shortHelp' => 'Return configuration of sets of user delivery config API.',
                'longHelp' => '',
            ),
            'updateUserConfig' => array(
                'reqType' => 'PUT',
                'path' => array('NotificationCenter', 'config'),
                'pathVars' => array(),
                'method' => 'updateUserConfig',
                'shortHelp' => 'Update configuration of sets of user delivery config API.',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Update Subscription Registry and Carriers Status configurations for current user.
     * @param ServiceBase $api
     * @param array $args
     * @return array configuration
     */
    public function updateUserConfig(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('personal'));
        $this->requireArgs($args['personal'], array('carriers', 'config'));
        $this->updatePersonalCarriers($api->user, $args['personal']['carriers']);
        $this->getSubscriptionsRegistry()->setUserConfiguration($api->user->id, $args['personal']['config']);
        return $this->getUserConfig($api, $args);
    }

    /**
     * Update carriers configuration for current user.
     * @param User $user current user
     * @param array $carriers carriers configuration for current user
     */
    protected function updatePersonalCarriers(User $user, array $carriers)
    {
        $carriersStatus = $user->getPreference(static::CARRIER_STATUS_NAME, static::CARRIER_STATUS_CATEGORY);
        $globalCarriers = $this->getCarriersConfig();
        foreach ($this->getCarrierRegistry()->getCarriers() as $module) {
            if (!empty($globalCarriers[$module]['status'])) {
                $status = false;
                if (!empty($carriers[$module]['status'])) {
                    $status = true;
                }
                $carriersStatus[$module] = $status;
            }
        }
        $user->setPreference(static::CARRIER_STATUS_NAME, $carriersStatus, 0, static::CARRIER_STATUS_CATEGORY);
    }

    /**
     * Return Subscription Registry and Carriers Status configurations for current user.
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array configuration for current user
     */
    public function getUserConfig(ServiceBase $api, array $args)
    {
        $subscriptionsRegistry = $this->getSubscriptionsRegistry();
        return array(
            'global' => array(
                'carriers' => $this->getCarriersConfig(),
                'config' => $subscriptionsRegistry->getGlobalConfiguration()
            ),
            'personal' => array(
                'carriers' => $this->getPersonalCarriers($api->user),
                'config' => $subscriptionsRegistry->getUserConfiguration($api->user->id)
            )
        );
    }

    /**
     * Return carriers configuration for current user.
     * @param User $user
     * @return array carriers configuration for current user
     */
    protected function getPersonalCarriers(User $user)
    {
        $carriers = array();
        $carriersStatus = (array)$user->getPreference(static::CARRIER_STATUS_NAME, static::CARRIER_STATUS_CATEGORY);
        $globalCarriers = $this->getCarriersConfig();
        foreach ($this->getCarrierRegistry()->getCarriers() as $module) {
            $carrier = $this->getCarrierRegistry()->getCarrier($module);
            $addressType = $carrier->getAddressType();
            $options = new stdClass();
            foreach ($addressType->getOptions($user) as $optionKey => $option) {
                $options->{$optionKey} = $option;
            }

            $carriers[$module] = array(
                // If personal is not yet set up, take it from global config.
                'status' => array_key_exists($module, $carriersStatus) ?
                    (!empty($carriersStatus[$module])) :
                    $globalCarriers[$module]['status'],

                'selectable' => $addressType->isSelectable(),
                'options' => $options,
            );
        }
        return $carriers;
    }
}
