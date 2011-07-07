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
require_once('modules/InboundEmail/InboundEmail.php');

/**
 * @ticket 33404
 */
class AutoCreateImportFolderTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $folder_id = null;
	var $folder_obj = null;
	var $ie = null;
    var $_user = null;
    
    
	public function setUp()
    {
        global $current_user, $currentModule;

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        
		$this->folder = new SugarFolder(); 
		$this->ie = new InboundEmail();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$this->folder_id}'");
        
        unset($this->ie);
    }
    
	function testAutoImportFolderCreation(){
	    global $current_user;
	   
    	$this->ie->name = "Sugar Test";
    	//BEGIN SUGARCRM flav=pro ONLY
    	$this->ie->team_id = create_guid();
    	$this->ie->team_set_id = create_guid();
    	//END SUGARCRM flav=pro ONLY
    	$this->folder_id = $this->ie->createAutoImportSugarFolder();
	    $this->folder_obj = new SugarFolder();
	    $this->folder_obj->retrieve($this->folder_id);
		
		$this->assertEquals($this->ie->name, $this->folder_obj->name, "Could not create folder for Inbound Email auto folder creation" );
        //BEGIN SUGARCRM flav=pro ONLY
    	$this->assertEquals($this->ie->team_id, $this->folder_obj->team_id, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals($this->ie->team_set_id, $this->folder_obj->team_set_id, "Could not create folder for Inbound Email auto folder creation" );
        //END SUGARCRM flav=pro ONLY
    	$this->assertEquals(0, $this->folder_obj->has_child, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals(1, $this->folder_obj->is_group, "Could not create folder for Inbound Email auto folder creation" );
        $this->assertEquals($this->_user->id, $this->folder_obj->assign_to_id, "Could not create folder for Inbound Email auto folder creation" );
        
	}
}
?>