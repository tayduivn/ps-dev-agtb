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

// fixme: SFA-4399 will be removing this if no longer needed since we'll be using the new relationship from SFA-4394
require_once 'clients/base/api/RelateApi.php';
class QuotesRelateApi extends RelateApi
{
    public function registerApiRest()
    {
        return array(
            'listQuotesRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('Quotes', '?', 'link', 'product_bundles'),
                'pathVars' => array('module', 'record', '', 'link_name'),
                'jsonParams' => array('filter'),
                'method' => 'filterRelated',
                'shortHelp' => 'Lists related records.',
                'longHelp' => 'include/api/help/module_record_link_link_name_filter_get_help.html',
            ),
        );
    }

    /**
     * Gets the related ProductBundles associated with the Quote, and returns the items associated with the
     * ProductBundles inside `bundle_items` on the ProductBundle
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function filterRelated(ServiceBase $api, array $args)
    {
        $restResp = parent::filterRelated($api, $args);
        foreach($restResp['records'] as $key => $bundle) {
            $productBundleBean = BeanFactory::getBean('ProductBundles', $bundle['id']);
            $local_args = array_diff_key($args, array('view' => '', 'fields' => ''));
            $productBundle = $this->formatBean($api, $local_args, $productBundleBean);
            $records = $this->formatBeans($api, $local_args, $productBundleBean->getLineItems());
            $productBundle['related_records'] = $records;
            $restResp['records'][$key] = $productBundle;
        }
        $restResp['records'] = array_reverse($restResp['records']);
        return $restResp;
    }
}
