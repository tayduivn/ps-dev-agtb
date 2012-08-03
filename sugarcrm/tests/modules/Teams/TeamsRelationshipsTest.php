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
 
require_once('modules/Teams/Team.php');
require_once('modules/Teams/TeamSet.php');
require_once('include/nusoap/nusoap.php');
require_once('SugarTestUserUtilities.php');
require_once('tests/SugarTestContactUtilities.php');

/**
 * Test cases for the Team object
 */
class TeamsRelationshipsTest extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;    
	public $_contact = null;
    public $_soapClient = null;
    public $_sessionId = null;
    public $_teamSetId = null;
    public $_contactId = null;
   
	public function setUp() 
    {
    	$this->markTestIncomplete(
              'fixing this test'
            );
		require('include/modules.php');
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
       	$this->_user->save();
        $this->_user->setPreference('timezone', 'Africa/Douala', 0, 'global');
        $GLOBALS['current_user'] = $this->_user;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->contacts_users_id = $this->_user->id;
        $this->_contactId = $this->_contact->save();
        
        // clear module cache prior to this test
        //if ( is_dir(dirname(__FILE__).'/../../../cache/modules/Teams/') )
        //    rmdir_recursive(dirname(__FILE__).'/../../../cache/modules/Teams/');
    }    
    
    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $this->_user = null;
        
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;   
    }     
    
    public function testSoapRelationshipCalls() {
    	$soap_url = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        $this->_soapClient = new nusoapclient($soap_url);      
    	
        $result = $this->_soapClient->call('login',
            array('user_auth' => 
                array('user_name' => $this->_user->user_name,
                      'password' => $this->_user->user_hash,
                      'timezone' => 'Africa/Douala',
                      'version' => '.01'), 
                      'application_name' => 'SoapTest')
            );
            
        $this->_sessionId = $result['id'];    
		$result = $this->_soapClient->call('set_relationship',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'module_id'=>$this->_contactId, 'link_field_name' => 'teams', 'related_ids' => array('1', 'East', 'West', $this->_contact->team_id)));
		$this->assertEquals($result['created'],1,'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response); 
		
		$contact = new Contact();
		$contact = $contact->retrieve($this->_contactId);
		
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	
		$this->assertEquals($row['total'], 4); 
		
	    $result = $this->_soapClient->call('get_relationships',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'module_id'=>$this->_contactId, 'link_field_name'=>'teams', 'related_module_query'=>'', 'related_fields' => array('name', 'id'), 'related_module_link_name_to_fields_array' => '' ));
	    $this->assertTrue(!empty($result),'Results not returned. HTTP Response: '.$this->_soapClient->response); 
	    $this->assertEquals(count($result['entry_list']), 4);
    }
   
    public function testRegularRelationshipCalls() {
    	$contact = new Contact();
    	$contact = $contact->retrieve($this->_contactId);
	    $teams = array('East', 'West', '1');
		$contact->load_relationship('teams');
		$contact->teams->add($teams);
    	$contact->load_relationship('teams');
    	$contact->teams->add(array('1', 'East', 'West', $contact->team_id));
    	
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($row['total'], 4, "Total number of teams in contact record is 4");
		
		$teams = array('East', 'West');
		$contact->load_relationship('teams');
		$contact->teams->remove($teams);
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertEquals($row['total'], 2, "Total number of teams in contact record is 1 after removing East and West teams");

    	$teams = array('East', 'West');
    	$contact->load_relationship('teams');
    	$contact->teams->replace($teams);
		$result = $GLOBALS['db']->query("SELECT count(team_id) as total FROM team_sets_teams WHERE team_set_id = '{$contact->team_set_id}'");     
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertEquals($row['total'], 2, "Total number of teams in contact record is 2 after replacing with East and West teams");
    }
   
    public function testDeleteRelationshipCall(){
    	$contact = new Contact();
    	$contact = $contact->retrieve($this->_contactId);
    	$contact->load_relationship('teams');
		$contact->teams->replace(array('East', 'West', $contact->team_id));
    	$contact->load_relationship('teams');
    	$contact->teams->delete($contact->id, 'West');
    	$contact->retrieve($contact->id);
    	
    	$teamSet = new TeamSet();
    	$team_ids = $teamSet->getTeamIds($contact->team_set_id);

    	$this->assertNotContains('West', $team_ids, 'The list of associated teams still contains West.');
    	//TODO come back to fix this test
    	//$this->assertContains('East', $team_ids, 'The list of associated teams does not contain East.');
    }
    
   
}

?>