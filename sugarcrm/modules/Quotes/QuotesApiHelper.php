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

        // Bug #57888 : REST API: Create related quote must populate billing/shipping contact and account
        if (isset($submittedData['module']) && $submittedData['module'] == 'Contacts' && isset($submittedData['record'])) {
            $this->setAddressFromBean($submittedData['module'], $submittedData['record'], $bean);
        }

        return true;
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
        $bean->shipping_contact_id = $fromId;
        $bean->billing_contact_id = $fromId;

        // set the shipping address first
        $type_key = ($fromModule == 'Accounts') ? 'shipping' : 'primary';
        $alt_type_key = ($fromModule == 'Accounts') ? 'billing' : 'alt';
        $this->processBeanAddressFields($fromBean, $bean, 'shipping', $type_key, $alt_type_key);

        // change the type key for the billing address
        $type_key = ($fromModule == 'Accounts') ? 'billing' : 'primary';
        $alt_type_key = ($fromModule == 'Accounts') ? 'shipping' : 'alt';

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
                $alt_type  . "_address_" . $field
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
