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

require_once 'data/SugarBeanApiHelper.php';

class ProductBundlesApiHelper extends SugarBeanApiHelper
{
    /**
     * This function sets up shipping and billing address for new Quote.
     *
     * @param SugarBean|ProductBundle $product_bundle The current SugarBean that is being worked with
     * @param array $submittedData The data from the request
     * @param array $options Any Options that may have been passed in.
     * @throws SugarApiException
     * @return array|boolean An array of validation errors if any occurred, otherwise `true`.
     */
    public function populateFromApi(SugarBean $product_bundle, array $submittedData, array $options = array())
    {
        // we don't have a quote_id, throw an invalid parameter error
        if (!isset($submittedData['quote_id']) || empty($submittedData['quote_id'])) {
            throw new SugarApiExceptionInvalidParameter(
                'EXCEPTION_INVALID_PARAMETER',
                null,
                $product_bundle->module_name
            );
        }

        $quote = $this->getQuoteBean($submittedData['quote_id']);

        parent::populateFromApi($product_bundle, $submittedData, $options);

        $this->fillInFromQuote($product_bundle, $quote);

        if (isset($submittedData['items']) && is_array($submittedData['items']) && !empty($submittedData['items'])) {
            $this->processBundleItems($submittedData['items'], $product_bundle, $quote);
        }

        // try and figure out the position, if `bundle_index` and `position` are not defined, pass in null
        // as null put it at the end of all the product bundles
        $position = null;
        if (isset($submittedData['bundle_index'])) {
            $position = $submittedData['bundle_index'];
        } elseif (isset($submittedData['position'])) {
            $position = $submittedData['position'];
        }
        $this->linkBundleToQuote($product_bundle, $quote, $position);

        return true;
    }

    /**
     * @param string $quote_id The GUID of the quote to load
     * @throws SugarApiExceptionNotAuthorized
     * @throws SugarApiExceptionNotFound
     * @return SugarBean|Quote
     */
    protected function getQuoteBean($quote_id)
    {
        // we have a quote, lets load it up to verify that we can see the quote
        $quote = BeanFactory::getBean('Quotes', $quote_id);

        // handle when a quote is not found
        if ($quote->id !== $quote_id) {
            throw new SugarApiExceptionNotFound('EXCEPTION_NOT_FOUND_QUOTE', null, 'ProductBundles');
        }

        if (!$quote->ACLAccess('save')) {
            // No create access so we construct an error message and throw the exception
            $failed_module_strings = return_module_language($GLOBALS['current_language'], $quote->module_dir);
            $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            $args = null;
            if (!empty($moduleName)) {
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_CREATE_MODULE_NOT_AUTHORIZED', $args);
        }

        return $quote;
    }

    /**
     * Fill in any data from the Quote Bean and saves the Product Bundle
     *
     * @param SugarBean|ProductBundle $product_bundle
     * @param SugarBean|Quote $quote
     */
    protected function fillInFromQuote(SugarBean $product_bundle, SugarBean $quote)
    {
        $product_bundle->team_id = $quote->team_id;
        $product_bundle->team_set_id = $quote->team_set_id;
        $product_bundle->currency_id = $quote->currency_id;
        $product_bundle->base_rate = $quote->base_rate;
        $product_bundle->taxrate_id = $quote->taxrate_id;

        // save the product bundle, as it needs to have an id to do any of the other items
        $product_bundle->save();
    }

    /**
     * @param array $items
     * @param SugarBean|ProductBundle $product_bundle
     * @param SugarBean|Quote $quote
     */
    protected function processBundleItems(array $items, SugarBean $product_bundle, SugarBean $quote)
    {
        /* @var $quote_api_helper QuotesApiHelper */
        $quote_api_helper = ApiHelper::getHelper($this->api, $quote);

        // handle the items on the product bundle
        foreach ($items as $item) {
            if ($item['module'] == 'ProductBundleNotes') {
                $quote_api_helper->handleBundleNoteSave($item, $product_bundle, $quote);
            } elseif ($item['module'] == 'Products') {
                $quote_api_helper->handleBundleProductSave($item, $product_bundle, $quote);
            }
        }
    }

    protected function linkBundleToQuote(SugarBean $product_bundle, SugarBean $quote, $position = null)
    {
        $quote->load_relationship('product_bundles');
        if (is_null($position)) {
            // use get(), since it's faster than getBeans() as we just need the count
            $position = count($quote->product_bundles->get());
        }
        $quote->product_bundles->add($product_bundle, array('bundle_index' => $position));

        // now that we have added the product_bundle, unset the link and call save on the quotes
        // this will cause the re-calculated to work correctly on the quotes
        unset($quote->product_bundles);
        $quote->save();
    }
}
