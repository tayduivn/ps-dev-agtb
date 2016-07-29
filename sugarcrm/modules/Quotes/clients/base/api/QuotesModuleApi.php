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

require_once 'clients/base/api/ModuleApi.php';
require_once 'modules/Quotes/clients/base/api/QuotesRelateApi.php';
class QuotesModuleApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'retrieveQuote' => array(
                'reqType' => 'GET',
                'path' => array('Quotes', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'retrieveQuoteRecord',
                'shortHelp' => 'Returns a single record',
                'longHelp' => 'include/api/help/module_record_get_help.html',
            ),
        );
    }


    /**
     * Retrieves the Quote ModuleApi record data and includes any related ProductBundles
     * to the returned Quote data
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array The Quote record data with related ProductBundles
     */
    public function retrieveQuoteRecord(ServiceBase $api, array $args)
    {
        // Get the Quotes ModuleApi data for the Quote record
        $data = parent::retrieveRecord($api, $args);

        // fixme: SFA-4399 will be changing the use of QuotesRelateApi below to use the new relationship in SFA-4394
        // Get the related ProductBundles data
        $args['link_name'] = 'product_bundles';
        $relateApi = new QuotesRelateApi();
        $data['quote_data'] = $relateApi->filterRelated($api, $args);

        return $data;
    }
}
