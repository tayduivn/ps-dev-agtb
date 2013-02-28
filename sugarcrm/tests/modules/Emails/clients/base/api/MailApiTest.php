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

class MailApiTest extends RestTestBase {
    var $skip_mail_send=true;

    var $current_user;
    var $input;

    var $account;
    var $email_id;

    var $document;
    var $documentRevision;

    var $user_cache_directory;
    var $uploaded_image_file_path;

    var $image_file_id;
    var $image_file_name;

    var $document_upload_directory;
    var $uploaded_document_file_path;
    var $renamed_document_file_path;

    var $document_file_id;
    var $document_file_name;

    var $teams;
    var $team_id;
    var $team_name;

    public function setUp()
    {
        global $current_user;
        parent::setUp();

        $this->current_user = $current_user;
        unset($this->email_id);
        $this->email_config_setup();

        $this->teams=array();
        $this->team_id   = "unit_test_team_id_";
        $this->team_name = "unit_test_team_name_";

        for ($i=0; $i<3; $i++) {
            $id = $this->team_id . $i;
            $name = $this->team_name . $i;
            $description =  "Description for " . $name;
            $sql = "INSERT INTO teams VALUES('$id','$name','',NULL,'2012-08-15 18:00:00','2012-08-15 18:00:00','$current_user->id','$current_user->id',0,'$description',0)";
            $result = $GLOBALS['db']->query($sql);
            $this->teams[] = $id;
        }
        //print_r($this->teams);

        $message = "<br>This is a <span style='color:red'>Test</span> email";

        $this->input = array(
            "email_config"  =>  null,

            "to_addresses"	=>  array(
                array("name" => "Captain Kangaroo",  "email" => "twolf@sugarcrm.com"),
                array("name" => "Mister Moose",  	 "email" => "twb2@webtribune.com"),
            ),

            "cc_addresses"	=> 	array(
                array("name" => "Bunny Rabbit",  	 "email" => "twb3@webtribune.com"),
            ),

            "bcc_addresses"	=> 	null,

            "attachments"	=> 	null,

            "documents"		=>	null,

            "subject"  		=>	"This is a Test Email",

            "html_body" 	=>	urlencode($message),

            "text_body" 	=>	"Hello There World!",

            "related"		=>	null,

            "teams"			=>	null,
        );


        /*---- Create an Uploaded Image File ----------------------------------*/
        $email=new Email();
        $this->user_cache_directory = "{$email->cachePath}/{$current_user->id}";

        //printf("MKDIR  '%s'\n",$this->user_cache_directory);
        mkdir($this->user_cache_directory, 0777, true);

        $this->image_file_name = "packers.tiff";
        $this->image_file_id = create_guid();

        $fromImageFile = "tests/modules/Emails/data/".$this->image_file_name;
        $this->uploaded_image_file_path = "{$this->user_cache_directory}/{$this->image_file_id}";

        //printf("UploadedImageFilePath: '%s'\n",$this->uploaded_image_file_path);
        copy($fromImageFile, $this->uploaded_image_file_path);

        /*---- Create an Uploaded Sugar Document File ----------------------------------*/
        $this->document_upload_directory = "upload";
        $this->document_file_name = "unit_test_document.pdf";

        $_FILES['filename_file'] = 'xxx';
        $this->document = new Document();
        $this->document->id = create_guid();
        $this->document->new_with_id = true;

        /*---- Create the upload file in the upload directory ----*/
        $fromDocumentFile = "tests/modules/Emails/data/".$this->document_file_name;
        $this->uploaded_document_file_path = "{$this->document_upload_directory}/{$this->document->id}";
        copy($fromDocumentFile, $this->uploaded_document_file_path);

        $this->document->revision = '38';
        $this->document->filename = $this->document_file_name;
        $this->document->document_name = 'MailAPI Unit Test Document';
        $this->document->assigned_user_id = $current_user->id;
        $this->document->file_ext = 'pdf';
        $this->document->file_mime_type = "application/pdf";
        $this->document->doc_type = "Sugar";
        $this->document->doc_id  = "";
        $this->document->doc_url = "";
        $this->document->save();

        $this->document->retrieve($this->document->id);

        $this->renamed_document_file_path = "{$this->document_upload_directory}/{$this->document->document_revision_id}";
        $this->document_file_id = $this->document->id;
     }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $sql = "DELETE FROM outbound_email WHERE type='system-override' AND  user_id='" .$GLOBALS['current_user']->id. "'";
        $GLOBALS['db']->query($sql);

