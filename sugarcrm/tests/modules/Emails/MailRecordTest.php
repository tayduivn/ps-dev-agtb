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

require_once('modules/Emails/MailRecord.php');
require_once('modules/Emails/EmailUI.php');

/**
 *
 */
class MailRecordTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $input;

    public function setUp()
    {
        // $user = new User();
        // $user->retrieve('a19adb46-86ee-aebd-c361-503f74779927');
        // $GLOBALS["current_user"] = $user;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->input = array(
            "email_config"  =>  "1234567890", // "7c8d3023-dddb-144c-105e-504e1e872b06",

            "to_addresses"	=>  array(
                array("name" => "Captain Kangaroo",  "email" => "twolf@sugarcrm.com"),
                array("name" => "Mister Moose",  	 "email" => "twb2@webtribune.com"),
            ),

            "cc_addresses"	=> 	array(
                array("name" => "Bunny Rabbit",  	 "email" => "twb3@webtribune.com"),
            ),

            "bcc_addresses"	=> 	null,

            "attachments"	=> 	array(
                array("name" => "rodgers.tiff",  "id" => "5beb1fad-9aa4-c3ed-b7f8-50363d5e3a2b"),
            ),
            "documents"		=>	array(
                array("name" => "schedule.pdf",  "id" => "123456789012345678901234567890123456"),
            ),

            "subject"  		=>	"This is a Test Email",

            "html_body" 	=>	urlencode("<div>Hello World!</div>"),

            "text_body" 	=>	"Hello There World!",

            "related"		=>	array(
                "type"	=> "Opportunities",
                "id"	=> "102181a2-5c05-b879-8e68-502279a8c401"
            ),

            "teams"			=>	array(
                "primary"	=> "West",
                "other"		=> array("1", "East")
            ),
        );

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testSaveAsDraft_Success ()
    {
        global $current_user;

        $mailRecord = new MailRecord($current_user);

        $mailRecord->mailConfig   = $this->input["email_config"];
        $mailRecord->toAddresses  = $this->input["to_addresses"];
        $mailRecord->ccAddresses  = $this->input["cc_addresses"];
        $mailRecord->bccAddresses = $this->input["bcc_addresses"];

        $mailRecord->attachments  = $this->input["attachments"];
        $mailRecord->documents    = $this->input["documents"];
        $mailRecord->teams        = $this->input["teams"];
        $mailRecord->related      = $this->input["related"];

        $mailRecord->subject      = $this->input["subject"];
        $mailRecord->html_body    = $this->input["html_body"];
        $mailRecord->text_body    = $this->input["text_body"];

        $emailRequest = array (
            'fromAccount' => '1234567890',
            'sendSubject' => 'This is a Test Email',
            'sendTo' => 'Captain Kangaroo <twolf@sugarcrm.com>, Mister Moose <twb2@webtribune.com>',
            'sendCc' => 'Bunny Rabbit <twb3@webtribune.com>',
            'sendBcc' => '',
            'saveToSugar' => '1',
            'sendDescription' => '<div>Hello World!</div>',
            'setEditor' => '1',
            'attachments' => '5beb1fad-9aa4-c3ed-b7f8-50363d5e3a2brodgers.tiff',
            'documents' => '123456789012345678901234567890123456',
            'parent_type' => 'Opportunities',
            'parent_id' => '102181a2-5c05-b879-8e68-502279a8c401',
            'primaryteam' => 'West',
            'teamIds' => 'West,1,East',
            'saveDraft' => 'true',
        );

        $emailBeanResponseValue = true;

        $mockEmailBean =  $this->getMock('Email' , array('email2Send'));
        $mockEmailBean->expects($this->once())
                ->method('email2Send')
                ->with($emailRequest)
                ->will($this->returnValue($emailBeanResponseValue));

        $mailRecord->emailBean = $mockEmailBean;
        $result = $mailRecord->saveAsDraft();

        //unset($result['EMAIL']);
        //print_r($result);

        $this->assertEquals($result['SUCCESS'],  $emailBeanResponseValue, "Unexpected Success Value");
    }


    public function testSaveAsDraft_FromAccountsUnavailable_ExceptionThrown()
    {
        global $current_user;

        $mailRecord = new MailRecord($current_user);

        $fromAccounts = array();

        $mockEmailUIBean =  $this->getMock('EmailUI', array('getFromAccountsArray'));
        $mockEmailUIBean->expects($this->once())
            ->method('getFromAccountsArray')
            ->will($this->returnValue($fromAccounts));

        $mockEmailBean =  $this->getMock('Email', array('email2init'));
        $mockEmailBean->expects($this->once())
            ->method('email2init');

        $mockEmailBean->et = $mockEmailUIBean;
        $mailRecord->emailBean = $mockEmailBean;

        /**/
        $email = new Email();
        $email->email2init();

        $ie = new InboundEmail();
        $ie->email = $email;

        $fromAccounts = $email->et->getFromAccountsArray($ie);
        // print_r($fromAccounts);
        // exit;
        /**/

        try {
            $mailRecord->saveAsDraft();
            $this->fail('Expected an Exception: FromAccount Configuration Data Not Valid');
        } catch(Exception $ex) {
            return;
        }
    }
}
?>