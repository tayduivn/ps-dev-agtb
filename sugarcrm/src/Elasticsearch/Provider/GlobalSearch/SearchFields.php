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
 * SearchFields builder
 *
 */
class SearchFields
{
    /**
     * Field separator
     */
    const FIELD_SEP = '.';

    /**
     * @var Booster
     */
    protected $booster;

    /**
     * List of search fields
     * @var array
     */
    protected $searchFields = array();

    /**
     * Ctor
     * @param Booster $booster
     */
    public function __construct(Booster $booster = null)
    {
        $this->booster = $booster;
    }

    /**
     * Return search fields
     * @return array
     */
    public function getSearchFields()
    {
        return $this->searchFields;
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
        // 1. searchable is is set to true
        // 2. searchable is empty and boost is set (*)
        //
        // This will be deprecated after 7.7 as this was the old behavior.

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
     * Add search field to the stack
     * @param string $module Module name
     * @param array $path Field path
     * @param array $defs Field definitions
     * @param string $weightId Identifier to apply weighted boost
     */
    public function addSearchField($module, array $path, array $defs, $weightId)
    {
        $searchField = $module . self::FIELD_SEP . implode(self::FIELD_SEP, $path);
        if ($this->booster) {
            $searchField = $this->booster->getBoostedField($searchField, $defs, $weightId);
        }
        $this->searchFields[] = $searchField;
    }
}
