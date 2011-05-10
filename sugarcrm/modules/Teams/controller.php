<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/MVC/Controller/SugarController.php');
//BEGIN SUGARCRM flav=int ONLY

//END SUGARCRM flav=int ONLY
class TeamsController extends SugarController {

	function TeamsController(){
		parent::SugarController();
	}
	
	//BEGIN SUGARCRM flav=int ONLY
	public function action_GetTeamHierarchy(){
		$this->view = 'ajax';
		if(!empty($_REQUEST['node']) && $_REQUEST['node'] != 'ynode-7'){
			$parent_id = $_REQUEST['node'];
			$sql = "SELECT team_hierarchies.id, teams.name FROM team_hierarchies INNER JOIN teams ON teams.id = team_hierarchies.team_id WHERE parent_id = '$parent_id'";
			
		}else{
			//return the whole tree with top level nodes only
			//echo '[{"text":"build","id":"123456","cls":"folder"},{"text":"INCLUDE_ORDER.txt","id":"\/INCLUDE_ORDER.txt","leaf":true,"cls":"file"},{"text":"ext-core.js","id":"\/ext-core.js","leaf":true,"cls":"file"},{"text":"source","id":"\/source","cls":"folder"},{"text":"adapter","id":"\/adapter","cls":"folder"},{"text":"examples","id":"\/examples","cls":"folder"},{"text":"docs","id":"\/docs","cls":"folder"},{"text":"ext-all.js","id":"\/ext-all.js","leaf":true,"cls":"file"},{"text":"license.txt","id":"\/license.txt","leaf":true,"cls":"file"},{"text":"ext-core-debug.js","id":"\/ext-core-debug.js","leaf":true,"cls":"file"},{"text":"ext-all-debug.js","id":"\/ext-all-debug.js","leaf":true,"cls":"file"},{"text":"resources","id":"\/resources","cls":"folder"},{"text":"CHANGES.html","id":"\/CHANGES.html","leaf":true,"cls":"file"}]';
			$sql = "SELECT team_hierarchies.id, teams.name FROM team_hierarchies INNER JOIN teams ON teams.id = team_hierarchies.team_id WHERE (parent_id is NULL OR parent_id = '')";
		}
		$result = $GLOBALS['db']->query($sql);
		$nodes = array();
	   	while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
	   			$node = array();
	   			$node['id'] = $row['id'];
	   			$node['text'] = $row['name'];
	   			$node['cls'] = 'file';
	   			$sql2 = "SELECT count(*) count FROM team_hierarchies WHERE parent_id = '".$row['id']."'";
	   			$result2 = $GLOBALS['db']->query($sql2);
	   			$row2 = $GLOBALS['db']->fetchByAssoc($result2);
	   			if($row2['count'] <= 0){
	   				$node['leaf'] = 'true';
	   			}
	   			$nodes[] = $node;
	   	}
	   	$json = getJSONobj();
		echo $json->encode($nodes);
	}
	
	public function action_AddTeamToHierarchy(){
		if(!empty($_POST['team_id'])){
			$teamH = new TeamHierarchy();
			if(!empty($_POST['parent_id']))
				$teamH->parent_id = $_POST['parent_id'];
			$teamH->team_id = $_POST['team_id'];
			$teamH->save();
		}
		$this->view = 'tree';
	}
	

	public function action_AddTheUserToTeam(){
		if(!empty($_POST['user_id']) && !empty($_POST['user_parent_id'])){
			//$focus = new Team();
			//$focus->retrieve($_POST['user_team_id']);
			//$focus->add_user_to_team($_POST['user_id']);
			$teamH = new TeamHierarchy();
			$teamH->addUserToTeam($_POST['user_id'], $_POST['user_parent_id']);
		}
		$this->view = 'tree';
	}
	
	public function action_ReorderTree(){
		$this->view = 'ajax';
		if(!empty($_POST['node_id']) && !empty($_POST['parent_id'])){
			$teamH = new TeamHierarchy();
			$teamH->retrieve($_POST['node_id']);
			$teamH->parent_id = $_POST['parent_id'];
			$teamH->save();
			return 'success';
		}
		return 'failure';
	}
	//END SUGARCRM flav=int ONLY
	
	public function action_DisplayInlineTeams(){
		$this->view = 'ajax';
		$body = '';
		$primary_team_id = isset($_REQUEST['team_id']) ? $_REQUEST['team_id'] : '';
		$caption = '';
		if(!empty($_REQUEST['team_set_id'])){
			require_once('modules/Teams/TeamSetManager.php');
			$teams = TeamSetManager::getTeamsFromSet($_REQUEST['team_set_id']);
			
			foreach($teams as $row){
				if($row['id'] == $primary_team_id) {
				   $body = $row['display_name'] . '*<br/>' . $body;	
				} else {
				   $body .= $row['display_name'].'<br/>';
				}
			}
		}
		global $theme;
		$json = getJSONobj();
		$retArray = array();
		
		$retArray['body'] = $body;
		$retArray['caption'] = $caption;
	    $retArray['width'] = '100';             
	    $retArray['theme'] = $theme;
	    echo 'result = ' . $json->encode($retArray);
	}
}
?>