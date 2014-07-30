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
     * @param $bean SugarBean|Quote The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        // call the legacy method here to load all the data that we need
        $bean->fill_in_additional_detail_fields();

        return parent::formatForApi($bean, $fieldList, $options);
    }

    /**
     * This function sets up shipping and billing address for new Quote.
     *
     * @param SugarBean|Quote $bean
     * @param array $submittedData
     * @param array $options
     * @return array
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        parent::populateFromApi($bean, $submittedData, $options);

        // valid relate modules
        $valid_relate_modules = array('Contacts', 'Accounts');
        // Bug #57888 : REST API: Create related quote must populate billing/shipping contact and account
        if (isset($submittedData['module']) &&
            in_array($submittedData['module'], $valid_relate_modules) &&
            isset($submittedData['record'])
        ) {
            $this->setAddressFromBean($submittedData['module'], $submittedData['record'], $bean);
        } else {
            // we are not on a related record, so lets check the field and fill in the data correctly
            $hasBillingAccountId = (isset($bean->billing_account_id) && !empty($bean->billing_account_id));
            $hasShippingAccountId = (isset($bean->shipping_account_id) && !empty($bean->shipping_account_id));
            $hasBillingContactId = (isset($bean->billing_contact_id) && !empty($bean->billing_contact_id));
            $hasShippingContactId = (isset($bean->shipping_contact_id) && !empty($bean->shipping_contact_id));

            if ($hasBillingAccountId) {
                $account = BeanFactory::getBean('Accounts', $bean->billing_account_id);
                $this->processBeanAddressFields($account, $bean, 'billing', 'billing', 'shipping');
            } else {
                if (!$hasBillingAccountId && $hasBillingContactId) {
                    $contact = BeanFactory::getBean('Contacts', $bean->billing_contact_id);
                    $this->processBeanAddressFields($contact, $bean, 'shipping', 'primary', 'alt');
                }
            }

            if (!$hasShippingAccountId && !$hasShippingContactId && $hasBillingAccountId) {
                // we don't have a id set for the shipping account or contact, pull the account from the billing
                $bean->shipping_account_id = $bean->billing_account_id;
                $hasShippingAccountId = true;
            }

            if ($hasShippingAccountId && !$hasShippingContactId) {
                $account = BeanFactory::getBean('Accounts', $bean->shipping_account_id);
                $this->processBeanAddressFields($account, $bean, 'shipping', 'shipping', 'billing');
            } else {
                if ($hasShippingContactId) {
                    $contact = BeanFactory::getBean('Contacts', $bean->shipping_contact_id);
                    $this->processBeanAddressFields($contact, $bean, 'shipping', 'primary', 'alt');
                }
            }
        }

        // lets process the bundles
        if (isset($submittedData['bundles']) && is_array($submittedData['bundles'])) {
            foreach ($submittedData['bundles'] as $bundle) {
                $this->processBundle($bundle, $bean);
            }
        }

        return true;
    }

    /**
     * @param $bundle
     * @param SugarBean|Quote $bean
     */
    protected function processBundle($bundle, SugarBean $bean)
    {
        if (!isset($bundle['id'])) {
            $bundle['id'] = null;
        }
        // lets try and get the bundle
        /* @var $pb ProductBundle */
        $pb = BeanFactory::getBean('ProductBundles', $bundle['id']);

        if (isset($bundle['deleted']) && $bundle['deleted'] == 1) {
            $pb->mark_deleted($pb->id);
            $bean->load_relationship('product_bundles');
            $bean->product_bundles->delete($bean->id, $pb);
        } else {
            $pb->team_id = $bean->team_id;
            $pb->team_set_id = $bean->team_set_id;
            $pb->shipping = $bundle['shipping'];
            $pb->currency_id = $bean->currency_id;
            $pb->taxrate_id = $bean->taxrate_id;
            $pb->bundle_stage = $bundle['bundle_stage'];
            $pb->name = $bundle['name'];

            $pb->save();

            // handle the items on the product bundle
            foreach ($bundle['items'] as $item) {
                if ($item['module'] == 'ProductBundleNotes') {
                    $this->handleBundleNoteSave($item, $pb, $bean);
                } else {
                    if ($item['module'] == 'Products') {
                        $this->handleBundleProductSave($item, $pb, $bean);
                    }
                }
            }

            // save the bundle to the quote
            $bean->load_relationship('product_bundles');
            if (!isset($bundle['position'])) {
                $bundle['position'] = count($bean->product_bundles->getBeans());
            }
            $bean->product_bundles->add($pb, array('bundle_index' => $bundle['position']));
        }
    }

    /**
     * @param array $product
     * @param SugarBean|ProductBundle $pb
     * @param SugarBean|Quote $bean
     */
    protected function handleBundleProductSave(array $product, SugarBean $pb, SugarBean $bean)
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

        $product_bean->currency_id = $bean->currency_id;
        $product_bean->base_rate = $bean->base_rate;
        $product_bean->team_id = $bean->team_id;
        $product_bean->team_set_id = $bean->team_set_id;
        $product_bean->quote_id = $bean->id;
        $product_bean->account_id = $bean->billing_account_id;
        $product_bean->contact_id = $bean->billing_contact_id;
        $product_bean->ignoreQuoteSave = true;
        $product_bean->save();

        $pb->load_relationship('products');
        if ($product['deleted'] === 1) {
            $pb->products->delete($pb->id, $product_bean);
        } else {
            if (!isset($note['position'])) {
                $note['position'] = $this->getNextBundleItemPosition($pb);
            }
            $pb->products->add($product_bean, array('product_index' => $product['position']));
        }
    }


    protected function handleBundleNoteSave(array $note, SugarBean $pb, SugarBean $bean)
    {
        if (!isset($note['id'])) {
            $note['id'] = null;
        }
        /* @var $product_bundle_note ProductBundleNote */
        $product_bundle_note = BeanFactory::getBean('ProductBundleNotes', $note['id']);
        $product_bundle_note->deleted = $note['deleted'];
        $product_bundle_note->description = $note['description'];
        $product_bundle_note->save();

        $pb->load_relationship('product_bundle_notes');
        if ($note['deleted'] === 1) {
            $pb->product_bundle_notes->delete($pb->id, $product_bundle_note);
        } else {
            if (!isset($note['position'])) {
                $note['position'] = $this->getNextBundleItemPosition($pb);
            }
            $pb->product_bundle_notes->add($product_bundle_note, array('note_index' => $note['position']));
        }
    }

    /**
     * Return the count for the number of items in the current ProductBundle, by doing this it
     * allows us to set the position if we are missing it.
     *
     * @param SugarBean|ProductBundle $pb
     * @return int
     */
    protected function getNextBundleItemPosition(SugarBean $pb)
    {
        $bundle_items = $pb->get_product_bundle_line_items();
        return count($bundle_items);
    }


    /**
     * Handle Setting the Addresses
     *
     * @param String $fromModule
     * @param String $fromId
     * @param SugarBean|Quote $bean
     */
    protected function setAddressFromBean($fromModule, $fromId, SugarBean $bean)
    {
        $fromBean = BeanFactory::getBean($fromModule, $fromId);
        if ($fromModule == 'Contacts') {
            $bean->shipping_contact_id = $fromId;
            $bean->billing_contact_id = $fromId;
            $type_key = 'primary';
            $alt_type_key = 'alt';
        } elseif ($fromModule == 'Accounts') {
            $bean->billing_account_id = $fromId;
            $bean->shipping_account_id = $fromId;
            $type_key = 'shipping';
            $alt_type_key = 'billing';
        }

        // set the shipping address first
        $this->processBeanAddressFields($fromBean, $bean, 'shipping', $type_key, $alt_type_key);

        // change the type key for the billing address, when we are pulling from Accounts
        if ($fromModule == 'Accounts') {
            $type_key = 'billing';
            $alt_type_key = 'shipping';
        }

        // if the initial bean has an account set on it, we need to to set the billing address
        // to the account address fields vs the contact address fields.
        // if there is no account_id then it will just set the billing address fields from the contact
        if (!empty($fromBean->account_id)) {
            $bean->billing_account_id = $fromBean->account_id;
            $bean->shipping_account_id = $fromBean->account_id;

            unset($fromBean);

            $fromBean = BeanFactory::getBean('Accounts', $bean->shipping_account_id);
            $type_key = 'billing';
            $alt_type_key = 'shipping';
        }

        // set the billing address
        $this->processBeanAddressFields($fromBean, $bean, 'billing', $type_key, $alt_type_key);

    }

    /**
     * Utility Method to set the fields on a given $bean from another bean.
     *
     * @param SugarBean $fromBean
     * @param SugarBean|Quote $bean
     * @param string $bean_type What field type are we setting on the $bean
     * @param string $type The primary type on the $fromBean
     * @param string $alt_type The secondary type on the $fromBean
     */
    protected function processBeanAddressFields($fromBean, $bean, $bean_type, $type, $alt_type)
    {
        $fields = array('street', 'city', 'state', 'postalcode', 'country');
        foreach ($fields as $field) {
            $beanField = $bean_type . "_address_" . $field;
            $bean->$beanField = $this->getAddressFormContact(
                $bean->$beanField,
                $fromBean,
                $type . "_address_" . $field,
                $alt_type . "_address_" . $field
            );
        }
    }

    protected function getAddressFormContact($bean_property, $fromBean, $property, $alt_property)
    {
        return !empty($bean_property) ? $bean_property
            : (isset($fromBean->$property) ? $fromBean->$property
                : (isset($fromBean->$alt_property) ? $fromBean->$alt_property
                    : ''));
    }
}
