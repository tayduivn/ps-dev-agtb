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

require_once 'tests/modules/Emails/clients/base/api/EmailsApiIntegrationTestCase.php';

class EmailsApiParticipantsTestCase extends EmailsApiIntegrationTestCase
{
    public static function tearDownAfterClass()
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        parent::tearDownAfterClass();
    }

    /**
     * Returns the RHS module name from the specified link on Emails.
     *
     * @param string $link
     * @return string
     * @throws Exception
     */
    protected function getRhsModule($link)
    {
        switch ($link) {
            case 'accounts_from':
            case 'accounts_to':
            case 'accounts_cc':
            case 'accounts_bcc':
                return 'Accounts';
            case 'contacts_from':
            case 'contacts_to':
            case 'contacts_cc':
            case 'contacts_bcc':
                return 'Contacts';
            case 'email_addresses_from':
            case 'email_addresses_to':
            case 'email_addresses_cc':
            case 'email_addresses_bcc':
                return 'EmailAddresses';
            case 'leads_from':
            case 'leads_to':
            case 'leads_cc':
            case 'leads_bcc':
                return 'Leads';
            case 'prospects_from':
            case 'prospects_to':
            case 'prospects_cc':
            case 'prospects_bcc':
                return 'Prospects';
            case 'users_from':
            case 'users_to':
            case 'users_cc':
            case 'users_bcc':
                return 'Users';
            default:
                throw new Exception('Invalid link name');
        }
    }

    /**
     * Creates a bean representing the sender for the specified link.
     *
     * The bean's primary email address is populated on the email_address property if the bean is not an
     * {@link EmailAddress}. This is a convenience that allows for using $bean->email_address to test that the primary
     * email address was used no matter what type of object it is. We're standardizing on the email_address property
     * because Email Addresses do not have primary email addresses -- but they always have an email_address property --
     * and the email_address property is free to use for all of the other modules.
     *
     * @param string $link
     * @return SugarBean
     */
    protected function createParticipantBean($link)
    {
        $module = $this->getRhsModule($link);
        $beanName = BeanFactory::getBeanName($module);
        $methodName = $module === 'Users' ? 'createAnonymousUser' : "create{$beanName}";
        $bean = call_user_func(array("SugarTest{$beanName}Utilities", $methodName));

        if (!empty($bean->emailAddress)) {
            $bean->email_address = $bean->emailAddress->getPrimaryAddress($bean);
        }

        return $bean;
    }

    /**
     * Retrieves the specified collection for an Emails record using {@link RelateCollectionApi::getCollection()} as a
     * convenience for use in assertions.
     *
     * @param string $id The ID of the Emails record that contains the collection.
     * @param string $collection The name of the collection field.
     * @return array
     */
    protected function getCollection($id, $collection)
    {
        $args = array(
            'module' => 'Emails',
            'record' => $id,
            'collection_name' => $collection,
            'fields' => array(
                'email_address_used',
            ),
        );
        $api = new RelateCollectionApi();
        return $api->getCollection($this->service, $args);
    }

    /**
     * Asserts that the specified collection contains the expected records.
     *
     * @param array $expected API-formatted records that are expected.
     * @param array $collection The collection of records linked to the Emails record.
     */
    protected function assertRecords(array $expected, array $collection)
    {
        // Testing for _acl is unnecessary.
        foreach ($collection['records'] as &$record) {
            unset($record['_acl']);
        }

        /**
         * Sorts the array of records by it's "id" attribute.
         *
         * @param array $a
         * @param array $b
         * @return int
         */
        $rsort = function (array $a, array $b) {
            return ($a['id'] < $b['id']) ? -1 : 1;
        };

        // Sort the records so they can be compared with confidence. We don't care so much about asserting that the API
        // responded with the records in a certain order.
        usort($expected, $rsort);
        usort($collection['records'], $rsort);

        $this->assertEquals($expected, $collection['records']);
    }
}
