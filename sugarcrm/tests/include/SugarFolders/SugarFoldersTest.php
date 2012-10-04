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

require_once('include/SugarFolders/SugarFolders.php');


class SugarFoldersTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $folder = null;
	var $additionalFolders = null;
    var $_user = null;
    var $emails = null;


	public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->_user;
        $GLOBALS['current_user'] = $this->_user;
		$this->folder = new SugarFolder();
		$this->additionalFolders = array();
		$this->emails = array();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Emails");
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
        $this->_clearFolder($this->folder->id);

        foreach ($this->additionalFolders as $additionalID)
            $this->_clearFolder($additionalID);

        foreach ($this->emails as $emailID)
            $GLOBALS['db']->query("DELETE FROM emails WHERE id='$emailID'");

        unset($this->folder);
    }

    /**
     * Test the Set Folder method.
     *
     */
    function testSetFolder()
    {
        //Set folder
        $this->folder->id = create_guid();
        $this->folder->new_with_id = TRUE;

        $fields = array('name' => 'TEST_FOLDER','parent_folder' => 'PRNT_FOLDER',
                        //BEGIN SUGARCRM flav=pro ONLY
                        'team_id' => create_guid(),'team_set_id' => create_guid(),
                        //END SUGARCRM flav=pro ONLY
                        );

        $this->folder->setFolder($fields);

        //Retrieve newly created folder
        $error_message = "Unable to set folder.";
        $this->folder->retrieve($this->folder->id);

        $this->assertEquals($fields['name'], $this->folder->name, $error_message );
        $this->assertEquals($fields['parent_folder'], $this->folder->parent_folder, $error_message );
        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertEquals($fields['team_id'], $this->folder->team_id, $error_message );
        $this->assertEquals($fields['team_set_id'], $this->folder->team_set_id, $error_message );
        //END SUGARCRM flav=pro ONLY
        $this->assertEquals($this->_user->id, $this->folder->assign_to_id, $error_message );

        //Check for folder subscriptions create for global user
        $sub_ids = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(1, count($sub_ids), $error_message);
        $this->assertEquals($this->folder->id, $sub_ids[0], $error_message);

    }

    /**
     * Test sugar folder subscriptions: create, clear, insert, clear specific folder.
     *
     */
    function testFolderSubscriptions()
    {
        $this->_createNewSugarFolder();
        $error_message = "Unable to create|insert|delete sugar folder subscriptions.";

        //Clear subscriptions
        $this->folder->clearSubscriptions();
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(0, count($subs), $error_message);

        //Add a subscription
        $this->folder->insertFolderSubscription($this->folder->id,$GLOBALS['current_user']->id);
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(1, count($subs), $error_message);

        //Clear subscriptions for a paricular folder
        $this->folder->clearSubscriptionsForFolder($this->folder->id);
        $subs = $this->folder->getSubscriptions($GLOBALS['current_user']);
        $this->assertEquals(0, count($subs), $error_message);
    }

    /**
     * Test the getParentIDRecursive function which is used to find a grouping of folders.
     *
     */
    function testgetParentIDRecursive()
    {
        $f1 = new SugarFolder();
        $f12 = new SugarFolder();
        $f3 = new SugarFolder();

        $f1->id = create_guid();
        $f1->new_with_id = TRUE;

        $f12->id = create_guid();
        $f12->new_with_id = TRUE;

        $f3->id = create_guid();
        $f3->new_with_id = TRUE;

        $f12->parent_folder = $f1->id;
        $f1->save();
        $f12->save();
        $f3->save();

        $this->additionalFolders[] = $f1->id;
        $this->additionalFolders[] = $f12->id;
        $this->additionalFolders[] = $f3->id;


        $parentIDs = $this->folder->getParentIDRecursive($f12->id); //Includes itself in the return list.
        $this->assertEquals(2, count($parentIDs), "Unable to retrieve parent ids recursively");

        $parentIDs = $this->folder->getParentIDRecursive($f3->id); //Includes itself in the return list.
        $this->assertEquals(1, count($parentIDs), "Unable to retrieve parent ids recursively");

        //Find the children by going the other way.
        $childrenArray = array();
        $this->folder->findAllChildren($f1->id,$childrenArray);
        $this->assertEquals(1, count($childrenArray), "Unable to retrieve child ids recursively");

        $childrenArray = array();
        $this->folder->findAllChildren($f3->id,$childrenArray);
        $this->assertEquals(0, count($childrenArray), "Unable to retrieve child ids recursively");
    }

    /**
     * Test to ensure that for a new user, the My Email, My Drafts, Sent Email, etc. folders can be retrieved.
     *
     */
    function testGetUserFolders()
    {
        require_once('modules/Emails/EmailUI.php');
        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);
        $error_message = "Unable to get user folders";
        $rootNode = new ExtNode('','');

        $folderOpenState = "";
        $ret = $this->folder->getUserFolders($rootNode, $folderOpenState, $GLOBALS['current_user'], true);

        $this->assertEquals(1, count($ret), $error_message);
        $this->assertEquals($GLOBALS['mod_strings']['LNK_MY_INBOX'], $ret[0]['text'], $error_message);
        //Should contain 'My Drafts', 'My Sent Mail', 'My Archive'
        $this->assertEquals(3, count($ret[0]['children']), $error_message);

    }

    /**
     * Test the addBean, getCountUnread,getCountItems functions.
     *
     */
    function testAddBean()
    {
        $emailParams = array('status' => 'unread');
        $email = $this->_createEmailObject($emailParams);
        $this->emails[] = $email->id;

        $this->_createNewSugarFolder();

        $cnt = $this->folder->getCountUnread($this->folder->id);
        $this->assertEquals(0, $cnt, "Unable to execute addBean function properly.");

        $this->folder->addBean($email,$GLOBALS['current_user']);

        $cnt = $this->folder->getCountUnread($this->folder->id);
        $this->assertEquals(1, $cnt, "Unable to execute addBean function properly.");

        //Create a second email obj with status read
        $emailParams = array('status' => 'read');
        $email = $this->_createEmailObject($emailParams);
        $this->emails[] = $email->id;
        $this->folder->addBean($email,$GLOBALS['current_user']);

        $cnt = $this->folder->getCountItems($this->folder->id);
        $this->assertEquals(2, $cnt, "Unable to execute getCountItems function properly.");


    }

    /**
     * Tests sugar folder methods that deal with emails.
     *
     */
    function testFolderEmailMethods()
    {

        $emailParams = array('status' => 'read');
        $email = $this->_createEmailObject($emailParams);
        $this->emails[] = $email->id;

        $this->_createNewSugarFolder();
        $this->folder->addBean($email,$GLOBALS['current_user']);

        $emailExists = $this->folder->checkEmailExistForFolder($email->id);
        $this->assertTrue($emailExists, "Unable to check for emails with a specific folder");

        //Remove the specific email from our folder.

        $this->folder->deleteEmailFromFolder($email->id);
        $emailExists = $this->folder->checkEmailExistForFolder($email->id);
        $this->assertFalse($emailExists, "Unable to check for emails with a specific folder.");

        //Move the Email bean from one folder to another
        $f3 = new SugarFolder();
        $f3->id = create_guid();
        $f3->new_with_id = TRUE;
        $f3->save();
        $this->additionalFolders[] = $f3->id;

        $this->folder->addBean($email,$GLOBALS['current_user']);

        $emailExists = $f3->checkEmailExistForFolder($email->id);
        $this->assertFalse($emailExists);

        $this->folder->move($this->folder->id, $f3->id,$email->id);
        $emailExists = $f3->checkEmailExistForFolder($email->id);
        $this->assertTrue($emailExists, "Unable to move Emails bean to a different sugar folder");

    }

    /**
     * Test retreiving a list of emails for a particular folder.
     *
     */
    function testGetListItemsForEmailXML()
    {
        //Create the my Emails Folder
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Emails");
        require_once('modules/Emails/EmailUI.php');
        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);
        $error_message = "Unable to get list items for email.";
        $rootNode = new ExtNode('','');

        $folderOpenState = "";
        $ret = $this->folder->getUserFolders($rootNode, $folderOpenState, $GLOBALS['current_user'], true);

        $this->assertEquals(1, count($ret), $error_message);
        $folderID = $ret[0]['id'];

        //Create the Email Object
        $emailParams = array('status' => 'unread','assigned_user_id' => $GLOBALS['current_user']->id);
        $email = $this->_createEmailObject($emailParams);
        $this->emails[] = $email->id;

        //Add Email Object to My Email Folder
        $my_email = new SugarFolder();
        $my_email->retrieve($folderID);
        $my_email->addBean($email,$GLOBALS['current_user']);

        //Make sure the email was added to the folder.
        $emailExists = $my_email->checkEmailExistForFolder($email->id);
        $this->assertTrue($emailExists, $error_message);
        //Get the list of emails.
        $emailList = $my_email->getListItemsForEmailXML($folderID);

        $this->assertEquals($email->id,$emailList['out'][0]['uid'],$error_message );

    }


    function _createEmailObject($additionalParams = array() )
    {
        global $timedate;

        $em = new Email();
		$em->name = 'tst_' . uniqid();
		$em->type = 'inbound';
		$em->intent = 'pick';
		$em->date_sent = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", (gmmktime() + (3600 * 24 * 2) ))) ; //Two days from today

		foreach ($additionalParams as $k => $v)
		  $em->$k = $v;

		$em->save();

		return $em;
    }

    function _createNewSugarFolder()
    {
        $this->folder->id = create_guid();
        $this->folder->new_with_id = TRUE;
        $this->folder->name = "UNIT TEST";
        $this->folder->save();

    }

    private function _clearFolder($folder_id)
    {
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$folder_id}'");
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$folder_id}'");
    }
}
?>