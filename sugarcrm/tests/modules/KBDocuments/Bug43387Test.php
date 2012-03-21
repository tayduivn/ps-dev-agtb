<?php
//File SUGARCRM flav=pro ONLY
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
     * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
     ********************************************************************************/

    /**
     * This tests that the outgoing email contains the proper bean values
     * @ticket 43387
     */
require_once 'modules/KBDocuments/KBDocument.php';

class Bug43387Test extends Sugar_PHPUnit_Framework_TestCase
{
    
    private $_kb =null;

    public function setUp()
    {
        $this->markTestIncomplete("Test failing on DB2");

        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'ACL');

        //create the KBDocument record
        $this->_kb = new KBDocument();
        $this->_kb->description = 'This is a unit test for bug 43387';
        $this->_kb->kbdocument_name = 'KBUnitTest 43387';
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
    public function testKBDocumentEmailBody()
    {
        global $timedate;
        //call the function that builds the email body
        $notify_mail = $this->_kb->create_notification_email($GLOBALS['current_user']);

        //grab the email body
        $body = $notify_mail->Body;

        //check to see that date is in the body
        $this->assertContains($timedate->to_display_date_time($this->_kb->date_entered), $body, 'KB date was not found in the email body');

        //check to see that subject is in the body
        $this->assertContains($this->_kb->kbdocument_name, $body, 'KB Subject was not found in the email body');

        //check to see that description is in the body
        $this->assertContains($this->_kb->description, $body, 'KB Description was not found in the email body');
    }
}