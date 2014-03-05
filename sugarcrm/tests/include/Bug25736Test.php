<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once('include/export_utils.php');

/**
 * Test if non-primary emails are being exported properly to a CSV file
 * from Accounts module, or modules based on Person
 */
class Bug25736ExportTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
    }

    /**
     * Check if non-primary mails are being exported properly
     * as semi-colon separated values
     *
     * @dataProvider providerEmailExport
     */
    public function testEmailExport($factory, $data, $expected)
    {
        /** @var SugarBean $bean */
        $bean = call_user_func($factory);

        // Add non-primary mails
        foreach ($data as $email) {
            list($address, $invalid, $optOut) = $email;
            $bean->emailAddress->addAddress($address, false, false, $invalid, $optOut);
        }
        $bean->emailAddress->save($bean->id, $bean->module_name);

        // Export the record
        $content = export($bean->module_name, $bean->id, false, false);

        $this->assertContains($expected, $content, 'Email addresses are not properly exported.');
    }

    /**
     * Module to be exported
     * Mails to be added as non-primary
     */
    public function providerEmailExport()
    {
        $factories = array(
            array('SugarTestAccountUtilities', 'createAccount'),
            array('SugarTestContactUtilities', 'createContact'),
            array('SugarTestLeadUtilities', 'createLead'),
            array('SugarTestProspectUtilities', 'createProspect'),
        );

        $data = array();
        foreach ($factories as $factory) {
            $data[] = array(
                $factory,
                array(
                    array('test1@mailmail.mail', true, false),
                    array('test2@mailmail.mail', false, true),
                ),
                'test1@mailmail.mail,1,0;test2@mailmail.mail,0,1',
            );
        }

        return $data;
    }
}
