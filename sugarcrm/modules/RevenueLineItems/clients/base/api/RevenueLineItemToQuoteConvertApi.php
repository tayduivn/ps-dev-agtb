<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once('include/api/SugarApi.php');
class RevenueLineItemToQuoteConvertApi extends SugarApi
{
    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'convert' => array(
                'reqType' => 'POST',
                'path' => array('RevenueLineItems', '?', 'quote'),
                'pathVars' => array('module', 'record', 'action'),
                'method' => 'convertToQuote',
                'shortHelp' => 'Convert a Revenue Line Item Into A Quote Record',
                'longHelp' => 'modules/RevenueLineItems/clients/base/api/help/convert_to_quote.html',
            ),
        );
    }

    /**
     * Converts RLI to a quote
     * 
     * @param ServiceBase api
     * @param array args
     * 
     * @returns array Quote ID and name of new quote
     */
    public function convertToQuote(ServiceBase $api, array $args)
    {
        // load up the Product
        /* @var $rli RevenueLineItem */
        $rli = BeanFactory::getBean('RevenueLineItems', $args['record']);

        if (empty($rli->id)) {
            // throw a 404 (Not Found) if the rli is not found
            throw new SugarApiExceptionNotFound();
        }

        /* @var $product Product */
        $product = $rli->convertToQuotedLineItem();
        $product->save();

        // lets create a new bundle
        /* @var $product_bundle ProductBundle */
        $product_bundle = BeanFactory::getBean('ProductBundles');

        $total = SugarMath::init()->exp("?*?", array($product->quantity, $product->likely_case))->result();
        $total_base = SugarCurrency::convertWithRate($total, $product->base_rate);

        $product_bundle->bundle_stage = 'Draft';
        $product_bundle->total = $total;
        $product_bundle->total_usdollar = $total_base;
        $product_bundle->subtotal = $total;
        $product_bundle->subtotal_usdollar = $total_base;
        $product_bundle->deal_tot = $total;
        $product_bundle->deal_tot_usdollar = $total_base;
        $product_bundle->new_sub = $total;
        $product_bundle->new_sub_usdollar = $total_base;
        $product_bundle->tax = 0.00;
        $product_bundle->tax_usdollar = 0.00;
        $product_bundle->currency_id = $product->currency_id;
        $product_bundle->save();
        $product_bundle->load_relationship('products');
        $product_bundle->products->add($product, array('product_index' => 1));

        // now that we have the product bundle, lets create the quote
        /* @var $quote Quote */
        $quote = BeanFactory::getBean('Quotes');

        $quote->total = $total;
        $quote->total_usdollar = $total_base;
        $quote->subtotal = $total;
        $quote->subtotal_usdollar = $total_base;
        $quote->deal_tot = $total;
        $quote->deal_tot_usdollar = $total_base;
        $quote->new_sub = $total;
        $quote->new_sub_usdollar = $total_base;
        $quote->tax = 0.00;
        $quote->tax_usdollar = 0.00;
        $quote->currency_id = $product->currency_id;
        $quote->opportunity_id = $product->opportunity_id;
        $quote->quote_stage = "Draft";
        $quote->assigned_user_id = $GLOBALS['current_user']->id;

        // get the account from the product
        /* @var $account Account */
        $account = BeanFactory::getBean('Accounts', $product->account_id);
        if (isset($account->id)) {
            $quote->billing_address_street = $account->billing_address_street;
            $quote->billing_address_city = $account->billing_address_city;
            $quote->billing_address_country = $account->billing_address_country;
            $quote->billing_address_state = $account->billing_address_state;
            $quote->billing_address_postalcode = $account->billing_address_postalcode;

            $quote->shipping_address_street = $account->shipping_address_street;
            $quote->shipping_address_city = $account->shipping_address_city;
            $quote->shipping_address_country = $account->shipping_address_country;
            $quote->shipping_address_state = $account->shipping_address_state;
            $quote->shipping_address_postalcode = $account->shipping_address_postalcode;
        }


        $quote->save();

        $quote->set_relationship(
            'quotes_accounts',
            array('quote_id' => $quote->id, 'account_id' => $product->account_id, 'account_role' => 'Bill To'),
            false
        );

        $quote->set_relationship(
            'quotes_accounts',
            array('quote_id' => $quote->id, 'account_id' => $product->account_id, 'account_role' => 'Ship To'),
            false
        );

        $quote->set_relationship(
            'product_bundle_quote',
            array('quote_id' => $quote->id, 'bundle_id' => $product_bundle->id, 'bundle_index' => 0)
        );

        # Set the quote_id on the product so we know it's linked
        $product->quote_id = $quote->id;
        $rli->quote_id = $quote->id;
        $product->status = Product::STATUS_QUOTED;
        $rli->status = RevenueLineItem::STATUS_QUOTED;
        $product->save();
        $rli->save();

        return array('id' => $quote->id, 'name' => $quote->name);

    }
}
