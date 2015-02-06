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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch;

/**
 *
 * SearchFields handler
 *
 */
class SearchFields
{
    const FIELD_SEP = '.';

    /**
     * @var GlobalSearch
     */
    protected $provider;

    /**
     * @var boolean Apply boosts
     */
    protected $boost = false;

    /**
     * @var BoostHandler
     */
    protected $boostHandler;

    /**
     * Ctor
     * @param GlobalSearch $provider
     * @param BoostHandler $boostHandler
     */
    public function __construct(GlobalSearch $provider, BoostHandler $boostHandler)
    {
        $this->provider = $provider;
        $this->boostHandler = $boostHandler;
    }

    /**
     * Set boost flag
     * @param boolean $toggle
     */
    public function setBoost($toggle)
    {
        $this->boost = (bool) $toggle;
    }

    /**
     * Get list of all search fields
     * @return array
     */
    public function getSearchFields(array $modules)
    {
        $list = array();
        foreach ($modules as $module) {
            $list = array_merge($list, $this->getModuleSearchFields($module));
        }
        return $list;
    }

    /**
     * Get search fields for given module
     * @param string $module
     * @return array
     */
    public function getModuleSearchFields($module)
    {
        $list = array();
        foreach ($this->getFtsFields($module) as $field => $defs) {

            if (!$this->isFieldSearchable($defs)) {
                continue;
            }

            $list = array_merge($list, $this->getMultiFieldSearchFields($module, $field, $defs));
        }
        return $list;
    }

    /**
     * Get search fields for given sugar field
     * @param string $module
     * @param string $field
     * @param array $defs
     * @return array
     */
    public function getMultiFieldSearchFields($module, $field, array $defs)
    {
        $list = array();

        // Get list of mapping definitions
        $mappingDefs = $this->provider->getMappingDefsForSugarType($defs['type']);

        foreach ($mappingDefs as $type) {

            $searchField = $module . self::FIELD_SEP . $field . self::FIELD_SEP .  $type;

            if ($this->boost) {
                $searchField = $this->boostHandler->getBoostedField($searchField, $defs, $type);
            }

            $list[] = $searchField;
        }

        return $list;
    }

    /**
     * Check if a field is searchable or not.
     * @param array $defs Field vardefs
     * @return boolean
     */
    public function isFieldSearchable(array $defs)
    {
        $isSearchable = false;

        // Decide to include the field in the query or not, given the conditions:
        // 1. searchable is not null and is set to true;
        // 2. searchable is null and boost is not null;
        if (isset($defs['full_text_search']['searchable'])) {
            if ($defs['full_text_search']['searchable'] == true) {
                $isSearchable = true;
            }
        } else {
            if (!empty($defs['full_text_search']['boost'])) {
                $isSearchable = true;
            }
        }
        return $isSearchable;
    }

    /**
     * Get FTS fields wrapper
     * @param string $module
     * @return array
     */
    protected function getFtsFields($module)
    {
        return $this->provider->getContainer()->metaDataHelper->getFtsFields($module);
    }
}
