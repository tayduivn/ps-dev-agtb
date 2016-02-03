<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/MVC/Controller/SugarController.php');
class TeamsController extends SugarController {

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
			$teamH = BeanFactory::getBean('TeamHierarchy');
			if(!empty($_POST['parent_id']))
				$teamH->parent_id = $_POST['parent_id'];
			$teamH->team_id = $_POST['team_id'];
			$teamH->save();
		}
		$this->view = 'tree';
	}


	public function action_AddTheUserToTeam(){
		if(!empty($_POST['user_id']) && !empty($_POST['user_parent_id'])){
			$teamH = BeanFactory::getBean('TeamHierarchy');
			$teamH->addUserToTeam($_POST['user_id'], $_POST['user_parent_id']);
		}
		$this->view = 'tree';
	}

	public function action_ReorderTree(){
		$this->view = 'ajax';
		if(!empty($_POST['node_id']) && !empty($_POST['parent_id'])){
			$teamH = BeanFactory::getBean('TeamHierarchy', $_POST['node_id']);
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
	    header("Content-Type: application/json");
	    echo $json->encode($retArray);
	}
    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * This method handles the saving team-based access configuration.
     */
    public function action_saveTBAConfiguration()
    {
        if ($GLOBALS['current_user']->isAdminForModule('Users')) {
            $enabled = !empty($_POST['enabled']) ? isTruthy($_POST['enabled']) : false;
            $disabledModules = !empty($_POST['disabled_modules']) ? explode(',', $_POST['disabled_modules']) : array();

            $tbaConfigurator = new TeamBasedACLConfigurator();
            $tbaConfigurator->setGlobal($enabled);

            if ($enabled) {
                $actionsList = array_keys(ACLAction::getUserActions($GLOBALS['current_user']->id));
                $enabledModules = array_values(array_diff($actionsList, $disabledModules));

                $tbaConfigurator->setForModulesList($disabledModules, false);
                $tbaConfigurator->setForModulesList($enabledModules, true);
            }
            echo json_encode(array('status' => true));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => $GLOBALS['app_strings']['EXCEPTION_NOT_AUTHORIZED']
            ));
        }
    }
    //END SUGARCRM flav=ent ONLY
}
