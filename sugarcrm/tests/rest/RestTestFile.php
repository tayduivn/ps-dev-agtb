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

require_once('tests/rest/RestTestBase.php');

class RestTestList extends RestTestBase {
    protected $_note;
    protected $_note_id;
    protected $_contact;
    protected $_contact_id;

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);

        // Create a test contact and a test note
        $contact = new Contact();
        $contact->first_name = 'UNIT TEST';
        $contact->last_name = 'TESTY TEST';
        $contact->save();
        $this->_contact_id = $contact->id;
        $this->_contact = $contact;

        $note = new Note();
        $note->name = 'UNIT TEST';
        $note->description = 'UNIT TEST';
        $note->save();
        $this->_note_id = $note->id;
        $this->_note = $note;
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_contact_id}'");
        $GLOBALS['db']->query("DELETE FROM notes WHERE id = '{$this->_note_id}'");

        unset($this->_contact, $this->_note);
    }

    public function testGetList() {
        $restReply = $this->_restCall('Contacts/' . $this->_contact_id . '/file/');
        $this->assertNotEmpty($restReply['reply'], 'Reply was empty');
        $this->assertArrayHasKey('picture', $restReply['reply'], 'Missing response data for Contacts');

        $restReply = $this->_restCall('Notes/' . $this->_note_id . '/file/');
        $this->assertNotEmpty($restReply['reply'], 'Reply was empty');
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Missing response data for Notes');
    }

    public function testPostUploadImageToContact() {
        $post = array('picture' => '@include/images/badge_256.png');
        $reply = $this->_restCall('Contacts/' . $this->_contact_id . '/file/picture', $post);
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');

        // Grab the contact and make sure it saved
        $fetch = $this->_restCall('Contacts/' . $this->_contact_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Contact ID is missing');
        $this->assertEquals($this->_contact_id, $fetch['reply']['id'], 'Known contact id and fetched contact id do not match');
        $this->assertEquals($reply['reply']['picture']['name'], $fetch['reply']['picture'], 'Contact picture field and picture file name do not match');
    }

    public function testPostUploadImageToContactWithHTMLJSONResponse() {
        $post = array('picture' => '@include/images/badge_256.png');
        $reply = $this->_restCall('Contacts/' . $this->_contact_id . '/file/picture?format=sugar-html-json', $post);
        //$this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        //$this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');
        $this->assertNull($reply['reply'], 'Decoded reply should be null');
        $this->assertNotEmpty($reply['replyRaw'], 'Raw Reply should contain an HTML encoded JSON string');
        $this->assertContains('&quot;xhr&quot;', $reply['replyRaw'], 'Raw reply should contain "xhr"');

        $decoded = json_decode(html_entity_decode($reply['replyRaw']), true);
        $this->assertNotEmpty($decoded['picture']['xhr'], 'Sugar HTML JSON result not decodeable');
        $this->assertInternalType('array', $decoded['picture']['xhr'], 'Expected array after decoding, some other type returned');
    }

    public function testPutUploadImageToContact() {
        $filename = 'include/images/badge_256.png';
        $opts = array(CURLOPT_INFILESIZE => filesize($filename), CURLOPT_INFILE => fopen($filename, 'r'));
        $headers = array('Content-Type: image/png', 'filename: ' . basename($filename));
        $reply = $this->_restCall('Contacts/' . $this->_contact_id . '/file/picture', '', 'PUT', $opts, $headers);
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing field name key');
        $this->assertNotEmpty($reply['reply']['picture']['name'], 'File name not returned');

        // Grab the contact and make sure it saved
        $fetch = $this->_restCall('Contacts/' . $this->_contact_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Contact ID is missing');
        $this->assertEquals($this->_contact_id, $fetch['reply']['id'], 'Known contact id and fetched contact id do not match');
        $this->assertEquals($reply['reply']['picture']['name'], $fetch['reply']['picture'], 'Contact picture field and picture file name do not match');
    }

    public function testDeleteImageFromContact() {
        $reply = $this->_restCall('Contacts/' . $this->_contact_id . '/file/picture', '', 'DELETE');
        $this->assertArrayHasKey('picture', $reply['reply'], 'Reply is missing fields');
    }

    public function testPostUploadFileToNote() {
        $post = array('filename' => '@CORPLICENSE.txt');
        $restReply = $this->_restCall('Notes/' . $this->_note_id . '/file/filename', $post);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->_restCall('Notes/' . $this->_note_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->_note_id, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
    }

    public function testPutUploadFileToNote() {
        $filename = 'CELICENSE.txt';
        $params = array('filename' => $filename, 'type' => 'text/plain');
        $restReply = $this->_restCallPut('Notes/' . $this->_note_id . '/file/filename', $params);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->_restCall('Notes/' . $this->_note_id);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->_note_id, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
    }

    public function testDeleteFileFromNote() {
        $reply = $this->_restCall('Notes/' . $this->_note_id . '/file/filename', '', 'DELETE');
        $this->assertArrayHasKey('filename', $reply['reply'], 'Reply is missing fields');
    }

    protected function _restCallPut($urlPart, $args, $passInQueryString = true) {
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/rest/v10/';
        $filename = basename($args['filename']);
        $url = $urlBase . $urlPart;
        if ($passInQueryString) {
            $conn = strpos('?', $url) === false ? '?' : '&';
            $url .= $conn . 'filename=' . urlencode($filename);
        }

        $filedata = file_get_contents($args['filename']);

        $auth = (!empty($this->authToken)) ? "oauth_token: $this->authToken\r\n" : '';
        $options = array(
            'http' => array(
                'method' => 'PUT',
                'header' => "{$auth}Content-Type: $args[type]\r\nfilename: $filename\r\n",
                'content' => $filedata,
            ),
        );

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return array('info' => array(), 'reply' => json_decode($response,true), 'replyRaw' => $response, 'error' => null);
    }
}

