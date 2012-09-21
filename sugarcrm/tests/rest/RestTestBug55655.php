<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestPortalBase.php');

/**
 * Bug 55655 
 * 
 * Portal user could not add an attachment to an existing note. This tests
 * being able to add an attachment to an existing note but not being able
 * to edit that attachment. 
 */
class RestTestBug55655 extends RestTestPortalBase
{
    protected $_testfile1 = 'Bug55655-01.txt';
    protected $_testfile2 = 'Bug55655-02.txt';
    
    public function setUp()
    {
        parent::setUp();
        
        // Create two sample text files for uploading
        sugar_file_put_contents($this->_testfile1, create_guid());
        sugar_file_put_contents($this->_testfile2, create_guid());
    }
    
    public function tearDown()
    {
        unlink($this->_testfile1);
        unlink($this->_testfile2);
        parent::tearDown();
    }
    
    public function testAddingNoteAttachmentToBugAsSupportPortal()
    {
        $bugReply = $this->_restCall(
            "Bugs/",
            json_encode(array(
                'name' => 'UNIT TEST CREATE BUG PORTAL USER', 
                'portal_viewable' => '1', 
                'team_id' => '1'
            )),
            'POST'
        );
        
        $this->bugId = $bugReply['reply']['id'];
        
        // Create a note on the bug without an attachment
        $bugNoteReply = $this->_restCall(
            "Bugs/{$this->bugId}/link/notes",
            json_encode(array(
                'name' => 'UNIT TEST BUG NOTE PORTAL USER',
                'portal_flag' => '1'
            )),
            'POST'
        );
        
        $this->noteId = $bugNoteReply['reply']['related_record']['id'];
                
        // Create the attachment
        $post = array('filename' => '@' . $this->_testfile1);
        $restReply = $this->_restCall('Notes/' . $this->noteId . '/file/filename', $post);
        $this->assertArrayHasKey('filename', $restReply['reply'], 'Reply is missing file name key');
        $this->assertNotEmpty($restReply['reply']['filename']['name'], 'File name returned empty');

        // Now get the note to make sure it saved
        $fetch = $this->_restCall('Notes/' . $this->noteId);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->noteId, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertEquals($restReply['reply']['filename']['name'], $fetch['reply']['filename']);
        
        // Now edit this attachment
        $params = array('filename' => $this->_testfile2, 'type' => 'text/plain');
        $restReply = $this->_restCallFilePut('Notes/' . $this->noteId . '/file/filename', $params);
        // This should fail like a savage
        $this->assertArrayHasKey('error', $restReply['reply'], 'There is no error reply in the response');
        $this->assertEquals('not_authorized', $restReply['reply']['error'], 'Error returned is not not_authorized');
        
        // Lastly check the note again
        $fetch = $this->_restCall('Notes/' . $this->noteId);
        $this->assertNotEmpty($fetch['reply']['id'], 'Note id not returned');
        $this->assertEquals($this->noteId, $fetch['reply']['id'], 'Known note id and fetched note id do not match');
        $this->assertArrayHasKey('filename', $fetch['reply'], 'Filename field was not returned in Note fetch');
        $this->assertEquals($fetch['reply']['filename'], $this->_testfile1, 'Filename was changed when it should not have been');
    }
}