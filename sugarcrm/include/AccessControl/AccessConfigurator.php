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

namespace Sugarcrm\Sugarcrm\AccessControl;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.

/**
 * Class AccessConfigurator, this class offers APIs to retrieve data from access_config.json
 *
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class AccessConfigurator
{
    /**
     * access control configuration file.
     */
    const ACCESS_CONFIG_FILE = 'access_config.json';

    /**
     * instance
     * @var AccessConfigurator
     */
    protected static $instance;


    /**
     * access configuration data, cached in memory for session
     * @var array
     */
    protected $data = [];

    /**
     * private ctor
     * AccessConfigurator constructor
     */
    private function __construct()
    {
    }

    /**
     * Singleton implementation
     * @return AccessConfigurator
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new AccessConfigurator();
        }

        return self::$instance;
    }

    /**
     *
     * get access controlled list
     * @param string $key
     * @param bool $useCache
     * @return array|mixed
     */
    public function getAccessControlledList(string $key, bool $useCache = true)
    {
        $cacheKey = 'ac-' . $key;
        if ($useCache) {
            $list = sugar_cache_retrieve($cacheKey);
            if (!empty($list)) {
                return $list;
            }
        }

        if (empty($this->data)) {
            $this->data = $this->loadAccessConfig();
        }

        if (isset($this->data[$key])) {
            if ($useCache) {
                sugar_cache_put($cacheKey, $this->data[$key]);
            }
            return $this->data[$key];
        }

        return [];
    }

    /**
     * get access controlled list by license types
     *
     * @param array $types
     * @param bool $useCache
     * @return array|mixed
     */
    public function getNotAcceccibleModuleListByLicenseTypes(array $types, bool $useCache = true)
    {
        if (empty($types)) {
            return [];
        }

        $cacheKey = 'ac-' . AccessControlManager::MODULES_KEY . '-' . implode('-', $types);
        if ($useCache) {
            $list = sugar_cache_retrieve($cacheKey);
            if (!empty($list)) {
                return $list;
            }
        }

        $controlledList = $this->getAccessControlledList(AccessControlManager::MODULES_KEY, $useCache);

        $notAccessibleList = [];

        // find out inaccessible modules
        if (!empty($controlledList)) {
            foreach ($controlledList as $module => $allowedTypes) {
                if (empty(array_intersect($types, $allowedTypes))) {
                    $notAccessibleList[$module] = true;
                }
            }
        }
        if ($useCache) {
            sugar_cache_put($cacheKey, $notAccessibleList);
        }

        return $notAccessibleList;
    }

    /**
     * load access config from disk
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function loadAccessConfig()
    {
        if (file_exists(self::ACCESS_CONFIG_FILE)) {
            $accConfig = file_get_contents(self::ACCESS_CONFIG_FILE);
            return json_decode($accConfig, true);
        }

        throw new \Exception("access config file doesn't exist: " . self::ACCESS_CONFIG_FILE);
    }
}
