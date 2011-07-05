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
require_once('modules/Emails/Email.php');
require_once('include/TimeDate.php');
/**
 * This class is meant to test everything for InboundEmail
 *
 */
class GroupFoldersTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $_user = null;
	
    /**
     * Create test user
     *
     */
	public function setUp() 
	{
    	global $groupfolder_id;
    	if (empty($groupfolder_id)) {
        	$this->_setupTestUser();
    	} // IF
    	$GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Emails");
    }
    
    public function tearDown()
    {
        unset($GLOBALS['mod_strings']);
    }

	/**
	 * Create a group folder
	 *
	 */
    public function testCreateGroupFolder() 
    { 
		global $current_user, $groupfolder_id, $teamObject;
		$sugarFolder = new SugarFolder();
		$sugarFolder->name = "UnitTestGroupFolder";
		$sugarFolder->parent_folder = "";
		$sugarFolder->has_child = 0;
		$sugarFolder->is_group = 1;
		$sugarFolder->assign_to_id = $current_user->id;
		$teamObject = SugarTestTeamUtilities::createAnonymousTeam();
		$sugarFolder->team_id = $teamObject->id;
		$sugarFolder->team_set_id = $teamObject->id;
		$status = $sugarFolder->save();
		$groupfolder_id = $sugarFolder->id;
    	$this->assertTrue($status,"group Folder can not be created");
    } // fn
    
    /**
     * Create an Email
     *
     */
    public function testCreateEmail() 
    {
    	global $current_user, $groupfolder_id, $teamObject, $email_id;
    	$email = new Email();
    	$email->name = "Unittest";
    	$email->description = "Unittest";
    	$email->description_html = "<b>Unittest</b>";
    	$email->from_addr_name = "Unittest";
    	$email->to_addrs_names = "Unittestto";
    	$email->cc_addrs_names = "Unittestcc";
    	$email->bcc_addrs_names = "Unittestbcc";
    	$email->reply_to_addr = "Unittest@ubnittest.com";
    	$email->from_addr = "ajaysales@sugarcrm.com";
    	$email->to_addrs = "Unittest@unittest.com";
    	$email->cc_addrs = "Unittest@unittest.com";
    	$email->bcc_addrs = "Unittest@unittest.com";
    	$email->message_id = md5('Unittest - ' . mt_rand());
		$email->team_id = $teamObject->id;
		$email->team_set_id = $teamObject->id;
		$email->assigned_user_id = $current_user->id;
		$email->save();
		$email_id = $email->id;
        $this->assertTrue(!empty($email_id), "testAssignEmailToGroupFolder failed");
    }
    
    /**
     * Assign this email to group folder
     *
     */
    public function testAssignEmailToGroupFolder() 
    {
    	global $current_user, $groupfolder_id, $teamObject, $email_id;
    	$email = new Email();
    	$email->retrieve($email_id);
		$toSugarFolder = new SugarFolder();
		$toSugarFolder->retrieve($groupfolder_id);
		$status = $toSugarFolder->addBean($email);
    	$this->assertTrue($status,"testAssignEmailToGroupFolder failed");
    }
    
    /**
     * Retrieve Email for this folder
     *
     */
    public function retrieveEmailForGroupFolder() 
    {
    	global $current_user, $groupfolder_id, $teamObject, $email_id;
		$toSugarFolder = new SugarFolder();
		$result = $toSugarFolder->getListItemsForEmailXML($groupfolder_id);
    	$this->assertTrue(($result['out'].length == 1),"retrieveEmailForGroupFolder failed");
    }
    
    /**
     * Delete this email
     *
     */
    public function testDeleteEmailForGroupFolder() 
    {
    	global $current_user, $groupfolder_id, $teamObject, $email_id;
    	$email = new Email();
    	$email->delete($email_id);
    }
	
    /**
	 * Delete a folder
	 *
	 */
    public function testDeleteGroupFolder() 
    {
    	global $groupfolder_id;
		$focus = $this->_retrieveGroupFolder();
		$status = $focus->delete();
    	if ($status) {
    		$this->_tearDownGroupFolder();
        	$this->_tearDownTestUser();
        	unset($groupfolder_id);
    	}
    	$this->assertTrue($status,"UnitTestGroupFolder can not be deleted");
    }
    
	/**
	 * retrieve a group folder
	 *
	 */
    protected function _retrieveGroupFolder() 
    {
    	global $groupfolder_id;
		$focus = new SugarFolder();
		$focus->retrieve($groupfolder_id);
		return $focus;
    } // fn
        
    /**
	 * Delete this inbound account.
	 *
	 */
    protected function _tearDownGroupFolder() 
    {
    	global $groupfolder_id;
		$GLOBALS['db']->query("delete from folders WHERE id = '{$groupfolder_id}'");
    }
    
    /**
     * Create a test user
     *
     */
	protected function _setupTestUser() 
	{
		global $current_user;
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $current_user = $this->_user;
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
    }
        
    /**
     * Remove user created for test
     *
     */
	protected function _tearDownTestUser() 
	{
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
       unset($GLOBALS['current_user']);
    }
}
