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

namespace Sugarcrm\Sugarcrm\SearchEngine;

use Sugarcrm\Sugarcrm\Logger\LoggerTransition;
use Psr\Log\LoggerInterface;

/**
 *
 * Helper class around MetaDataManager for SearchEngine
 *
 */
class MetaDataHelper
{
    /**
     * @var \MetaDataManager
     */
    protected $mdm;

    /**
     * @var string Metadata hash
     */
    protected $mdmHash;

    /**
     * @var \SugarCacheAbstract
     */
    protected $sugarCache;

    /**
     * @var LoggerTransition
     */
    protected $logger;

    /**
     * Disable caching
     * @var boolean
     */
    protected $disableCache = false;

    /**
     * Cross module aggregations definitions
     * @var array
     */
    protected $crossModuleAggDefs = array();

    /**
     * @param \MetaDataManager $mdm
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->mdm = \MetaDataManager::getManager();
        $this->sugarCache = \SugarCache::instance();
        $this->updateHash();
    }

    /**
     * Enable/disable cache
     * @param boolean $toggle
     */
    public function disableCache($toggle)
    {
        $this->disableCache = (bool) $toggle;
        if ($toggle) {
            $this->logger->critical("MetaDataHelper: Performance degradation, cache disabled.");
        }
    }

    /**
     * Refresh cache. All keys from MetaDataHepler are prefixed with the hash
     * from MetaDataManager. Calling this method will get the current hash
     * and will automatically invalidate previous cache entries if anything
     * changed for MetaDataManager.
     */
    public function updateHash()
    {
        $this->mdmHash = $this->mdm->getCachedMetadataHash(new \MetaDataContextDefault());

        // Make sure we have a hash available, if not lets temporarily disable
        // our cache backend.
        if (empty($this->mdmHash)) {
            $this->disableCache(true);
            $this->logger->warning("MetaDataHelper: No MetaDataHelper hash value available.");
        } else {
            $this->logger->debug("MetaDataHelper: Using hash " . $this->mdmHash);
        }
    }

    /**
     * Return system wide enabled FTS modules.
     * @return array
     */
    public function getAllEnabledModules()
    {
        $cacheKey = 'enabled_modules';
        if ($list = $this->getCache($cacheKey)) {
            return $list;
        }

        $list = array();
        $modules = $this->mdm->getModuleList();
        foreach ($modules as $module) {
            $vardefs = $this->getModuleVardefs($module);
            if (!empty($vardefs['full_text_search'])) {
                $list[] = $module;
            }
        }
        return $this->setCache($cacheKey, $list);
    }

    /**
     * Get vardefs for given module
     * @param string $module
     * @return array
     */
    public function getModuleVardefs($module)
    {
        $cacheKey = 'vardefs_' . $module;
        if ($vardefs = $this->getCache($cacheKey)) {
            return $vardefs;
        }
        return $this->setCache($cacheKey, $this->mdm->getVarDef($module));
    }

    /**
     * Return vardefs for FTS enabled fields
     * @param string $module Module name
     * @param boolean $allowTypeOverride
     * @return array
     */
    public function getFtsFields($module, $allowTypeOverride = true)
    {
        $cacheKey = 'ftsfields_' . $module;
        if ($allowTypeOverride) {
            $cacheKey .= '_override';
        }

        if ($ftsFields = $this->getCache($cacheKey)) {
            return $ftsFields;
        }

        $ftsFields = array();
        $vardefs = $this->getModuleVardefs($module);
        foreach ($vardefs['fields'] as $field => $defs) {

            // skip field if no type has been defined
            if (empty($defs['type'])) {
                continue;
            }

            if (isset($defs['full_text_search']) && !empty($defs['full_text_search']['enabled'])) {
                // the type in 'full_text_search' overrides the type in the field
                if ($allowTypeOverride && !empty($defs['full_text_search']['type'])) {
                    $defs['type'] = $defs['full_text_search']['type'];
                }
                $ftsFields[$field] = $defs;
            }
        }
        return $this->setCache($cacheKey, $ftsFields);
    }

    /**
     * Return list of modules which are available for a given user.
     * @param \User $user
     * @return array
     */
    public function getAvailableModulesForUser(\User $user)
    {
        $cacheKey = 'modules_user_' . $user->id;
        if ($list = $this->getCache($cacheKey)) {
            return $list;
        }

        $list = array();
        foreach ($this->getAllEnabledModules() as $module) {
            $seed = \BeanFactory::getBean($module);
            if ($seed->ACLAccess('ListView', array('user' => $user))) {
                $list[] = $module;
            }
        }
        return $this->setCache($cacheKey, $list);
    }

