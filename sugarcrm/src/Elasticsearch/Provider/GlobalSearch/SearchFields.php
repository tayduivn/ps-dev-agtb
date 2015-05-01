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
     * Module name prefix separator
     */
    const PREFIX_SEP = '__';

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
     * Add search field to the stack
     * @param string $module Module name
     * @param array $path Field path
     * @param array $defs Field definitions
     * @param string $weightId Identifier to apply weighted boost
     */
    public function addSearchField($module, array $path, array $defs, $weightId)
    {
        //Check ACL access
        if (is_array($path) && !empty($path)) {
            $names = explode(self::PREFIX_SEP, $path[0]);
            if (sizeof($names) === 2) {
                $field = $names[1];
                $accessLevel = \SugarACL::getFieldAccess($module, $field);
                if ($accessLevel === \SugarACL::ACL_NO_ACCESS) {
                    return;
                }
            }
        }

        $searchField = implode(self::FIELD_SEP, $path);
        if ($this->booster) {
            $searchField = $this->booster->getBoostedField($searchField, $defs, $weightId);
        }
        $this->searchFields[] = $searchField;
    }
}
