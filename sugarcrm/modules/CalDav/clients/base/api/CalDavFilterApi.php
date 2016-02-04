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
require_once 'clients/base/api/FilterApi.php';
/**
 * Class CalDavFilterApi
 */
class CalDavFilterApi extends FilterApi
{
    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'filterModuleAll' => array(
                'reqType' => 'GET',
                'path' => array('CalDav'),
                'pathVars' => array('module'),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'List of all records in this module. CalDav does not have any records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
        );
    }
    /**
     * CalDav does not have any records to return.
     * {@inheritdoc}
     */
    public function filterList(ServiceBase $api, array $args, $acl = 'list')
    {
        return array();
    }
}
