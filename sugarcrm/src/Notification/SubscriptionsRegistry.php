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

namespace Sugarcrm\Sugarcrm\Notification;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;
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
        $beanEmitterName = (string)$this->getEmitterRegistry()->getBeanEmitter();
        $tree[$beanEmitterName] = $this->getBeanEmitterTree($tree);

        foreach ($tree as $emitter => $config) {
            if (empty($config)) {
                unset($tree[$emitter]);
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
     * Create base bean emitter configuration tree.
     *
     * @param array $tree base configuration tree
     * @return array base configuration bean emitter tree
     */
    protected function getBeanEmitterTree(array $tree)
    {
        $emitterTree = array();
        $emitterRegistry = $this->getEmitterRegistry();
        foreach ($emitterRegistry->getModuleEmitters() as $emitterName) {
            $emitter = $emitterRegistry->getModuleEmitter($emitterName);
            foreach ($emitter->getEventStrings() as $eventName) {
                $event = $emitter->getEventPrototypeByString($eventName);
                if ($event instanceof BeanEvent) {
                    if (!array_key_exists($eventName, $emitterTree)) {
                        $emitterTree[$eventName] = array();
                    }
                    $emitterTree[$eventName] += $tree[$emitterName][$eventName];
                }
            }
        }

        return $emitterTree;
    }

    /**
     * Gets an array of NotificationCenterSubscription beans from a SugarQuery
     *
     * @param \SugarQuery $query - Query object for fetching beans
     * @param array $fields A list of fields to populate in the beans
     * @return \NotificationCenterSubscription[] list of beans
     */
    protected function getBeans(\SugarQuery $query, array $fields = null)
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
     * @param string|null $userId User id
     * @return \SugarQuery pre-configured Sugar query
     * @throws \SugarQueryException
     */
    protected function getSugarQuery($userId = null)
    {
        $query = $this->getBaseSugarQuery();
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
     * @param string $userId user id
     * @return array configuration
     */
    public function getUserConfiguration($userId)
    {
        $res = $this->getTree();
        $beans = $this->getBeans($this->getSugarQuery($userId));

        foreach ($beans as $bean) {
            if ($this->isValidBeanForTree($res, $bean)) {
                $emitter = (string)$this->getEmitter($bean);
                $res[$emitter][$bean->event_name][$bean->filter_name][] = array(
                    $bean->carrier_name,
                    $bean->carrier_option
                );
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
     * @param string|null $userId identifier for user or null for global configuration
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
                        $reducesBeans = $this->reduceBeans($beans, $path);
                        $carriers = $config[$emitter][$event][$filter];
                        if ($carriers == self::CARRIER_VALUE_DEFAULT) {
                            $carriers = array();
                        }

                        if ($delCarrierOption) {
                            foreach (array_keys($carriers) as $key) {
                                unset($carriers[$key][1]);
                            }
                        }

                        $diff = $this->getDiff($reducesBeans, $carriers);

                        if ($userId) {
                            $path += array('user_id' => $userId);
                        }
                        $this->processDiff($path, $diff['delete'], $diff['insert']);
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
     * @param integer $limit count of max count reduce beans
     * @return \NotificationCenterSubscription[] reduced beans
     */
    protected function reduceBeans(array &$beans, array $searchOpts, $limit = 0)
    {
        $out = array();

        foreach ($beans as $key => $bean) {
            if ($this->isSuitable($bean, $searchOpts)) {
                $out[$bean->id] = $bean;
                unset($beans[$key]);
                if ($limit > 0 && $limit == count($out)) {
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * Comparing is bean field values same as in search map.
     *
     * @param \SugarBean $bean for comparing
     * @param array $searchOptions map field and value which should be equal as in bean
     * @return bool is approach
     */
    protected function isSuitable(\SugarBean $bean, array $searchOptions)
    {
        foreach ($searchOptions as $field => $value) {
            if ($value != $bean->getFieldValue($field)) {
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
    protected function getDiff($beans, $carriers)
    {
        foreach ($carriers as $key => $carrier) {
            $carrier[1] = array_key_exists(1, $carrier) ? $carrier[1] : '';
            $carrierFilter = array(
                'carrier_name' => $carrier[0],
                'carrier_option' => $carrier[1]
            );
            // Need to reduce out a list of only one bean, because the other is duplicates and go on deleting.
            if ($this->reduceBeans($beans, $carrierFilter, 1)) {
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
    protected function processDiff(array $path, array $beansForDelete, array $carriersForInsert)
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
     * @param string $userId user id
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
        $sfList = $this->getUsersFilters($event);
        $config = array();
        foreach ($sfList as $subscriptionFilter) {
            $filterName = (string)$subscriptionFilter;
            $data = $this->getUsersEventConfig($event, $subscriptionFilter);
            foreach ($data as $userId => $userData) {
                $userConfig = $this->calculateUserConfig(
                    $userData[$filterName],
                    array_key_exists($filterName, $globalConfig) ? $globalConfig[$filterName] : array()
                );
                if (!empty($userConfig)) {
                    $config[$userId] = array(
                        'filter' => $filterName,
                        'config' => $userConfig
                    );
                }
            }
        }

        return $config;
    }

    /**
     * Returns tree of users config tree for the event.
     *
     * @param EventInterface $event for filtering users config
     * @param SubscriptionFilterInterface $filter
     * @return array users config tree
     */
    protected function getUsersEventConfig(EventInterface $event, SubscriptionFilterInterface $filter)
    {
        $list = $this->getUsersList($event, $filter);
        $filterName = (string)$filter;
        $config = array();
        foreach ($list as $row) {
            $userId = $row['user_id'];
            $type = $this->normalizeType($row['type']);
            $carrier = trim($row['carrier_name']);

            if (!array_key_exists($userId, $config)) {
                $config[$userId] = array($filterName => array());
            }

            // not found subscription trigger
            if (empty($carrier) && empty($row['type'])) {
                continue;
            }

            if (!array_key_exists($type, $config[$userId][$filterName])) {
                $config[$userId][$filterName][$type] = array();
            }

            if (!array_key_exists($carrier, $config[$userId][$filterName][$type])) {
                $config[$userId][$filterName][$type][$carrier] = array();
            }

            $config[$userId][$filterName][$type][$carrier][] = $row['carrier_option'];
        }
        return $config;
    }

    /**
     * Calculation user configuration.
     *
     * @param array $userData user config data
     * @param array $globalData global config data
     * @return array calculated user configuration
     */
    public function calculateUserConfig(array $userData, array $globalData)
    {
        $config = array();
        if (array_key_exists('main', $userData)) {
            $config = $userData['main'];
        } elseif (array_key_exists('bean', $userData)) {
            $config = $userData['bean'];
        }

        if (empty($config)) {
            if (array_key_exists('main', $globalData)) {
                $config = $globalData['main'];
            } elseif (array_key_exists('bean', $globalData)) {
                $config = $globalData['bean'];
            }
        }

        if (array_key_exists('', $config)) {
            return array();
        } else {
            return $this->formatParsedUsersData($config);
        }
    }

    /**
     * Re-format array of suitable users with their carrier preference.
     *
     * @param array $userConfig pre ready user config
     * @return array of suitable users with their carrier preference
     */
    protected function formatParsedUsersData(array $userConfig)
    {
        $result = array();
        foreach ($userConfig as $carrierName => $carrierOptions) {
            $carrierOptions = array_unique($carrierOptions);
            foreach ($carrierOptions as $carrierOption) {
                $result[] = array($carrierName, $carrierOption);
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
        $list = $this->getGlobalEventList($event);
        $globalConfig = array();

        foreach ($list as $row) {
            $filter = $row['filter_name'];
            $type = $this->normalizeType($row['type']);
            $carrier = trim($row['carrier_name']);

            if (!array_key_exists($filter, $globalConfig)) {
                $globalConfig[$filter] = array();
            }
            if (!array_key_exists($type, $globalConfig[$filter])) {
                $globalConfig[$filter][$type] = array();
            }

            if (!array_key_exists($carrier, $globalConfig[$filter][$type])) {
                $globalConfig[$filter][$type][$carrier] = array();
            }

            $globalConfig[$filter][$type][$carrier][] = $row['carrier_option'];
        }

        return $globalConfig;
    }

    /**
     * Normalize type for ease use.
     *
     * @param string $type for normalization
     * @return string normalized type
     */
    protected function normalizeType($type)
    {
        if ('bean' != $type) {
            $type = 'main';
        }
        return $type;
    }

    /**
     * Returns list of stored data for the event for next processing.
     *
     * @param EventInterface $event for filtering
     * @return array list of raw data
     * @throws \SugarQueryException
     */
    protected function getGlobalEventList(EventInterface $event)
    {
        $query = $this->getSugarQuery(null);
        $this->eventWhere($event, $query->where());
        $list = $query->execute();
        return $list;
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
        $where->equals('notification_subscription.event_name', (string)$event);
        if ($event instanceof ApplicationEvent) {
            $where->equals('notification_subscription.type', 'application')
                ->equals('notification_subscription.emitter_module_name', '');
        } elseif ($event instanceof ModuleEventInterface) {
            if ($event instanceof BeanEvent) {
                $emitterCondition = $where->queryOr();
                $emitterCondition->queryAnd()->equals('notification_subscription.type', 'bean')
                    ->equals('notification_subscription.emitter_module_name', '');
                $emitterCondition->queryAnd()->equals('notification_subscription.type', 'module')
                    ->equals('notification_subscription.emitter_module_name', $event->getModuleName());
            } else {
                $where->equals('notification_subscription.type', 'module')
                    ->equals('notification_subscription.emitter_module_name', $event->getModuleName());
            }
        }

        if (!is_null($subscriptionFilter)) {
            $where->equals('notification_subscription.filter_name', (string)$subscriptionFilter);
        }
    }

    /**
     * Return sorted list of subscription filters for event
     *
     * @param EventInterface $event for venerating list
     * @return SubscriptionFilterInterface[] sorted list of subscription filters for event
     */
    protected function getUsersFilters(EventInterface $event)
    {
        $sfList = $this->getSupportedFilters($event);
        usort($sfList, function (SubscriptionFilterInterface $a, SubscriptionFilterInterface $b) {
            $a = $a->getOrder();
            $b = $b->getOrder();
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? 1 : -1;
        });
        return $sfList;
    }

    /**
     * Return list of Subscription Filter which support the event.
     *
     * @param EventInterface $event for checking
     * @return SubscriptionFilterInterface[] list of Subscription Filter which support the event
     */
    protected function getSupportedFilters(EventInterface $event)
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

    /**
     * Returns list of stored user config for the event for next processing.
     *
     * @param EventInterface $event for filtering users config
     * @param SubscriptionFilterInterface $subscriptionFilter for filtering users config
     * @return array list of raw data
     */
    protected function getUsersList(EventInterface $event, SubscriptionFilterInterface $subscriptionFilter)
    {
        $query = $this->getBaseSugarQuery();
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
            'notification_subscription.type',
        ));

        $list = $query->execute();
        return $list;
    }

    /**
     * Return base SugarQuery instance.
     *
     * @return \SugarQuery sugar query instance.
     */
    protected function getBaseSugarQuery()
    {
        return new \SugarQuery();
    }
}
