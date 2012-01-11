<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('modules/Emails/Email.php');

class Bug40911 extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user;
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Save a SugarFolder 
     */
    public function testSaveNewFolder()
    {
        $this->markTestIncomplete('This test takes to long to run');
        return;
        
        global $current_user, $app_strings;

        $email = new Email();
        $email->type = 'out';
        $email->status = 'sent';
        $email->from_addr_name = $email->cleanEmails("sender@domain.eu");
        $email->to_addrs_names = $email->cleanEmails("to@domain.eu");
        $email->cc_addrs_names = $email->cleanEmails("cc@domain.eu");
        $email->save();

        $_REQUEST["emailUIAction"] = "getSingleMessageFromSugar";
        $_REQUEST["uid"] = $email->id;
        $_REQUEST["mbox"] = "";
        $_REQUEST['ieId'] = "";
        ob_start();
        require "modules/Emails/EmailUIAjax.php";
        $jsonOutput = ob_get_contents();
        ob_end_clean();
        $meta = json_decode($jsonOutput);

        $this->assertRegExp("/.*cc@domain.eu.*/", $meta->meta->email->cc);
    }
    
}


