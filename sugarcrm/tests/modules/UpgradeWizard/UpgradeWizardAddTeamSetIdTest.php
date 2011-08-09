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
class UpgradeWizardAddTeamSetIdTest extends Sugar_PHPUnit_Framework_TestCase  {

var $skipTest = true;
var $module = 'Contacts'; //Just do this for Contacts module for now
var $team_set_ids = array();
var $team_ids = array();

function setUp() 

{
	
    if($this->skipTest) {
       $this->markTestSkipped("Skipping unless otherwise specified");
    }	
	
	$this->team_set_ids = array();
	$this->team_ids = array();	
	
	$result = $GLOBALS['db']->query("SELECT id, team_set_id from {$this->module}");
	while($row = $GLOBALS['db']->fetchByAssoc($result)) {
		  $this->team_set_ids[$row['id']] = $row['team_set_id'];
	}
	
	//$GLOBALS['db']->query("UPDATE {$this->module} SET team_set_id = NULL");
	
	//Delete the teams_sets and team_sets_teams entry with only one team
    $result = $GLOBALS['db']->query("SELECT id FROM teams");
	while($row = $GLOBALS['db']->fetchByAssoc($result)) {
		  $this->team_ids[$row['id']] = $row['id'];
	}

	foreach($this->team_ids as $id) {
	      $GLOBALS['db']->query("DELETE FROM team_sets_teams WHERE team_set_id = '{$id}'");
	      $GLOBALS['db']->query("DELETE FROM team_sets WHERE id = '{$id}'");
	}
	
	$bean = loadBean($this->module);
	$GLOBALS['db']->deleteColumn($bean, $bean->field_defs['team_set_id']);
}

function tearDown() {
	foreach($this->team_set_ids as $id=>$team_set_id) {
		    $GLOBALS['db']->query("UPDATE {$this->module} SET team_set_id = '{$team_set_id}' WHERE id = '{$id}'");
	}
}

function test_add_teamsetid() {		
	$result = $GLOBALS['db']->query("SELECT count(team_id) as total from {$this->module}");
    $row = $GLOBALS['db']->fetchByAssoc($result);
    $contact_total = $row['total']; 
    
    $FieldArray = $GLOBALS['db']->helper->get_columns($this->module);
    $this->assertTrue(!isset($FieldArray['team_set_id']), "Assert that team_set_id column was removed");
    
	require_once('modules/UpgradeWizard/uw_utils.php');	
	$filter = array($this->module);	
	upgradeModulesForTeamsets($filter);
		
    $result = $GLOBALS['db']->query("SELECT count(team_id) as total from {$this->module} where team_id = team_set_id");
    $row = $GLOBALS['db']->fetchByAssoc($result);
    $contact_total2 = $row['total'];
    $this->assertTrue($contact_total == $contact_total2); 

    $FieldArray = $GLOBALS['db']->helper->get_columns($this->module);
    $this->assertTrue(isset($FieldArray['team_set_id']), "Assert that team_set_id column was created");
}


}

?>