        unset($GLOBALS['current_user']);

        if (isset($this->email_id)) {
            $this->deleteEmails($this->email_id);
            unset($this->email_id);
        }

        $sql = "DELETE FROM documents WHERE id = '{$this->document->id}'";
        $GLOBALS['db']->query($sql);
        $sql = "DELETE FROM document_revisions WHERE id = '{$this->document->document_revision_id}'";
        $GLOBALS['db']->query($sql);

        parent::tearDown();
    }


    /**
     * @group email
     * @group mailapi01
     * @group mailer
     */
    public function testCreate_Draft_Success() {
        $this->input["status"] = "draft";

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");
    }

    /**
     * @group email
     * @group mailapi02
     * @group mailer
     */
    public function testCreate_Draft_WithRelationship_Success() {
        $this->input["status"] = "draft";

        $this->input["related"] = array(
            "type"	=> "Accounts",
            "id"	=> "1234567890"
        );

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
            // $emailObject = new Email();
            // $emailObject->retrieve($this->email_id);
            // var_dump($emailObject);
        }

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        //print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");

        $this->assertEquals($this->input["related"]["type"], $email['parent_type'], "Invalid Relationship - Parent Type");
        $this->assertEquals($this->input["related"]["id"],   $email['parent_id'],   "Invalid Relationship - Parent ID");
    }


    /**
     * @group email
     * @group mailapi03
     * @group mailer
     */
    public function testCreate_Draft_WithAttachment_Success() {
        $this->input["status"] = "draft";

         $this->input["attachments"] = array(
            array("name" => $this->image_file_name,
                "id"   => $this->image_file_id
            )
        );

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");
    }


    /**
     * @group email
     * @group mailapi04
     * @group mailer
     */
     public function testCreate_Draft_WithMultipleTeams_Success() {

        $this->input["status"] = "draft";

        $this->input["teams"] = array(
            "primary"	=> $this->teams[0],
            "other"		=> array($this->teams[1], $this->teams[2])
        );

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        //print_r($reply);

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");

        // $this->assertTrue($this->check_team_sets(), "Expected Team Sets Not Created");
        $this->assertEquals($this->teams[0],$email['team_id'],"Unexpected Email Team ID");
    }

    /**
     * @group email
     * @group mailapi05
     * @group mailer
     */
    public function testCreate_Draft_WithSugarDocumentAttached_Success() {
        $this->input["status"] = "draft";

        $this->input["documents"] = array(
            $this->document_file_id
        );

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("draft", $email['type'], "Email Type Incorrect");
        $this->assertEquals("draft", $email['status'], "Email Status Incorrect");
    }


    /**
     * @group email
     * @group mailapi06
     * @group mailer
     */
    public function testCreate_Ready_Success() {
        if ($this->skip_mail_send) {
            $this->markTestSkipped("Not real sure how to test actually sending of the mail");
        }

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"twolf@sugarcrm.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $reply = $post_response['reply'];
        // print_r($reply);

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("out", $email['type'], "Email Type Incorrect");
        $this->assertEquals("sent", $email['status'], "Email Status Incorrect");
    }


    /**
     * @group email
     * @group mailapi07
     * @group mailer
     */
    public function testCreate_Ready_WithAttachment_Success() {
        if ($this->skip_mail_send) {
            $this->markTestSkipped("Not real sure how to test actually sending of the mail");
        }

        $this->input["attachments"] = array(
            array("name" => $this->image_file_name,
                  "id"   => $this->image_file_id
            )
        );

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"twolf@sugarcrm.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        // printf("Current User = '%s' : '%s'\n\n",$this->current_user->id,  $this->current_user->name);

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $reply = $post_response['reply'];
        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];
        // print_r($email);

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("out", $email['type'], "Email Type Incorrect");
        $this->assertEquals("sent", $email['status'], "Email Status Incorrect");
    }


    /**
     * @group email
     * @group mailapi08
     * @group mailer
     */
    public function testCreate_Ready_WithSugarDocumentAttached_Success() {
        if ($this->skip_mail_send) {
            $this->markTestSkipped("Not real sure how to test actually sending of the mail");
        }

        $this->input["documents"] = array(
             $this->document_file_id
        );

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"twolf@sugarcrm.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        $this->assertHttpStatus($post_response);

        $reply = $post_response['reply'];

        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_message']."\n";
        }
        if (isset($reply['EMAIL']['id'])) {
            $this->email_id = $reply['EMAIL']['id'];
        }

        $success = (int) $reply['SUCCESS'];
        $this->assertEquals(1,$success, "Not Successful");

        $email = $reply['EMAIL'];

        $this->assertEquals(36, strlen($email['id']), "Email ID Invalid");
        $this->assertEquals($this->input["subject"], $email['name'], "Email Subject Incorrect");
        $this->assertEquals("out", $email['type'], "Email Type Incorrect");
        $this->assertEquals("sent", $email['status'], "Email Status Incorrect");
    }


    /**
     * @group email
     * @group mailapi09
     * @group mailer
     */
    public function testCreate_InvalidStatus() {
        $this->input["status"] = "bogus";

        $post_response = $this->_restCall("/Mail/", json_encode($this->input), 'POST');
        // print_r($post_response);

        $this->assertEquals(412, $post_response['info']['http_code'], "Expected Request Failure Http Status Code");
        $this->assertEquals("request_failure", $post_response['reply']['error'], "Expected Request Failure Response");
        $this->assertEquals("Invalid Status Property", $post_response['reply']['error_message'], "Expected Request Failure Response");
    }


    /**
     *
     * Private Helper Methods
     *
     */
    private function check_team_sets() {
        $ids = array();
        $save_team_set_id=null;

        $sql1 = "SELECT team_id, team_set_id FROM team_sets_teams WHERE team_id like '{$this->team_id}%'";
        $result = $GLOBALS['db']->query($sql1);
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $team_set_id = $row['team_set_id'];
            $save_team_set_id = $team_set_id;
        }

        $sql2 = "SELECT id, team_id FROM team_sets_teams WHERE team_set_id = '$save_team_set_id'";
        $result = $GLOBALS['db']->query($sql2);
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $id = $row['id'];
            $ids[$id] = true;
        }

        $count=0;
        $sql3 = "SELECT id FROM team_sets WHERE id = '$save_team_set_id'";
        $result = $GLOBALS['db']->query($sql3);
        if ($result) {
            while($row = $GLOBALS['db']->fetchByAssoc($result)) {
                $count++;
            }
        }

        /**
        printf("SQL1: %s\n",$sql1);
        printf("save_team_set_id: %s\n",$save_team_set_id);
        printf("SQL2: %s\n",$sql1);
        print_r($ids);
        printf("COUNT IDS: %d\n",count($ids));

        printf("SQL3: %s\n",$sql3);
        printf("COUNT TEAM SETS: %d\n",$count);
        **/

        if (count($ids) != 3)
            return false;   // should be exactly three
        if ($count != 1)
            return false;   // should be exactly one

        return true;
    }



    private function deleteEmails($email_id) {

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


    private function email_config_setup() {
        $r1 = $GLOBALS['db']->query('SELECT config.value FROM config WHERE name=\'fromaddress\'');
        $r2 = $GLOBALS['db']->query('SELECT config.value FROM config WHERE name=\'fromname\'');
        $a1 = $GLOBALS['db']->fetchByAssoc($r1);
        if (empty($a1)) {
            $a1="test@phpunit.org";
            $sql = "INSERT into config VALUES('notify', 'fromaddress', '$a1')";
            $GLOBALS['db']->query($sql);
        }
        $a2 = $GLOBALS['db']->fetchByAssoc($r2);
        if (empty($a2)) {
            $a2="Unit Test";
            $sql = "INSERT into config VALUES('notify', 'fromname', '$a2')";
            $GLOBALS['db']->query($sql);
        }

        $q = "SELECT id FROM outbound_email WHERE user_id == '" .$this->current_user->id. "'";
        $r = $GLOBALS['db']->query($q);
        $a = $GLOBALS['db']->fetchByAssoc($r);

        $oe = new OutboundEmail();
        if(empty($a)) {
            $oe->id = '';
            $oe->name = 'system';
            $oe->type = 'system-override';
            $oe->user_id = $this->current_user->id;
            $oe->mail_sendtype = 'SMTP';
            $oe->mail_smtptype = 'other';
            $oe->mail_smtpserver = 'localhost';
            $oe->mail_smtpport = 25;
            $oe->mail_smtpuser = '';
            $oe->mail_smtppass = '';
            $oe->mail_smtpauth_req = 0;
            $oe->mail_smtpssl = 0;
            $oe->save();
        }
    }
}
