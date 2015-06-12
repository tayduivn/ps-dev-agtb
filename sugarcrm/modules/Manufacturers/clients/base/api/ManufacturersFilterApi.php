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

require_once 'clients/base/api/FilterApi.php';

class ManufacturersFilterApi extends FilterApi
{
    public function registerApiRest()
    {
        $api = parent::registerApiRest();

        $api['filterModuleAll'] = array(
            'reqType' => 'GET',
            'path' => array('Manufacturers'),
            'pathVars' => array('module'),
            'method' => 'filterList',
            'jsonParams' => array('filter'),
            'shortHelp' => 'List of all records in this module',
            'longHelp' => 'include/api/help/module_filter_get_help.html',
            'exceptions' => array(
                // Thrown in filterList
                'SugarApiExceptionInvalidParameter',
                // Thrown in filterListSetup and parseArguments
                'SugarApiExceptionNotAuthorized',
            ),
        );

        return $api;
    }

    /**
     * {@inheritDoc}
     * Add filter to return only active revisions for filterApi.
     */
    public function filterListSetup(ServiceBase $api, array $args, $acl = 'list')
    {
        list($args, $q, $options, $seed) = parent::filterListSetup($api, $args, $acl);

        $q->where()->equals('status', 'Active');

        return array($args, $q, $options, $seed);
    }
}
