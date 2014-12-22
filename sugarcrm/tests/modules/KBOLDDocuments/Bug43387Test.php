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

    /**
     * This tests that the outgoing email contains the proper bean values
     * @ticket 43387
     */
require_once 'modules/KBOLDDocuments/KBOLDDocument.php';

class Bug43387Test extends Sugar_PHPUnit_Framework_TestCase
{
    
    private $_kb =null;

    public function setUp()
    {
        $this->markTestIncomplete("Test failing on DB2");

        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'ACL');

        //create the KBOLDDocument record
        $this->_kb = new KBOLDDocument();
        $this->_kb->description = 'This is a unit test for bug 43387';
        $this->_kb->kbolddocument_name = 'KBUnitTest 43387';
        $this->_kb->status = 'In Review';
        $this->_kb->assigned_user_id = $GLOBALS['current_user']->id;
        $this->_kb->save();


    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id = '".$this->_kb->id."'");
        unset($this->_kb);
    }

    /**
     * Test that outgoing emails have expected values
     */
    public function testKBOLDDocumentEmailBody()
    {
        global $timedate;
        //call the function that builds the email body
        $notify_mail = $this->_kb->create_notification_email($GLOBALS['current_user']);

        //grab the email body
        $body = $notify_mail->Body;

        //check to see that date is in the body
        $this->assertContains($timedate->to_display_date_time($this->_kb->date_entered), $body, 'KB date was not found in the email body');

        //check to see that subject is in the body
        $this->assertContains($this->_kb->kbolddocument_name, $body, 'KB Subject was not found in the email body');

        //check to see that description is in the body
        $this->assertContains($this->_kb->description, $body, 'KB Description was not found in the email body');
    }
}
