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


require_once('data/SugarBeanApiHelper.php');

class QuotesApiHelper extends SugarBeanApiHelper
{
    /**
     * Formats the bean so it is ready to be handed back to the API's client. Certain fields will get extra processing
     * to make them easier to work with from the client end.
     *
     * @param $quote SugarBean|Quote The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $quote, array $fieldList = array(), array $options = array())
    {
        // call the legacy method here to load all the data that we need
        $quote->fill_in_additional_detail_fields();

        return parent::formatForApi($quote, $fieldList, $options);
    }

    /**
     * This function sets up shipping and billing address for new Quote.
     *
     * @param SugarBean|Quote $quote The current SugarBean that is being worked with
     * @param array $submittedData The data from the request
     * @param array $options Any Options that may have been passed in.
     * @return array|boolean An array of validation errors if any occurred, otherwise `true`.
     */
    public function populateFromApi(SugarBean $quote, array $submittedData, array $options = array())
    {
        parent::populateFromApi($quote, $submittedData, $options);

        // valid relate modules
        $valid_relate_modules = array('Contacts', 'Accounts');
        // Bug #57888 : REST API: Create related quote must populate billing/shipping contact and account
        if (isset($submittedData['module']) &&
            in_array($submittedData['module'], $valid_relate_modules) &&
            isset($submittedData['record'])
        ) {
            $this->setAddressFromBean($submittedData['module'], $submittedData['record'], $quote);
        } else {
            // we are not on a related record, so lets check the field and fill in the data correctly
            $hasBillingAccountId = (isset($quote->billing_account_id) && !empty($quote->billing_account_id));
            $hasShippingAccountId = (isset($quote->shipping_account_id) && !empty($quote->shipping_account_id));
            $hasBillingContactId = (isset($quote->billing_contact_id) && !empty($quote->billing_contact_id));
            $hasShippingContactId = (isset($quote->shipping_contact_id) && !empty($quote->shipping_contact_id));

            if ($hasBillingAccountId) {
                $account = BeanFactory::getBean('Accounts', $quote->billing_account_id);
                $this->processBeanAddressFields($account, $quote, 'billing', 'billing', 'shipping');
            } elseif (!$hasBillingAccountId && $hasBillingContactId) {
                $contact = BeanFactory::getBean('Contacts', $quote->billing_contact_id);
                $this->processBeanAddressFields($contact, $quote, 'shipping', 'primary', 'alt');
            }

            if (!$hasShippingAccountId && !$hasShippingContactId && $hasBillingAccountId) {
                // we don't have a id set for the shipping account or contact, pull the account from the billing
                $quote->shipping_account_id = $quote->billing_account_id;
                $hasShippingAccountId = true;
            }

            if ($hasShippingAccountId && !$hasShippingContactId) {
                $account = BeanFactory::getBean('Accounts', $quote->shipping_account_id);
                $this->processBeanAddressFields($account, $quote, 'shipping', 'shipping', 'billing');
            } elseif ($hasShippingContactId) {
                $contact = BeanFactory::getBean('Contacts', $quote->shipping_contact_id);
                $this->processBeanAddressFields($contact, $quote, 'shipping', 'primary', 'alt');
            }
        }

        // lets process the bundles
        if (isset($submittedData['bundles']) && is_array($submittedData['bundles'])) {
            foreach ($submittedData['bundles'] as $bundle) {
                $this->processBundle($bundle, $quote);
            }
        }

        return true;
    }

    /**
     * @param $bundle
     * @param SugarBean|Quote $quote
     */
    protected function processBundle($bundle, SugarBean $quote)
    {
        if (!isset($bundle['id'])) {
            $bundle['id'] = null;
        }
        // lets try and get the bundle
        /* @var $pb ProductBundle */
        $pb = BeanFactory::getBean('ProductBundles', $bundle['id']);

        if (isset($bundle['deleted']) && $bundle['deleted'] == 1) {
            $pb->mark_deleted($pb->id);
        } else {
            $pb->team_id = $quote->team_id;
            $pb->team_set_id = $quote->team_set_id;
            $pb->shipping = $bundle['shipping'];
            $pb->currency_id = $quote->currency_id;
            $pb->taxrate_id = $quote->taxrate_id;
            $pb->bundle_stage = $bundle['bundle_stage'];
            $pb->name = $bundle['name'];

            # we gotta save this first as the notes/products look for data from here below.
            $pb->save();

            // handle the items on the product bundle
            foreach ($bundle['items'] as $item) {
                if ($item['module'] == 'ProductBundleNotes') {
                    $this->handleBundleNoteSave($item, $pb, $quote);
                } elseif ($item['module'] == 'Products') {
                    $this->handleBundleProductSave($item, $pb, $quote);
                }
            }

            // save the bundle to the quote
            $quote->load_relationship('product_bundles');
            if (!isset($bundle['position'])) {
                $bundle['position'] = isset($bundle['bundle_index']) ?
                    $bundle['bundle_index'] : count($quote->product_bundles->getBeans());
            }
            $quote->product_bundles->add($pb, array('bundle_index' => $bundle['position']));
        }
    }

