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

/**
 * Used for Storing Default LogicHooks for the ProductBundles module
 *
 * Look in modules/ProductBundles/Ext/LogicHooks/* for the definitions for when the specific methods will be called
 *
 * Class ProductBundleHooks
 */
class ProductBundleHooks
{
    /**
     * Handle when the product relationship is added or deleted, this makes sure that the related quote is always
     * updates to have the correct totals
     *
     * @param SugarBean|ProductBundle $bean The current ProductBundle
     * @param string $event What event is being used, currently only `after_relationship_add`
     *     and `after_relationship_delete` is supported
     * @param array $args An array of arguments passed in when the hook is called
     * @return bool
     */
    public function afterProductRelationship(SugarBean $bean, $event, $args)
    {
        $link = (isset($args['link']) && !empty($args['link'])) ? $args['link'] : false;
        if ($link === 'products' && $event == 'after_relationship_delete' || $event == 'after_relationship_add') {
            // save the product bundle here to update the calculated values
            $bean->save();
            // make sure the quote is saved as well
            $bean->load_relationship('quotes');
            $quotes = $bean->quotes->getBeans();
            foreach ($quotes as $quote) {
                // make sure that the products_bundles variable is unset, this is just in case the product_bundles
                // link was loaded on a cached bean
                unset($quote->product_bundles);
                SugarRelationship::addToResaveList($quote);
            }

            return true;
        }
        return false;
    }
}
