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

use Sugarcrm\Sugarcrm\Notification\BeanEmitter\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\ApplicationEmitter\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry;

/**
 * Subscription Registry will be our entry point to work with configuration of subscriptions.
 *
 * Class SubscriptionsRegistry
 * @package Notification
 */
class SubscriptionsRegistry
{
    /**
     * Flag disabled notification for specific emitter, event, role.
     */
    const CARRIER_VALUE_DISABLED = 'disabled';

    /**
     * Flag use default configuration of notification for specific emitter, event, role.
     */
    const CARRIER_VALUE_DEFAULT = 'default';

    /**
     * Return configuration.
     *
     * @return array configuration
     */
    public function getGlobalConfiguration()
    {
        $res = $this->getTree();
        $beans = $this->getBeans($this->getSugarQuery());

        foreach ($beans as $bean) {
            if ($this->isValidBeanForTree($res, $bean)) {
                $emitter = (string)$this->getEmitter($bean);
                $res[$emitter][$bean->event_name][$bean->filter_name][] = array(
                    $bean->carrier_name,
                    $bean->carrier_option
                );
            }
        }

        return $res;
    }

    /**
     * Create base configuration tree.
     * @return array base configuration tree
     */
    protected function getTree()
    {
        $tree = array();
        $emitters = $this->getEmitters();
        $sfList = $this->getSubscriptionFilters();
        foreach ($emitters as $module => $emitter) {
            $emitterName = (string)$emitter;
            $bean = null;
            if (is_string($module)) {
                $bean = \BeanFactory::newBean($module);
            }
            $tree[$emitterName] = array();
            $eventStrings = $emitter->getEventStrings();
            foreach ($eventStrings as $eventName) {
                $tree[$emitterName][$eventName] = array();
                $event = $emitter->getEventPrototypeByString($eventName);
                if (!is_null($bean) && $event instanceof BeanEvent) {
                    $event->setBean($bean);
                }
                foreach ($sfList as $subscriptionFilter) {
                    if ($subscriptionFilter->supports($event)) {
                        $subscriptionFilterName = (string)$subscriptionFilter;
                        $tree[$emitterName][$eventName][$subscriptionFilterName] = array();
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * List of Emitters.
     * List of Emitters if key is string that means kay consist name of module for emitter.
     *
     * @return EmitterInterface[]
     */
    protected function getEmitters()
    {
        $registry = $this->getEmitterRegistry();
        $list = array($registry->getApplicationEmitter());
        foreach ($registry->getModuleEmitters() as $module) {
            $list[$module] = $registry->getModuleEmitter($module);
        }
        return $list;
    }

    /**
     * @see EmitterRegistry::getInstance
     * @return EmitterRegistry
     */
    protected function getEmitterRegistry()
    {
        return EmitterRegistry::getInstance();
    }

    /**
     * List of SubscriptionFilters.
     *
     * @return SubscriptionFilterInterface[]
     */
    protected function getSubscriptionFilters()
    {
        $list = array();
        $registry = $this->getSubscriptionFilterRegistry();
        foreach ($registry->getFilters() as $filterName) {
            $list[] = $registry->getFilter($filterName);
        }
        return $list;
    }

    /**
     * see SubscriptionFilterRegistry::getInstance
     * @return SubscriptionFilterRegistry
     */
    protected function getSubscriptionFilterRegistry()
    {
        return SubscriptionFilterRegistry::getInstance();
    }

    /**
     * Retrieve list of NotificationCenterSubscription beans from Sugar Query
     *
     * @return \NotificationCenterSubscription[] list of beans
     */
    protected function getBeans(\SugarQuery $query, $fields = null)
    {
        if (is_null($fields)) {
            $fields = array(
                'type',
                'emitter_module_name',
                'event_name',
                'filter_name',
                'carrier_name',
                'carrier_option'
            );
        }

        $seed = $this->getNewBaseBean();
        $beans = $seed->fetchFromQuery($query, $fields);
        return $beans;
    }

    /**
     * Create new empty bean of NotificationCenterSubscription
     *
     * @return \NotificationCenterSubscription new Bean
     */
    protected function getNewBaseBean()
    {
        $bean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $bean->new_with_id = true;
        $bean->id = create_guid();
        return $bean;
    }

    /**
     * Get pre-configured Sugar query for global config.
     *
     * @return \SugarQuery pre-configured Sugar query
     * @throws \SugarQueryException
     */
    protected function getSugarQuery($userId = null)
    {
        $query = new \SugarQuery();
        $query->from($this->getNewBaseBean());
        if (is_null($userId)) {
            $query->where()->isNull('user_id');
        } else {
            $query->where()->equals('user_id', $userId);
        }

        return $query;
    }

    /**
     * Is bean in tree
     *
     * @param array $tree
     * @param \NotificationCenterSubscription $bean
     * @return bool is bean in tree
     */
    protected function isValidBeanForTree(array $tree, \NotificationCenterSubscription $bean)
    {
        $emitter = (string)$this->getEmitter($bean);
        return array_key_exists($emitter, $tree)
        && array_key_exists($bean->event_name, $tree[$emitter])
        && array_key_exists($bean->filter_name, $tree[$emitter][$bean->event_name]);
    }

    /**
     * Return emitter by NotificationCenterSubscription bean.
     *
     * @param \NotificationCenterSubscription $bean Source NotificationCenterSubscription bean
     * @return EmitterInterface emitter instance
     */
    protected function getEmitter(\NotificationCenterSubscription $bean)
    {
        $registry = $this->getEmitterRegistry();
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
     * Return configuration.
     *
     * @param GUID $userId user id
     * @return array configuration
     */
    public function getUserConfiguration($userId)
    {
        $res = $this->getTree();
        $beans = $this->getBeans($this->getSugarQuery($userId));

        foreach ($beans as $bean) {
            if ($this->isValidBeanForTree($res, $bean)) {
                $emitter = (string)$this->getEmitter($bean);
                if ($bean->carrier_name == self::CARRIER_VALUE_DISABLED) {
                    $res[$emitter][$bean->event_name][$bean->filter_name] = self::CARRIER_VALUE_DISABLED;
                } else {
                    $res[$emitter][$bean->event_name][$bean->filter_name][] = array(
                        $bean->carrier_name,
                        $bean->carrier_option
                    );
                }
            }
        }

        $this->fillDefaultConfig($res);

        return $res;
    }

    /**
     * Fill in the configuration of the default values.
     *
     * @param array $configuration for filling of the default values
     */
    protected function fillDefaultConfig(array &$configuration)
    {
        foreach ($configuration as $emitterName => $emitterConfig) {
            foreach ($emitterConfig as $eventName => $eventConfig) {
                foreach ($eventConfig as $filterName => $filterConfig) {
                    if (is_array($filterConfig) && empty($filterConfig)) {
                        $configuration[$emitterName][$eventName][$filterName] = self::CARRIER_VALUE_DEFAULT;
                    }
                }
            }
        }
    }

    /**
     * Save configuration.
     *
     * @param array $config configuration for saving
     */
    public function setGlobalConfiguration($config)
    {
        $this->setConfiguration(null, $config, true);
    }

    /**
     * Save configuration.
     *
     * @param GUID|null $userId identifier for user or null for global configuration
     * @param array $config configuration for saving
     * @param bool $delCarrierOption is necessary delete carrier option(need in global config case)
     */
    protected function setConfiguration($userId, $config, $delCarrierOption)
    {
        $beans = $this->getBeans($this->getSugarQuery($userId));

        $tree = $this->getTree();
        foreach ($tree as $emitter => $emitterConfig) {
            foreach ($emitterConfig as $event => $eventConfig) {
                foreach (array_keys($eventConfig) as $filter) {
                    if (!empty($config[$emitter][$event][$filter])) {
                        $path = $this->pathToBranch($emitter, $event, $filter);
                        $branchBeans = $this->moveBeans($beans, $path);
                        $carriers = $config[$emitter][$event][$filter];
                        if ($carriers == self::CARRIER_VALUE_DISABLED) {
                            $carriers = array(array(self::CARRIER_VALUE_DISABLED));
                        }
                        if ($carriers == self::CARRIER_VALUE_DEFAULT) {
                            $carriers = array();
                        }

                        if ($delCarrierOption) {
                            foreach (array_keys($carriers) as $key) {
                                unset($carriers[$key][1]);
                            }
                        }

                        $diff = $this->diffBranch($branchBeans, $carriers);

                        if ($userId) {
                            $path += array('user_id' => $userId);
                        }
                        $this->mergeDiff($path, $diff['delete'], $diff['insert']);
                    }
                }
            }
        }

        $this->deleteConfigBeans($beans);
    }

    /**
     * Generate search options map based on emitter name, event name, relation name
     *
     * @param string $emitter emitter name
     * @param string $event event name
     * @param string $filter subscription filter name
     * @return array generated search option
     */
    protected function pathToBranch($emitter, $event, $filter)
    {
        $emitterArr = $this->decodeEmitter($emitter);
        return array(
            'type' => $emitterArr['type'],
            'emitter_module_name' => $emitterArr['emitter_module_name'],
            'event_name' => $event,
            'filter_name' => $filter
        );
    }

    /**
     * Parse emitter name and emitter type and emitter_module_name.
     *
     * @param string $emitterName emitter name for parsing
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
     * Filter beans and move separate list.
     *
     * @param \NotificationCenterSubscription[] &$beans
     * @param array $searchOpts map field and value which should be equal as in bean
     * @return \NotificationCenterSubscription[]
     */
    protected function moveBeans(array &$beans, array $searchOpts)
    {
        $out = array();
        $ids = $this->search($beans, $searchOpts);
        foreach ($ids as $id) {
            $out[$id] = $beans[$id];
            unset($beans[$id]);
        }
        return $out;
    }

    /**
     * Searches the SugarBean list for a given search map and returns the corresponding ids if successful.
     *
     * @param \SugarBean[] $beans list of bean for
     * @param array $opts map field and value which should be equal as in bean
     * @return GUID[] list found bean ids
     */
    protected function search($beans, array $opts)
    {
        $ids = array();
        foreach ($beans as $bean) {
            if ($this->isApproach($bean, $opts)) {
                $ids[] = $bean->id;
            }
        }

        return $ids;
    }

    /**
     * Comparing is bean field values same as in search map.
     *
     * @param \SugarBean $bean for comparing
     * @param array $searchOptions map field and value which should be equal as in bean
     * @return bool is approach
     */
    protected function isApproach(\SugarBean $bean, array $searchOptions)
    {
        foreach ($searchOptions as $field => $value) {
            if (!($value == $bean->getFieldValue($field))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compare list of beans from db and carriers form config
     *
     * @param \NotificationCenterSubscription[] $beans list of beans from db
     * @param array $carriers list of carriers from config
     * @return array diff with 2 lists, which beans should be deleted, carriers that and should inserted
     */
    protected function diffBranch($beans, $carriers)
    {
        foreach ($carriers as $key => $carrier) {
            $carrier[1] = array_key_exists(1, $carrier) ? $carrier[1] : '';
            $carrierArr = array(
                'carrier_name' => $carrier[0],
                'carrier_option' => $carrier[1]
            );
            if ($this->moveBeans($beans, $carrierArr)) {
                unset($carriers[$key]);
            };
        }

        return array('delete' => $beans, 'insert' => $carriers);
    }

    /**
     * Merging diff. Update outdated beans, delete remove excess. And insert lacking careers.
     *
     * @param array $path in which branch making merge
     * @param \NotificationCenterSubscription[] $beansForDelete list of beans which contain outdated data
     * @param array $carriersForInsert list of carriers which not stored
     */
    protected function mergeDiff($path, $beansForDelete, $carriersForInsert)
    {
        foreach ($beansForDelete as $key => $bean) {
            if (!empty($carriersForInsert)) {
                $carrier = array_shift($carriersForInsert);
                $carrierArr = array(
                    'carrier_name' => $carrier[0],
                    'carrier_option' => array_key_exists(1, $carrier) ? $carrier[1] : ''
                );
                $bean->fromArray($carrierArr);
                $bean->save();
                unset($beansForDelete[$key]);
            }
        }

        $this->createCarriers($path, $carriersForInsert);
        $this->deleteConfigBeans($beansForDelete);
    }

    /**
     * Create beans based on branch path and carrier list from config.
     *
     * @param array $path path to branch in with located carriers
     * @param array $carriers list carrier data which consist of carrier name and carrier option
     */
    protected function createCarriers(array $path, array $carriers)
    {
        foreach ($carriers as $key => $carrier) {
            $carrier[1] = array_key_exists(1, $carrier) ? $carrier[1] : '';
            $bean = $this->getNewBaseBean();
            $bean->fromArray($path + array('carrier_name' => $carrier[0], 'carrier_option' => $carrier[1]));
            $bean->save();
        }
    }

    /**
     * Deleting list of beans.
     *
     * @param \SugarBean[] $beans list for deleting
     */
    protected function deleteConfigBeans(array $beans)
    {
        foreach ($beans as $bean2Del) {
            $bean2Del->mark_deleted($bean2Del->id);
        }
    }

    /**
     * Save configuration for user.
     *
     * @param  $userId
     * @param array $config configuration for saving
     */
    public function setUserConfiguration($userId, $config)
    {
        $this->setConfiguration($userId, $config, false);
    }

    /**
     * Returns array of suitable users with their carrier preference.
     *
     * @param EventInterface $event for filtering
     * @return array of suitable users with their carrier preference
     */
    public function getUsers(EventInterface $event)
    {
        $globalConfig = $this->getGlobalEventConfig($event);
        $sfList = $this->getSupportedFilters($event);
        usort($sfList, function (SubscriptionFilterInterface $a, SubscriptionFilterInterface $b) {
            $a = $a->getOrder();
            $b = $b->getOrder();
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });
        $sfList = array_reverse($sfList);

        $result = array();
        foreach ($sfList as $subscriptionFilter) {
            $query = new \SugarQuery();
            $userAlias = $subscriptionFilter->filterQuery($event, $query);

            $joinOptions = array(
                'team_security' => false,
                'joinType' => 'LEFT'
            );
            $join = $query->joinTable('notification_subscription', $joinOptions);
            $joinOn = $join->on()->equalsField('notification_subscription.user_id', "{$userAlias}.id")
                ->equals('notification_subscription.deleted', '0');
            $this->eventWhere($event, $joinOn, $subscriptionFilter);

            $query->select(array(
                array("{$userAlias}.id", 'user_id'),
                'notification_subscription.carrier_name',
                'notification_subscription.carrier_option',
            ));
            foreach ($query->execute() as $row) {
                $userId = $row['user_id'];
                if (is_null($row['carrier_name'])) {
                    $row = $globalConfig[(string)$subscriptionFilter] + $row;
                }
                if (!array_key_exists($userId, $result) || $result[$userId]['filter'] != (string)$subscriptionFilter) {
                    $result[$userId] = array(
                        'filter' => (string)$subscriptionFilter,
                        'config' => array(),
                    );
                }
                $result[$userId]['config'][] = array($row['carrier_name'], $row['carrier_option']);
            }
        }

        return $result;
    }

    /**
     * Returns array global of carrier preference for the event.
     *
     * @param EventInterface $event for filtering
     * @return array global of carrier preference for the event
     * @throws \SugarQueryException
     */
    protected function getGlobalEventConfig(EventInterface $event)
    {
        $bean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $query = new \SugarQuery();
        $query->from($bean);
        $query->where()->isNull('user_id');
        $this->eventWhere($event, $query->where());

        $globalConfig = array();
        foreach ($bean->fetchFromQuery($query) as $row) {
            $globalConfig[$row->filter_name] = array(
                'carrier_name' => $row->carrier_name,
                'carrier_option' => $row->carrier_option
            );
        }
        return $globalConfig;
    }

    /**
     * Preparing where for filtering carrier preference for the event.
     *
     * @param EventInterface $event for filtering
     * @param \SugarQuery_Builder_Where $where which will be prepared
     * @param SubscriptionFilterInterface|null $subscriptionFilter subscription Filter if necessary the single
     */
    protected function eventWhere(
        EventInterface $event,
        \SugarQuery_Builder_Where $where,
        SubscriptionFilterInterface $subscriptionFilter = null
    ) {
        $emitterType = $event instanceof ApplicationEvent ? 'application' : 'module';
        $emitterModuleType = $event instanceof BeanEvent ? $event->getModuleName() : '';
        $where->equals('notification_subscription.event_name', (string)$event)
            ->equals('notification_subscription.type', $emitterType)
            ->equals('notification_subscription.emitter_module_name', $emitterModuleType);
        if (!is_null($subscriptionFilter)) {
            $where->equals('notification_subscription.filter_name', (string)$subscriptionFilter);
        }
    }

    /**
     * Return list of Subscription Filter which support the event.
     *
     * @param EventInterface $event for checking
     * @return SubscriptionFilterInterface[] list of Subscription Filter which support the event
     */
    private function getSupportedFilters(EventInterface $event)
    {
        $sfList = $this->getSubscriptionFilters();
        $supported = array();
        foreach ($sfList as $subscriptionFilter) {
            if ($subscriptionFilter->supports($event)) {
                $supported[] = $subscriptionFilter;
            }
        }
        return $supported;
    }
}