    /**
     * @param array $product
     * @param SugarBean|ProductBundle $pb
     * @param SugarBean|Quote $quote
     */
    protected function handleBundleProductSave(array $product, SugarBean $pb, SugarBean $quote)
    {
        if (!isset($product['id'])) {
            $product['id'] = null;
        }
        /* @var $product_bean Product */
        $product_bean = BeanFactory::getBean('Products', $product['id']);

        foreach ($product_bean->column_fields as $field) {
            if (isset($product[$field])) {
                $value = $product[$field];
                if (isset($product_bean->field_defs[$field]['type'])) {
                    // figure out the type that we need
                    $def = $product_bean->field_defs[$field];
                    // get the correct type in the following order
                    //  custom_type -> dbType -> type
                    // from the vardefs
                    $type = !empty($def['custom_type']) ? $def['custom_type'] :
                        !empty($def['dbType']) ? $def['dbType'] : $def['type'];

                    /* @var $sugarField SugarFieldBase */
                    $sugarField = SugarFieldHandler::getSugarField($type);
                    $sugarField->save(
                        $product_bean,
                        array($field => $value),
                        $field,
                        $product_bean->field_defs[$field]
                    );
                } else {
                    $product->$field = $value;
                }
            }
        }

        $product_bean->currency_id = $quote->currency_id;
        $product_bean->base_rate = $quote->base_rate;
        $product_bean->team_id = $quote->team_id;
        $product_bean->team_set_id = $quote->team_set_id;
        $product_bean->quote_id = $quote->id;
        $product_bean->account_id = $quote->billing_account_id;
        $product_bean->contact_id = $quote->billing_contact_id;
        $product_bean->ignoreQuoteSave = true;

        $pb->load_relationship('products');
        if (isset($product['deleted']) && $product['deleted'] === 1) {
            $product_bean->mark_deleted($product_bean->id);
        } else {
            $product_bean->save();
            if (!isset($product['position'])) {
                $product['position'] = isset($product['product_index']) ?
                    $product['product_index'] : count($pb->getLineItems());
            }
            $pb->products->add($product_bean, array('product_index' => $product['position']));
        }
    }


    protected function handleBundleNoteSave(array $note, SugarBean $pb, SugarBean $quote)
    {
        if (!isset($note['id'])) {
            $note['id'] = null;
        }
        /* @var $product_bundle_note ProductBundleNote */
        $product_bundle_note = BeanFactory::getBean('ProductBundleNotes', $note['id']);
        $product_bundle_note->deleted = $note['deleted'];
        $product_bundle_note->description = $note['description'];


        $pb->load_relationship('product_bundle_notes');
        if (isset($note['deleted']) && $note['deleted'] === 1) {
            $product_bundle_note->mark_deleted($product_bundle_note->id);
        } else {
            $product_bundle_note->save();
            if (!isset($note['position'])) {
                $note['position'] = isset($product['note_index']) ?
                    $product['note_index'] : count($pb->getLineItems());
            }
            $pb->product_bundle_notes->add($product_bundle_note, array('note_index' => $note['position']));
        }
    }

    /**
     * Handle Setting the Addresses
     *
     * @param String $fromModule
     * @param String $fromId
     * @param SugarBean|Quote $quote
     */
    protected function setAddressFromBean($fromModule, $fromId, SugarBean $quote)
    {
        $fromBean = BeanFactory::getBean($fromModule, $fromId);
        if ($fromModule == 'Contacts') {
            $quote->shipping_contact_id = $fromId;
            $quote->billing_contact_id = $fromId;
            $typeKey = 'primary';
            $altTypeKey = 'alt';
        } elseif ($fromModule == 'Accounts') {
            $quote->billing_account_id = $fromId;
            $quote->shipping_account_id = $fromId;
            $typeKey = 'shipping';
            $altTypeKey = 'billing';
        }

        // set the shipping address first
        $this->processBeanAddressFields($fromBean, $quote, 'shipping', $typeKey, $altTypeKey);

        // change the type key for the billing address, when we are pulling from Accounts
        if ($fromModule == 'Accounts') {
            $typeKey = 'billing';
            $altTypeKey = 'shipping';
        }

        // if the initial bean has an account set on it, we need to to set the billing address
        // to the account address fields vs the contact address fields.
        // if there is no account_id then it will just set the billing address fields from the contact
        if (!empty($fromBean->account_id)) {
            $quote->billing_account_id = $fromBean->account_id;
            $quote->shipping_account_id = $fromBean->account_id;

            unset($fromBean);

            $fromBean = BeanFactory::getBean('Accounts', $quote->shipping_account_id);
            $typeKey = 'billing';
            $altTypeKey = 'shipping';
        }

        // set the billing address
        $this->processBeanAddressFields($fromBean, $quote, 'billing', $typeKey, $altTypeKey);

    }

    /**
     * Utility Method to set the fields on a given $quote from another bean.
     *
     * @param SugarBean $fromBean
     * @param SugarBean|Quote $quote
     * @param string $type What field type are we setting on the $quote
     * @param string $primaryField The primary field on the $fromBean
     * @param string $altField The secondary field on the $fromBean
     */
    protected function processBeanAddressFields($fromBean, $quote, $type, $primaryField, $altField)
    {
        $fields = array('street', 'city', 'state', 'postalcode', 'country');
        foreach ($fields as $field) {
            $quoteField = $type . "_address_" . $field;
            $quote->$quoteField = $this->getAddressFormContact(
                $quote->$quoteField,
                $fromBean,
                $primaryField . "_address_" . $field,
                $altField . "_address_" . $field
            );
        }
    }

    /**
     * Utility method to pick which string to return, if $quote_value is not empty, just return it,
     * otherwise check $property and then $alt_property for a value, if they are both empty, this will
     * just return an empty string
     *
     * @param string $quote_value The current value on the quote
     * @param SugarBean $fromBean The SugarBean we are looking at for a value
     * @param string $primaryField The first field to check
     * @param string $altField The second field to check
     * @return string
     */
    protected function getAddressFormContact($quote_value, $fromBean, $primaryField, $altField)
    {
        return !empty($quote_value) ? $quote_value
            : (isset($fromBean->$primaryField) ? $fromBean->$primaryField
                : (isset($fromBean->$altField) ? $fromBean->$altField
                    : ''));
    }
}
