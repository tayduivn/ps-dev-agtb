<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once('data/SugarBeanApiHelper.php');

class QuotesApiHelper extends SugarBeanApiHelper
{
    /**
     * This function sets up shipping and billing address for new Quote.
     *
     * @param SugarBean $bean
     * @param array $submittedData
     * @param array $options
     * @return array
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        $data = parent::populateFromApi($bean, $submittedData, $options);

        // Bug #57888 : REST API: Create related quote must populate billing/shipping contact and account
        if ( isset($submittedData['module']) && $submittedData['module'] == 'Contacts' && isset($submittedData['record']) )
        {
            $contactBean = BeanFactory::getBean('Contacts', $submittedData['record']);
            $bean->shipping_contact_id = $submittedData['record'];
            $bean->billing_contact_id = $submittedData['record'];

            $bean->shipping_address_street      = $this->getAddressFormContact ($bean->shipping_address_street, $contactBean, 'address_street' );
            $bean->shipping_address_city        = $this->getAddressFormContact( $bean->shipping_address_city, $contactBean, 'address_city' );
            $bean->shipping_address_state       = $this->getAddressFormContact( $bean->shipping_address_state, $contactBean, 'address_state' );
            $bean->shipping_address_postalcode  = $this->getAddressFormContact( $bean->shipping_address_postalcode, $contactBean, 'address_street' );
            $bean->shipping_address_country     = $this->getAddressFormContact( $bean->shipping_address_country, $contactBean, 'address_street' );

            if ( !empty($contactBean->account_id) )
            {
                $bean->billing_account_id = $contactBean->account_id;
                $bean->billing_address_street      = $this->getAddressFormContact ($bean->billing_address_street, $contactBean, 'address_street' );
                $bean->billing_address_city        = $this->getAddressFormContact( $bean->billing_address_city, $contactBean, 'address_city' );
                $bean->billing_address_state       = $this->getAddressFormContact( $bean->billing_address_state, $contactBean, 'address_state' );
                $bean->billing_address_postalcode  = $this->getAddressFormContact( $bean->billing_address_postalcode, $contactBean, 'address_street' );
                $bean->billing_address_country     = $this->getAddressFormContact( $bean->billing_address_country, $contactBean, 'address_street' );
            }
        }

        return $data;
    }

    protected function getAddressFormContact($bean_property, $bean, $property)
    {
        $primary_property = 'primary_'.$property;
        $alt_property = 'alt_'.$property;
        return !empty($bean_property) ? $bean_property
            : ( isset($bean->$primary_property) ? $bean->$primary_property
                : ( isset($bean->$alt_property) ? $bean->$alt_property
                    : '' ) );
    }
}
