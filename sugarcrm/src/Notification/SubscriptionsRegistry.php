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

namespace Sugarcrm\Sugarcrm\Notification;

/**
 * Subscription Registry will be our entry point to work with configuration of subscriptions.
 *
 * Class SubscriptionsRegistry
 * @package Notification
 */
class SubscriptionsRegistry
{
    /**
     * Return configuration.
     *
     * @return array configuration
     */
    public function getGlobalConfiguration()
    {
        $res = array();
        $beans = $this->getBeans($this->getSugarQuery());

        foreach ($beans as $bean) {
            $emitter = (string)$this->getEmitter($bean);

            if (!array_key_exists($emitter, $res)) {
                $res[$emitter] = array();
            }
            if (!array_key_exists($bean->event_name, $res[$emitter])) {
                $res[$emitter][$bean->event_name] = array();
            }
            if (!array_key_exists($bean->relation_name, $res[$emitter][$bean->event_name])) {
                $res[$emitter][$bean->event_name][$bean->relation_name] = array();
            }
            $res[$emitter][$bean->event_name][$bean->relation_name][$bean->carrier_name] = $bean->carrier_option;
        }

        return $res;
    }

    /**
     * Retrieve list of NotificationCenterSubscription beans from Sugar Query
     *
     * @return NotificationCenterSubscription[] list of beans
     */
    protected function getBeans(\SugarQuery $query, $fields = null)
    {
        if (is_null($fields)) {
            $fields = array(
                'type',
                'emitter_module_name',
                'event_name',
                'relation_name',
                'carrier_name',
                'carrier_option'
            );
        }

        $seed = $this->getNewBean();
        $beans = $seed->fetchFromQuery($query, $fields);
        return $beans;
    }

    /**
     * Create new empty bean of NotificationCenterSubscription
     *
     * @return \NotificationCenterSubscription new Bean
     */
    protected function getNewBean()
    {
        return \BeanFactory::newBean('NotificationCenterSubscriptions');
    }

    /**
     * Get pre-configured Sugar query for global config.
     *
     * @return \SugarQuery pre-configured Sugar query
     * @throws \SugarQueryException
     */
    protected function getSugarQuery()
    {
        $query = new \SugarQuery();
        $query->from($this->getNewBean());
        $query->where()->isNull('user_id');
        return $query;
    }

    /**
     * Return emitter by NotificationCenterSubscription bean.
     *
     * @param \NotificationCenterSubscription $bean Source NotificationCenterSubscription bean
     * @return EmitterInterface emitter instance
     */
    protected function getEmitter(\NotificationCenterSubscription $bean)
    {
        $registry = $this->getRegistry();
        switch ($bean->type) {
            case 'application':
                $emitter = $registry->getApplicationEmitter();
                break;
            case 'bean':
                $emitter = $registry->getBeanEmitter();
                break;
            case 'module':
                $emitter = $registry->getModuleEmitter($bean->emitter_module_name);
                break;
            default:
                throw new \LogicException('Cannot create emitter for target bean');
                break;
        }
        return $emitter;
    }

    /**
     * @see EmitterRegistry::getInstance
     * @return EmitterRegistry
     */
    protected function getRegistry()
    {
        return EmitterRegistry::getInstance();
    }

    /**
     * Save configuration.
     *
     * @param array $config configuration for saving
     */
    public function setGlobalConfiguration($config)
    {
        $ids = array();
        foreach ($config as $emitterName => $emitterConfig) {
            foreach ($emitterConfig as $eventName => $eventConfig) {
                foreach ($eventConfig as $relationName => $relationConfig) {
                    foreach ($relationConfig as $carrierName => $carrierOption) {
                        $emitterArr = $this->decodeEmitter($emitterName);
                        $bean = $this->getNewBean();
                        $bean->type = $emitterArr['type'];
                        $bean->emitter_module_name = $emitterArr['emitter_module_name'];
                        $bean->event_name = $eventName;
                        $bean->relation_name = $relationName;
                        $bean->carrier_name = $carrierName;
                        $bean->carrier_option = $carrierOption;
                        $this->checkExisting($bean);
                        $bean->save();
                        $ids[] = $bean->id;
                    }
                }
            }
        }
        $this->deleteOther($ids);
    }

    /**
     * Parse emitter name and emitter type and emitter_module_name.
     *
     * @param $emitterName emitte name for parsing
     * @return array emitter type and emitter_module_name
     */
    protected function decodeEmitter($emitterName)
    {
        $res = array('type' => null, 'emitter_module_name' => null);
        switch ($emitterName) {
            case 'ApplicationEmitter':
                $res['type'] = 'application';
                break;
            case 'BeanEmitter':
                $res['type'] = 'bean';
                break;
            default:
                $res['type'] = 'module';
                $res['emitter_module_name'] = $emitterName;
                break;
        }
        return $res;
    }

    /**
     * Function check is bean exists.
     *
     * @param \NotificationCenterSubscription $bean for checking
     */
    protected function checkExisting(\NotificationCenterSubscription $bean)
    {
        $query = $this->getSugarQuery();
        foreach (array('type', 'emitter_module_name', 'event_name', 'relation_name', 'carrier_name') as $field) {
            $query->where()->equals($field, $bean->getFieldValue($field));
        }

        $beans = $bean->fetchFromQuery($query);
        if (0 == count($beans)) {
            $bean->new_with_id = true;
            $bean->id = create_guid();
        } else {
            $ids = array_keys($beans);
            $bean->retrieve($ids[0]);
        }
    }

    /**
     * Delete subscriptions but $ids.
     *
     * @param string[] $ids list of ids that will not be deleted
     */
    protected function deleteOther($ids)
    {
        if (count($ids) > 0) {
            $query = $this->getSugarQuery();
            $query->where()->notIn('id', $ids);
        } else {
            throw new \LogicException('Can not be fully delete global settings');
        }

        $beans = $this->getBeans($query, array('id', 'deleted'));
        foreach ($beans as $bean) {
            $bean->mark_deleted($bean->id);
        };
    }
}
