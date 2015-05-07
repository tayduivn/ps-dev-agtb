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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * MultiMatch query builder
 *
 */
class MultiMatchQuery
{

    /**
     * Create a multi-match query.
     * @param string $term Search term
     * @param array $modules List of modules
     * @param object $provider the global search provider
     * @return \Elastica\Query\MultiMatchs
     */
    public function create($term, array $modules, $provider)
    {
        $query = new \Elastica\Query\MultiMatch();
        $query->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS);
        $query->setQuery($term);
        $query->setFields($provider->getSearchFields($modules));
        $query->setTieBreaker(1.0); // TODO make configurable
        return $query;
    }

    /**
     * Check if a field is accessible
     * @param string $module Module name
     * @param array $path Field path
     * @return bool
     */
    public static function isFieldAccessible($module, array $path)
    {
        //Check ACL access
        if (is_array($path) && !empty($path)) {
            $names = explode(SearchFields::PREFIX_SEP, $path[0]);
            if (sizeof($names) === 2) {
                $field = $names[1];
                $accessLevel = \SugarACL::getFieldAccess($module, $field);
                if ($accessLevel === \SugarACL::ACL_NO_ACCESS) {
                    return false;
                }
            }
        }
        return true;
    }
}
