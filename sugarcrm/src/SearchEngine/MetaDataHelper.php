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
    private $mdm;

    /**
     * @param \MetaDataManager $mdm
     */
    public function __construct(\MetaDataManager $mdm = null)
    {
        $this->mdm = $mdm ?: \MetaDataManager::getManager();
    }

    /**
     * Return system wide enabled FTS modules.
     *
     * TODO: cleanup unified_search_display_modules mess
     *
     * @return array
     */
    public function getAllEnabledModules()
    {
        $list = array();
        $modules = $this->mdm->getModuleList();
        foreach ($modules as $module) {
            $vardefs = $this->getModuleVardefs($module);
            if (!empty($vardefs['full_text_search'])) {
                $list[] = $module;
                // TODO - do we need to check for at least one FTS field ?
            }
        }
        return $list;
    }

    /**
     * Get vardefs for given module
     * @param string $module
     * @return array
     */
    public function getModuleVardefs($module)
    {
        return $this->mdm->getVarDef($module);
    }

    /**
     * Return vardefs for FTS enabled fields
     * @param string $module Module name
     * @return array
     */
    public function getFtsFields($module)
    {
        $ftsFields = array();
        $vardefs = $this->getModuleVardefs($module);
        foreach ($vardefs['fields'] as $field => $defs) {
            if (isset($defs['full_text_search']) && !empty($defs['full_text_search']['enabled'])) {
                //the type in 'full_text_search' overrides the type in the field
                if (!empty($defs['full_text_search']['type'])) {
                    $defs['type'] = $defs['full_text_search']['type'];
                }
                $ftsFields[$field] = $defs;
            }
        }
        return $ftsFields;
    }

    /**
     * Return list of modules which are available for a given user.
     *
     * TODO: Today users can alter the modules they search against in user
     * preferences. Not sure what the use case is, however this functionality
     * should move into the Provider classes itself as there is more than just
     * global search.
     */
    public function getAvailableModulesForUser(\User $user)
    {
        $list = array();
        foreach ($this->getAllEnabledModules() as $module) {
            $seed = \BeanFactory::getBean($module);
            if ($seed->ACLAccess('ListView', array('user' => $user))) {
                $list[] = $module;
            }
        }
        return $list;
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
     * Verify if a module is available for givem user
     * @param string $module
     * @param \User $user
     * @return boolean
     */
    public function isModuleAvailableForUser($module, \User $user)
    {
        return in_array($module, $this->getAvailableModulesForUser($user));
    }

    /**
     * Filter module list for given user
     * @param array $modules
     * @param \User $user
     * @return array
     */
    public function filterModulesAvailableForUser(array $modules, \User $user)
    {
        $filtered = array();
        foreach ($modules as $module) {
            if ($this->isModuleAvailableForUser($module, $user)) {
                $filtered[] = $module;
            }
        }
        return $filtered;
    }
}
