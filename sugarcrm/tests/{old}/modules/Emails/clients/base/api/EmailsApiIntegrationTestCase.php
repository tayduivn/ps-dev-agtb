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

require_once 'modules/Emails/clients/base/api/EmailsApi.php';

class EmailsApiIntegrationTestCase extends Sugar_PHPUnit_Framework_TestCase
{
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDownAfterClass();
    }

    /**
     * Creates a new Emails record through {@link EmailsApi::createRecord()} that will be deleted during tear down.
     *
     * @param array $args
     * @return array The API response from creating the Emails record.
     */
    protected function createRecord(array $args)
    {
        $args['module'] = 'Emails';
        $args['name'] = 'Sugar Email' . create_guid();
        $args['description'] = 'blah blah blah';
        $args['description_html'] = 'blah <b>blah</b> <i>blah</i>';
        $api = new EmailsApi();
        $record = $api->createRecord($this->service, $args);
        SugarTestEmailUtilities::setCreatedEmail($record['id']);
        return $record;
    }

    /**
     * Updates an existing Emails record through {@link EmailsApi::updateRecord()}.
     *
     * @param string $id The ID of the record to update.
     * @param array $args
     * @return array The API response from updating the Emails record.
     */
    protected function updateRecord($id, array $args)
    {
        $args['module'] = 'Emails';
        $args['record'] = $id;
        $api = new EmailsApi();
        return $api->updateRecord($this->service, $args);
    }

    /**
     * Delete an existing Emails record through {@link EmailsApi::deleteRecord()}.
     *
     * @param string $id The ID of the record to delete.
     * @param array $args
     * @return array The API response from deleting the Emails record.
     */
    protected function deleteRecord($id, array $args)
    {
        $args['module'] = 'Emails';
        $args['record'] = $id;
        $api = new EmailsApi();
        return $api->deleteRecord($this->service, $args);
    }

    /**
     * Returns the right-hand side module name for the specified link on Emails.
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
     * Creates a bean for the right-hand side module of the specified link.
     *
     * The primary email address for all beans is available on $bean->email1 as a convenience for testing that the
     * primary email address was used no matter what type of object it is. Beans from the EmailAddresses module don't
     * have an email1 property, so the value from the email_address property is assigned to email1.
     *
     * @param string $link
     * @return SugarBean
     */
    protected function createRhsBean($link)
    {
        $module = $this->getRhsModule($link);
        $beanName = BeanFactory::getBeanName($module);
        $methodName = $module === 'Users' ? 'createAnonymousUser' : "create{$beanName}";
        $bean = call_user_func(array("SugarTest{$beanName}Utilities", $methodName));

        if ($module === 'EmailAddresses') {
            $bean->email1 = $bean->email_address;
        }

        return $bean;
    }

    /**
     * Asserts that the specified collection contains the expected records.
     *
     * @param array $expected API-formatted records that are expected.
     * @param array $collection The collection of records linked to the Emails record.
     * @param string $message The message to display when the assertion fails.
     */
    protected function assertRecords(array $expected, array $collection, $message = '')
    {
        // Testing for these attributes is unnecessary.
        foreach ($collection['records'] as &$record) {
            unset($record['_acl']);
            unset($record['_locked_fields']);
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

        $this->assertEquals($expected, $collection['records'], $message);
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
     * Converts a database-formatted timestamp to ISO.
     *
     * @param string $timestamp
     * @return string
     */
    protected function getIsoTimestamp($timestamp)
    {
        $td = TimeDate::getInstance();
        return $td->asIso($td->fromDb($timestamp));
    }
}
