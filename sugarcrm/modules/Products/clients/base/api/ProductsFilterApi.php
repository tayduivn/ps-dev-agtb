<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'clients/base/api/FilterApi.php';

/**
 * Class ProductsFilterApi
 *
 * Products Module Specific Filter API
 */
class ProductsFilterApi extends FilterApi
{
    /**
     * Register the API End Points for Rest
     *
     * @return array
     */
    public function registerApiRest()
    {
        return array(
            'filterModuleGet' => array(
                'reqType' => 'GET',
                'path' => array('Products', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
            'filterModuleAll' => array(
                'reqType' => 'GET',
                'path' => array('Products'),
                'pathVars' => array('module'),
                'method' => 'filterList',
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
            'filterModulePost' => array(
                'reqType' => 'POST',
                'path' => array('Products', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
            ),
        );
    }

    /**
     * Override the default filterList method to add in the logic that is required for Products to only show items
     * with associated Opportunities in the List Views
     *
     * @param RestService $api
     * @param array $args
     * @return array
     */
    public function filterList(RestService $api, array $args)
    {
        // adjust the filter by the rules set forth by PM/PO's
        //BEGIN SUGARCRM flav=ent ONLY
        $oppFilter = array(
            array('opportunity_id' => array('$not_null' => '')),
            array('opportunity_id' => array('$not_equals' => '')),
        );
        $oppFilterType = '$and';
        //END SUGARCRM flav=ent ONLY

        //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
        $oppFilter = array(
            array('opportunity_id' => array('$is_null' => '')),
            array('opportunity_id' => array('$equals' => '')),
        );
        $oppFilterType = '$or';
        //END SUGARCRM  flav=pro && flav!=ent ONLY

        if (!isset($args['filter']) || empty($args['filter'])) {
            //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
            $oppFilter = array(array('$or' => $oppFilter));
            //END SUGARCRM flav=pro && flav!=ent ONLY
            $args['filter'] = $oppFilter;
        } else {
            array_push($args['filter'], array($oppFilterType => $oppFilter));
        }

        return parent::filterList($api, $args);
    }
}
