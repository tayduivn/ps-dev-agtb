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

class EmailsApiIntegrationTestCase extends PHPUnit_Framework_TestCase
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
