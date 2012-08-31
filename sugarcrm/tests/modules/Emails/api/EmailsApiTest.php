<?php
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

require_once('tests/rest/RestTestBase.php');

class EmailsApiTest extends RestTestBase {
    var $input;

    public function setUp()
    {
        $message = "<br>This is a <span style='color:red'>Test</span> email";

        $this->input = array(
            // "toaddress"		=> array("sparsley@sugarcrm.com", "nharding@sugarcrm.com", "twolf@sugarcrm.com"),
            "to_addresses"	=>  array(
                array("name" => "Captain Kangaroo",  "email" => "twolf@sugarcrm.com"),
                /*	array("name" => "Donald Duck",  	 "email" => "glevine@sugarcrm.com"), */
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

            "html_body" 	=>	urlencode($message),

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


        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    public function testCreate_Draft_Success() {
        $this->input["status"] = "draft";

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");

        $reply = $post_response['reply'];
        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");

        $this->deleteEmails($email['id']);
    }


    public function testCreate_Ready_Success() {

        $this->markTestSkipped("Not real sure how to actually test successful sending");

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"tim.tj.wolf@gmail.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;


        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");

        $reply = $post_response['reply'];
        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("out", $email['type'], "Email Type Incorrect");
        $this->assertEquals("sent", $email['status'], "Email Status Incorrect");

        $this->deleteEmails($email['id']);
    }


    public function testCreate_Ready_EmailSendFailed() {

        $this->markTestSkipped("Not real sure how to actually test sending to bad email address");

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"bogus_email_unit_test@webtribune.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");

        $reply = $post_response['reply'];
        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(0, $success, "Expected Mail Send to Fail");
    }


    public function testCreate_InvalidStatus() {
        $this->input["status"] = "bogus";

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        // print_r($post_response);

        $this->assertEquals(412, $post_response['info']['http_code'], "Expected Request Failure Http Status Code");
        $this->assertEquals("request_failure", $post_response['reply']['error'], "Expected Request Failure Response");
        $this->assertEquals("Invalid Status", $post_response['reply']['error_description'], "Expected Request Failure Response");
    }

    /**
     *
     * Private Helper Methods
     *
     */

    private function deleteEmails($email_id) {
        $sql = "DELETE FROM emails WHERE id = '{$email_id}'";
        // echo $sql;
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM emails_text WHERE email_id = '{$email_id}'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM emails_email_addr_rel WHERE email_id = '{$email_id}'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM notes WHERE parent_id = '{$email_id}'";
        $GLOBALS['db']->query($sql);

        if (is_array($this->input["to_addresses"])) {
            foreach($this->input["to_addresses"] AS $address) {
                $email_address = $address["email"];
                $sql = "DELETE FROM email_addresses WHERE email_address = '$email_address'";
                $GLOBALS['db']->query($sql);
            }
        }
        if (is_array($this->input["cc_addresses"])) {
            foreach($this->input["cc_addresses"] AS $address) {
                $email_address = $address["email"];
                $sql = "DELETE FROM email_addresses WHERE email_address = '$email_address'";
                $GLOBALS['db']->query($sql);
            }
        }
        if (is_array($this->input["bcc_addresses"])) {
            foreach($this->input["bcc_addresses"] AS $address) {
                $email_address = $address["email"];
                $sql = "DELETE FROM email_addresses WHERE email_address = '$email_address'";
                $GLOBALS['db']->query($sql);
            }
        }
    }
}