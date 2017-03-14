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

class ProductBundleHooks
{
    /**
     * set the quote_id on associated qlis and save them
     * @param ProductBundle $bean
     * @param $event
     * @param $args
     */
    public function setQLIQuoteLink(ProductBundle $bean, $event, $args)
    {
        $quotes = $bean->get_linked_beans('quotes', 'Quotes');
        $products = $bean->get_linked_beans('products', 'Products');

        foreach ($quotes as $quote) {
            foreach ($products as $qli) {
                $qli->quote_id = $quote->id;
                $qli->save();
            }
        }
    }
}
