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

class QuoteHooks
{
    /**
     * Hook to resave the quote product bundles so that the hook to set the quote->qli relationship works
     * @param Quote $bean
     * @param $event
     * @param $args
     */
    public function setQLIQuoteLink(Quote $bean, $event, $args)
    {
        if (!$args['isUpdate']) {
            $productBundles = $bean->get_linked_beans('product_bundles', 'ProductBundles');

            foreach ($productBundles as $bundle) {
                $bundle->save();
            }
        }
    }
}