    /**
     * Verify if given module is FTS enabled
     * @param unknown $module
     * @return boolean
     */
    public function isModuleEnabled($module)
    {
        return in_array($module, $this->getAllEnabledModules());
    }

    /**
     * Verify if a module is available for given user
     * @param string $module
     * @param \User $user
     * @return boolean
     */
    public function isModuleAvailableForUser($module, \User $user)
    {
        return in_array($module, $this->getAvailableModulesForUser($user));
    }

    /**
     * Get auto increment fields for module.
     * @param string $module
     * @return array
     */
    public function getFtsAutoIncrementFields($module)
    {
        $cacheKey = 'autoincr_' . $module;
        if ($incFields = $this->getCache($cacheKey)) {
            return $incFields;
        }

        $incFields = array();
        foreach ($this->getFtsFields($module) as $field => $defs) {
            if (!empty($defs['auto_increment'])) {
                $incFields[] = $defs['name'];
            }
        }
        return $this->setCache($cacheKey, $incFields);
    }


    /**
     * Get cached content
     * @param string $key Cache key
     * @param null|mixed
     */
    protected function getCache($key)
    {
        if ($this->disableCache) {
            return null;
        }
        $key = $this->getRealCacheKey($key);
        return $this->sugarCache->$key;
    }

    /**
     * Set value in cache
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function setCache($key, $value)
    {
        if (!$this->disableCache) {
            $key = $this->getRealCacheKey($key);
            $this->sugarCache->set($key, $value);
        }
        return $value;
    }

    /**
     * Get cache key. This method is not supposed to be called directly.
     * Use `$this->getCache` or `$this->setCache` as both implicitly use
     * this method.
     * @param string $key
     * @return string
     */
    protected function getRealCacheKey($key)
    {
        return "mdmhelper_" . $this->mdmHash . "_" . $key;
    }

    /**
     * Get the aggregation definitions of a given module.
     * @param string $module : the name of module
     * @return array
     */
    public function getModuleAggregations($module)
    {
        $aggDefs = $this->getAllAggDefs();
        if (isset($aggDefs['modules'][$module])) {
            return $aggDefs['modules'][$module];
        }
        return array();
    }

    /**
     * Get the aggregations definitions shared by multiple modules.
     * @return array
     */
    public function getCrossModuleAggregations()
    {
        $aggDefs = $this->getAllAggDefs();
        return $aggDefs['cross'];
    }

    /**
     * Get all aggregation definitions
     * @return array
     */
    protected function getAllAggDefs()
    {
        $cacheKey = 'aggdefs';
        if ($list = $this->getCache($cacheKey)) {
            return $list;
        }

        $allAggDefs = array(
            'cross' => array(),
            'modules' => array(),
        );
        foreach ($this->getAllEnabledModules() as $module) {
            $aggDefs = $this->getAllAggDefsModule($module);
            $allAggDefs['cross'] = array_merge($allAggDefs['cross'], $aggDefs['cross']);
            $allAggDefs['modules'][$module] = $aggDefs['module'];
        }
        return $this->setCache($cacheKey, $aggDefs);
    }

    /**
     * Get all aggregation definitions for given module
     * @param string $module Module name
     * @return array
     */
    protected function getAllAggDefsModule($module)
    {
        $aggDefs = array(
            'cross' => array(),
            'module' => array(),
        );

        $fieldDefs = $this->getFtsFields($module);
        foreach ($fieldDefs as $fieldName => $fieldDef) {

            // skip the field without aggregation defs
            if (empty($fieldDef['full_text_search']['aggregation'])) {
                continue;
            }

            $aggDef = $fieldDef['full_text_search']['aggregation'];

            // the type must be defined
            if (is_array($aggDef) && !empty($aggDef['type'])) {

                // set empty options array if nothing specified
                if (empty($aggDef['options']) || !is_array($aggDef['options'])) {
                    $aggDef['options'] = array();
                }

                // split module vs cross module aggregations
                if (!empty($aggDef['cross_module'])) {
                    $aggDefs['cross'][$fieldName] = $aggDef;
                } else {
                    $aggDefs['module'][$module . '.' . $fieldName] = $aggDef;
                }
            }
        }
        return $aggDefs;
    }
}
