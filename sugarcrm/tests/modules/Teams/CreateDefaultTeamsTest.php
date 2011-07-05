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
require_once('modules/Contacts/ContactFormBase.php');

class CreateDefaultTeamsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_user = null;
    private $_contact = null;
    
    public function setUp() 
    {
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user'] = $this->_user;
		$GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Collin' AND last_name = 'Lee'");
    }    
    
    public function tearDown() 
    {
        unset($GLOBALS['current_user']);
     
        if ( $this->_contact instanceOf Contact && !empty($this->_contact->id) )
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->_contact->id}'");
        
        $this->_contact = null;
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }     
    
    public function testCreateDefaultTeamsForNewContact() 
    {
    	$_POST['first_name'] = 'Collin';
		$_POST['last_name'] = 'Lee';
		$_POST['action'] = 'Save';
        $_REQUEST['action'] = 'Save';
		
        $query = "select t.id, t.name, t.name_2 from teams t where t.name in ('Global', 'East', 'West')";

        $result = $GLOBALS['db']->query($query);
        $count = 0;
        $primary_team_id = '';
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {   
 	   		if (empty($primary_team_id)) {
 	   		   $primary_team_id = $row['id'];
 	   		}
			$_POST['team_name_collection_' . $count] = $row['name'] . ' ' . $row['name_2'];
			$_POST['id_team_name_collection_' . $count] = $row['id'];
			$count++;              
	   	}
        
	   	$_POST['primary_team_name_collection'] = 0;
		
        $contactForm = new ContactFormBase();
        $this->_contact = $contactForm->handleSave('', false, false);
        $this->assertEquals($this->_contact->team_id,$primary_team_id,
            "Contact's primary team equals the current user's primary team");
    }
    
    /**
     * @dataProvider providerTeamName
     */
    public function testGetCorrectTeamName($team, $expected){
    	$this->assertEquals($team->get_summary_text(),$expected,
            "{$expected} team name did not match");
    }
    
	public function providerTeamName(){
		$team1 = new Team();
    	$team1->name = 'Will';
    	$team1->name_2 = 'Westin';
    	
    	$team2 = new Team();
    	$team2->name = 'Will';
 		
        return array(
            array($team1,'Will Westin'),
            array($team2,'Will'),
        );
    }
}