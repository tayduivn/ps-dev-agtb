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
 
class SugarFieldTeamsetTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $_user = null;    
	var $_contact = null;
    var $_sessionId = null;
    var $_teamSetId = null;
    var $_contactId = null;

	public function setUp() 
	{
		$this->markTestIncomplete(
            'Need to ensure proper cleanup first.'
        );
        global $beanList, $beanFiles, $moduleList, $sugar_config;
        require('include/modules.php');		
		
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['current_user'] = $this->_user;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->team_id = '1'; //Set primary team to Global
        $this->_contact->contacts_users_id = $this->_user->id;
        $this->_contactId = $this->_contact->save();   

        global $current_language, $app_strings;
		$app_strings = return_application_language($current_language);
    }    
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->_user = null;
        
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;   
    }     
    
    /**
     * test_massupdate_replace_team
     * This teast adds three teams to the contact record and then attempts to replace
     * the teams with just the West team
     * 
     */
    public function testReplaceTeamsWithAnotherTeamFromMassupdate() 
    {
    	$contact = new Contact();
    	$contact = $contact->retrieve($this->_contactId);
	    $teams = array('East', 'West');
		$contact->load_relationship('teams');
		$contact->teams->add($teams);
		$contact->teams->save();
		
		require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');
		require_once('include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');
		$sugar_field = new SugarFieldTeamset('Teamset');
		
		//Seed the $_POST variable to simulate removing the West team
		$_POST['team_name_new_on_update'] = false;
		$_POST['team_name_allow_update'] = '';
		$_POST['team_name_allow_new'] = true;
		$_POST['team_name_collection_0'] = 'West';
		$_POST['id_team_name_collection_0'] = 'West';
		$_POST['team_name_type'] = 'replace';
		
		//Seed the $_REQUEST variable for the getTeamsFromRequest method in SugarFieldTeamset.php
		$_REQUEST['team_name_collection_0'] = 'West';
		$_REQUEST['id_team_name_collection_0'] = 'West';	
		
		$sugar_field->save($contact, $_POST, 'team_name', array());
		$contact->teams->save();
		
		$teamIds = array();
		$result = $GLOBALS['db']->query("SELECT team_id FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");
		while($row = $GLOBALS['db']->fetchByAssoc($result))
		      $teamIds[] = $row['team_id'];
		
    	$this->assertFalse(in_array('East',$teamIds), "Assert that East team has been removed");
    	//$this->assertEquals("West", $contact->team_id, "Assert that primary team is Global");          	
    }
    
    public function testAddingWestTeamViaMassupdate() 
    {
    	$contact = new Contact();
    	$contact = $contact->retrieve($this->_contactId);

		require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');
		require_once('include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');
		$sugar_field = new SugarFieldTeamset('Teamset');
		
		//Seed the $_POST variable to simulate removing the Global team
		$_POST['team_name_new_on_update'] = false;
		$_POST['team_name_allow_update'] = '';
		$_POST['team_name_allow_new'] = true;
		$_POST['team_name_collection_0'] = 'West';
		$_POST['id_team_name_collection_0'] = 'West';
		$_POST['team_name_type'] = 'add';
		
		//Seed the $_REQUEST variable for the getTeamsFromRequest method in SugarFieldTeamset.php
		$_REQUEST['team_name_collection_0'] = 'West';
		$_REQUEST['id_team_name_collection_0'] = 'West';	
		
		$contact->load_relationships('teams');
		$sugar_field->save($contact, $_POST, 'team_name', array());
		$contact->teams->save();
		
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($row['total'], 2, "Assert that the West team was added to the team sets for the contact"); //West, user's private team and global team       	
    }
}

?>
