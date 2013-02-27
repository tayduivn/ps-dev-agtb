<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/SugarFolders/SugarFolders.php');

/**
 * This test simulates a failure in creating an inbound email from the campaigns 'Email Setup' wizard
 * @ticket 11203
 */
class Bug11203Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $_user = null;

	public function setUp()
    {
        //set the global user to an admin
        global $current_user;
        $this->_user = $current_user;

        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 1;
        $user->save();
        $current_user = $user;


        //build request the way WizardEmailSetupSave.php would
        //Make sure that the credentials will not pass!  We want to test a failed optimums result during inboundEmail save.
        $_REQUEST = array(
              'module' => 'Campaigns',
              'action' =>' WizardEmailSetupSave',
              'mailbox' => 'INBOX',
              'ssl' => '1',
              'email_password' => 'S8bllc',
              'notify_fromname' => 'SugarCRM',
              'mail_sendtype' => 'SMTP',
              'notify_fromaddress' => 'do_not_reply@example.com',
              'mail_smtpserver' => 'smtp.gmail.com',
              'mail_smtpport' => '587',
              'mail_smtpauth_req' => '1',
              'mail_smtpuser' => 'fail@gmail.com',
              'mail_smtppass' => 'fail847d',
              'massemailer_campaign_emails_per_run' => '500',
              'massemailer_tracking_entities_location_type' => '1',
              'name' => 'UnitTest_Mailbox11203',
              'server_url' => 'smtp.gmail.com',
              'email_user' => 'fail@gmail.com',
              'protocol' => 'imap',
              'port' => '993',
              'mark_read' => '1',
              'only_since' => '1',
              'mailbox_type' => 'bounce',
              'from_name' => 'SugarCRM',
              'group_id' => 'new',
              'from_name' =>'fail@gmail.com',
              'from_addr' =>'fail@gmail.com',
              'reply_to_name' =>'failed',
              'reply_to_addr' =>'fail@gmail.com',
              'filter_domain' =>'somedomain.com',
              'email_num_autoreplies_24_hours' =>'10',

          );

	}
    public function tearDown()
    {   global $current_user;
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$current_user->id}'");
        $current_user = $this->_user;
        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE name='UnitTest_Mailbox11203'");
        unset($_REQUEST);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

    }

    function test_CampaignEmailSetupFailure()
    {

        //include the save file like WizardEmailSetupSave.php does
         require_once('modules/InboundEmail/Save.php');

        //Test that the failure was returned.
        $this->assertTrue($_REQUEST['error'], 'Request did not have the error flag set to true after failed Inbound Email Save, this means that the campaign wizard will not display an error as it should have.');
    }

}
?>