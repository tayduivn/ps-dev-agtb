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

        $sql = "DELETE FROM teams WHERE name like 'Sugar%'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM teams WHERE name like '{$this->team_name}%'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM team_sets";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM team_sets_teams";
        $GLOBALS['db']->query($sql);

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
        $this->document->document_name = 'EmailsAPI Unit Test Document';
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
        // $sql = "DELETE FROM users WHERE first_name = 'SugarUser'";
        // $sql = "DELETE FROM users WHERE id = '{$this->current_user->id}'";
        // $GLOBALS['db']->query($sql);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $sql = "DELETE FROM teams WHERE name like 'Sugar%'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM teams WHERE name like '{$this->team_name}%'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM team_sets";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM team_sets_teams";
        $GLOBALS['db']->query($sql);

        if (isset($this->email_id)) {
            $this->deleteEmails($this->email_id);
            unset($this->email_id);
        }

        if (file_exists($this->uploaded_image_file_path)) {
            $res=unlink($this->uploaded_image_file_path);
            //printf("UNLINK  '%s '(RES=%d)\n",$this->uploaded_image_file_path, $res);
        }
        if (file_exists($this->user_cache_directory)) {
            $res=rmdir($this->user_cache_directory);
            //printf("RMDIR  '%s '(RES=%d)\n",$this->user_cache_directory, $res);
        }

        if (file_exists($this->uploaded_document_file_path)) {
            $res=unlink($this->uploaded_document_file_path);
            //printf("UNLINK Uploaded Document File: '%s '(RES=%d)\n",$this->uploaded_document_file_path, $res);
        }

        if (file_exists($this->renamed_document_file_path)) {
            $res=unlink($this->renamed_document_file_path);
            //printf("UNLINK Renamed Document File: '%s '(RES=%d)\n",$this->renamed_document_file_path, $res);
        }

        $sql = "DELETE FROM documents WHERE id = '{$this->document->id}'";
        $GLOBALS['db']->query($sql);
        $sql = "DELETE FROM document_revisions WHERE id = '{$this->document->document_revision_id}'";
        $GLOBALS['db']->query($sql);

        parent::tearDown();
    }



    public function testCreate_Draft_Success() {
        $this->input["status"] = "draft";

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group emails_related
     */
    public function testCreate_Draft_WithRelationship_Success() {
        $this->input["status"] = "draft";

        $this->input["related"] = array(
            "type"	=> "Accounts",
            "id"	=> "1234567890"
        );

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group emails_attachment
     */
    public function testCreate_Draft_WithAttachment_Success() {
        $this->input["status"] = "draft";

         $this->input["attachments"] = array(
            array("name" => $this->image_file_name,
                "id"   => $this->image_file_id
            )
        );

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group emails_multiple_teams
     */
     public function testCreate_Draft_WithMultipleTeams_Success() {

        $this->input["status"] = "draft";

        $this->input["teams"] = array(
            "primary"	=> $this->teams[0],
            "other"		=> array($this->teams[1], $this->teams[2])
        );

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];
        // print_r($reply);

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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

        $this->assertTrue($this->check_team_sets(), "Expected Team Sets Not Created");
        $this->assertEquals($this->teams[0],$email['team_id'],"Unexpected Email Team ID");
        $this->assertEquals($this->get_team_set_id(),$email['team_set_id'],"Unexpected Email Team Set ID");
    }

    /**
     * @group emails_document
     */
    public function testCreate_Draft_WithSugarDocumentAttached_Success() {
        $this->input["status"] = "draft";

        $this->input["documents"] = array(
            array("name" => $this->document_file_name,
                  "id"   => $this->document_file_id
            )
        );

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];
        // print_r($reply);

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group send
     */
    public function testCreate_Ready_Success() {
        if ($this->skip_mail_send) {
            $this->markTestSkipped("Not real sure how to test actually sending of the mail");
        }

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"twolf@sugarcrm.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group send
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

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $this->assertEquals(200, $post_response['info']['http_code'], "Bad Http Status Code");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
     * @group send
     */
    public function testCreate_Ready_WithSugarDocumentAttached_Success() {
        if ($this->skip_mail_send) {
            $this->markTestSkipped("Not real sure how to test actually sending of the mail");
        }

        $this->input["documents"] = array(
            array("name" => $this->document_file_name,
                  "id"   => $this->document_file_id
            ),
        );

        $this->input["status"] = "ready";
        $this->input["to_addresses"] = array( array("name"=>"Unit Test",  "email"=>"twolf@sugarcrm.com") );
        $this->input["cc_addresses"] = null;
        $this->input["bcc_addresses"] = null;

        $post_response = $this->_restCall("/Emails/", json_encode($this->input), 'POST');
        $reply = $post_response['reply'];
        // print_r($reply);

        $http_status = $post_response['info']['http_code'];
        $this->assertEquals(200, $http_status, "Unexpected HTTP Status: " . $http_status."\n");
        if (isset($reply['error'])) {
            echo "Error Type: " . $reply['error'] . " Error Message: " . $reply['error_description']."\n";
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
    private function check_team_sets() {
        $ids = array();
        $team_set_ids = array();
        $save_team_set_id=null;

        $sql1 = "SELECT team_id, id, team_set_id FROM team_sets_teams WHERE team_id like '{$this->team_id}%'";
        $result = $GLOBALS['db']->query($sql1);
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $id = $row['id'];
            $team_set_id = $row['team_set_id'];
            $ids[$id] = true;
            $team_set_ids[$team_set_id] = true;
            $save_team_set_id = $team_set_id;
        }

        $count=0;
        $sql2 = "SELECT id FROM team_sets WHERE id = '$save_team_set_id'";
        $result = $GLOBALS['db']->query($sql2);
        if ($result) {
            while($row = $GLOBALS['db']->fetchByAssoc($result)) {
                $count++;
            }
        }

        /*
        printf("save_team_set_id: %s\n",$save_team_set_id);
        printf("SQL1: %s\n",$sql1);
        print_r($ids);
        printf("COUNT IDS: %d\n",count($ids)); // SHOULD BE THREE
        print_r($team_set_ids);
        printf("COUNT TEAM SET IDS: %d\n",count($team_set_ids)); // SHOULD BE ONE

        printf("SQL2: %s\n",$sql2);
        printf("COUNT TEAM SETS: %d\n",$count);
        */

        if (count($ids) != 3)
            return false;   // should be exactly three
        if (count($team_set_ids) != 1)
            return false;   // should be exactly one
        if ($count != 1)
            return false;   // should be exactly one

        return true;
    }


    private function get_team_set_id() {
        $sql1 = "SELECT team_id, id, team_set_id FROM team_sets_teams WHERE team_id like '{$this->team_id}%' LIMIT 1";
        $result = $GLOBALS['db']->query($sql1);
        if ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            return $row['team_set_id'];
        }
        return '';
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


        $q = "SELECT id FROM outbound_email WHERE type = 'system'";
        $r = $GLOBALS['db']->query($q);
        $a = $GLOBALS['db']->fetchByAssoc($r);

        $oe = new OutboundEmail();
        if(empty($a)) {
            $oe->id = '';
            $oe->name = 'system';
            $oe->type = 'system';
            $oe->user_id = '1';
            $oe->mail_sendtype = 'SMTP';
            $oe->mail_smtptype = 'other';
            $oe->mail_smtpserver = 'localhost';
            $oe->mail_smtpport = 25;
            $oe->mail_smtpuser = '';
            $oe->mail_smtppass = '';
            $oe->mail_smtpauth_req = 1;
            $oe->mail_smtpssl = 0;
            $oe->save();
        }
        else {
            $oe->retrieve($a['id']);
            if(empty($oe->mail_smtpserver)) {
                $oe->mail_sendtype = 'SMTP';
                $oe->mail_smtptype = 'other';
                $oe->mail_smtpserver = 'localhost';
                $oe->save();
            }
        }
    }
}