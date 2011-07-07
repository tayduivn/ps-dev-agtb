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

/**
 * @ticket 33404
 */
class Bug33404Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $folder = null;
    var $_user = null;
    
    
	public function setUp()
    {
        global $current_user, $currentModule;

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
		$this->folder = new SugarFolder(); 
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");
        
        unset($this->folder);
    }
    
	function testInsertFolderSubscription(){
	    global $current_user;
	   
	    $id1 = create_guid();
	    $id2 = create_guid();
	    
	    $this->folder->insertFolderSubscription($id1,$this->_user->id);
	    $this->folder->insertFolderSubscription($id2,$this->_user->id);
	    
	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where assigned_user_id='{$this->_user->id}'");
		$rs = $GLOBALS['db']->fetchByAssoc($result);
		
		$this->assertEquals(2, $rs['cnt'], "Could not insert folder subscriptions properly" );
    }
    
    
    
    function testClearSubscriptionsForFolder()
    {
        global $current_user;
	   
        $random_user_id1 = create_guid();
        $random_user_id2 = create_guid();
        $random_user_id3 = create_guid();
        
	    $folderID = create_guid();
	    
	    $this->folder->insertFolderSubscription($folderID,$random_user_id1);
        $this->folder->insertFolderSubscription($folderID,$random_user_id2);
        $this->folder->insertFolderSubscription($folderID,$random_user_id3);
	    
        $result1 = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$folderID}' ");
		$rs1 = $GLOBALS['db']->fetchByAssoc($result1);
        $this->assertEquals(3, $rs1['cnt'], "Could not clear folder subscriptions, test setup failed while inserting folder subscriptionss");
        
        //Test deletion of subscriptions.
        $this->folder->clearSubscriptionsForFolder($folderID);
	    $result = $GLOBALS['db']->query("SELECT count(*) as cnt FROM folders_subscriptions where folder_id='{$folderID}' ");
		$rs = $GLOBALS['db']->fetchByAssoc($result);
	 
		$this->assertEquals(0, $rs['cnt'], "Could not clear folder subscriptions");
    }
}
?>