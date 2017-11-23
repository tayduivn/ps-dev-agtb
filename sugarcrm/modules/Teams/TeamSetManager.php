<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Doctrine\DBAL\DBALException;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class TeamSetManager {

	private static $instance;
	private static $_setHash = array();

	/**
	 * Constructor for TrackerManager.  Declared private for singleton pattern.
	 *
	 */
	private function __construct() {}

	/**
	 * getInstance
	 * Singleton method to return static instance of TrackerManager
	 * @returns static TrackerManager instance
	 */
	static function getInstance(){
	    if (!isset(self::$instance)) {
	        self::$instance = new TeamSetManager();
			//Set global variable for tracker monitor instances that are disabled
	        self::$instance->setup();
	    } // if
	    return self::$instance;
	}

	/**
	 * Add a team_set_id and module combination to the hash for later flushing to the db.
	 *
	 * @param $team_set_id - GUID of the team_set_id
	 * @param $module      - string
	 */
	public static function add($team_set_id, $table_name){
		if(empty(self::$_setHash[$team_set_id]) || empty(self::$_setHash[$team_set_id][$table_name])){
			self::$_setHash[$team_set_id][] = $table_name;
		}
	}

	/**
	 * Go through each of the team_sets_modules and find sets that are no longer in use
	 *
	 */
	public static function cleanUp(){
		$teamSetModule = BeanFactory::newBean('TeamSetModules');
		//maintain a list of the team set ids we would like to remove
		$setsToRemove = array();
		$setsToKeep = array();

        $conn = DBManagerFactory::getConnection();

        $query = 'SELECT team_set_id, module_table_name FROM team_sets_modules WHERE team_sets_modules.deleted = 0';
        $stmt = $conn->executeQuery($query);

        while (($tsmRow = $stmt->fetch())) {
			//pull off the team_set_id and module and run a query to see if we find if the module is still using this team_set
			//of course we have to be careful not to remove a set before we have gone through all of the modules containing that
			//set otherwise.
			$module_table_name = $tsmRow['module_table_name'];
			$team_set_id = $tsmRow['team_set_id'];
			//if we have a user_preferences table then we do not need to check the db.
			$pos = strpos($module_table_name, 'user_preferences');
			if ($pos !== false) {
				$tokens = explode('-', $module_table_name);
				if(count($tokens) >= 3){
					//we did find that this team_set was going to be removed from user_preferences
                    $query = 'SELECT contents FROM user_preferences WHERE category = ? AND deleted = 0';
                    $prefStmt = $conn->executeQuery($query, array($tokens[1]));

                    while (($userPrefRow = $prefStmt->fetch())) {
						$prefs = unserialize(base64_decode($userPrefRow['contents']));
						$team_set_id = SugarArray::staticGet($prefs, implode('.', array_slice($tokens, 2)));
						if(!empty($team_set_id)){
							//this is the team set id that is being used in user preferences we have to be sure to not remove it.
							$setsToKeep[$team_set_id] = true;
						}
					}//end while
				}//fi
			}else{
                $moduleRecordsExist = self::doesRecordWithTeamSetExist($module_table_name, $team_set_id);
                
                if ($moduleRecordsExist) {
                    $setsToKeep[$team_set_id] = true;
                } else {
                    $setsToRemove[$team_set_id] = true;
                }
			}
		}

		//compute the difference between the sets that have been designated to remain and those set to remove
		$arrayDiff = array_diff_key($setsToRemove, $setsToKeep);

		//now we have our list of team_set_ids we would like to remove, let's go ahead and do it and remember
		//to update the TeamSetModule table.
		foreach($arrayDiff as $team_set_id => $key){
            //1) remove from team_sets_teams
            $conn->delete('team_sets_teams', array(
                'team_set_id' => $team_set_id,
            ));

            //2) remove from team_sets
            $conn->delete('team_sets', array(
                'id' => $team_set_id,
            ));

            //3) remove from team_sets_modules
            $conn->delete($teamSetModule->table_name, array(
                'team_set_id' => $team_set_id,
            ));
		}
	}

	/**
	 * Save the data in the hash to the database using TeamSetModule object
	 *
	 */
	public static function save(){
		//if this entry is set in the config file, then store the set
		//and modules in the team_set_modules table
        if (!isset($GLOBALS['sugar_config']['enable_team_module_save'])
            || !empty($GLOBALS['sugar_config']['enable_team_module_save'])) {
			foreach(self::$_setHash as $team_set_id => $table_names){
				$teamSetModule = BeanFactory::newBean('TeamSetModules');
				$teamSetModule->team_set_id = $team_set_id;

				foreach($table_names as $table_name){
					$teamSetModule->module_table_name = $table_name;
					//remove the id so we do not think this is an update
					$teamSetModule->id = '';
					$teamSetModule->save();
				}
			}
		}
	}

    /**
     * Check if one or more records attached to a team still exist in the database
     *
     * @param string $moduleTableName Module table name
     * @param string $teamSetId       TeamSet id
     * @param string $beanId          Record to exclude from search
     * @return boolean
     */
    public static function doesRecordWithTeamSetExist($moduleTableName, $teamSetId, $beanId = null)
    {
		$whereStmt = 'team_set_id = ? AND deleted = 0';
		$params = array($teamSetId);
		if ($beanId) {
			$whereStmt .= ' AND id != ?';
			$params[] = $beanId;
		}
		$connection = DBManagerFActory::getConnection();
		$queryBuilder = $connection->createQueryBuilder();
		$queryBuilder->select('id')
				->from($moduleTableName)
				->where($whereStmt);
		// set the maximum number of records to be 1 to avoid scanning extra records in database
		$query = $queryBuilder->setMaxResults(1)->getSQL();
		$numRows = $connection->executeQuery($query, $params)->rowCount();
		return ($numRows == 1);
    }

    /**
     * Removes TeamSet module if no records exist
     *
     * @param SugarBean $focus
     * @param String    $teamSetid Team set to remove
     */
    public static function removeTeamSetModule($focus, $teamSetId)
    {
        if (empty($teamSetId)) {
            return;
        }
        
        if (self::doesRecordWithTeamSetExist($focus->table_name, $teamSetId, $focus->id)) {
            return;
        }

        $query = 'DELETE FROM team_sets_modules WHERE team_set_id = ? AND module_table_name = ?';
        DBManagerFactory::getConnection()
            ->executeQuery($query, array($teamSetId, $focus->table_name));
    }

	/**
	 * The above method "save" will flush the entire cache, saveTeamSetModule will just save one entry.
	 *
	 * @param guid $teamSetId	the GUID of the team set id we wish to save
	 * @param string $tableName	the corresponding table name
	 */
	public static function saveTeamSetModule($teamSetId, $tableName){
		//if this entry is set in the config file, then store the set
		//and modules in the team_set_modules table
        if (!isset($GLOBALS['sugar_config']['enable_team_module_save'])
            || !empty($GLOBALS['sugar_config']['enable_team_module_save'])) {
			$teamSetModule = BeanFactory::newBean('TeamSetModules');
			$teamSetModule->team_set_id = $teamSetId;
			$teamSetModule->module_table_name = $tableName;
			$teamSetModule->save();
		}
	}

	public static function getFormattedTeamNames($teams_arr=array()) {
		//Add a safety check (in the event that team_set_id is not set (maybe perhaps from manual SQL or failed unit tests)
		if(!is_array($teams_arr)) {
		   return array();
		}

		//now format the returned values relative to how the user has their locale
    	$teams = array();
	    foreach($teams_arr as $team){
	    	$display_name = Team::getDisplayName($team['name'], $team['name_2']);
            $teams[] = array(
                'id' => (string)$team['id'],
                'display_name' => $display_name,
                'name' => $team['name'],
                'name_2' => $team['name_2'],
            );
		}
		return $teams;
	}

	/**
     * Retrieve a list of team associated with a set ordered by name
	 *
	 * @param $team_set_id string
	 * @return array of teams array('id', 'name');
	 */
	public static function getUnformattedTeamsFromSet($team_set_id){
		if(empty($team_set_id)) return array();

        /** @var TeamSet $teamSet */
        $teamSet = BeanFactory::newBean('TeamSets');

        $teams = [];
        foreach ($teamSet->getTeams($team_set_id) as $team) {
            $teams[] = [
                'id' => $team->id,
                'name' => $team->name,
                'name_2' => $team->name_2,
            ];
        }

        return $teams;
	}

	/**
	 * Retrieve a list of team associated with a set for display purposes
	 *
	 * @param $team_set_id string
	 * @return array of teams array('id', 'name');
	 */
	public static function getTeamsFromSet($team_set_id){
		if(empty($team_set_id)) return array();
		return self::getFormattedTeamNames(self::getUnformattedTeamsFromSet($team_set_id));
	}

    /**
     * Return a formatted list of teams with badges.
     *
     * @param $focus
     * @param bool|false $forDisplay
     * @return mixed|string|void
     */
    public static function getFormattedTeamsFromSet($focus, $forDisplay = false)
    {
        $result = array();
        //BEGIN SUGARCRM flav=ent ONLY
        $isTBAEnabled = TeamBasedACLConfigurator::isEnabledForModule($focus->module_dir);
        //END SUGARCRM flav=ent ONLY

        $team_set_id = $focus->team_set_id ? $focus->team_set_id : $focus->team_id;
        $teams = self::getTeamsFromSet($team_set_id);

        //BEGIN SUGARCRM flav=ent ONLY
        $selectedTeamIds = array();
        if ($isTBAEnabled && !empty($focus->acl_team_set_id)) {
            $selectedTeamIds = array_map(function ($el) {
                return $el['id'];
            }, TeamSetManager::getTeamsFromSet($focus->acl_team_set_id));
        }
        //END SUGARCRM flav=ent ONLY

        foreach ($teams as $key => $row) {
            $isPrimaryTeam = false;
            $row['title'] = $forDisplay ?
                $row['display_name'] :
                (!empty($row['name']) ? $row['name'] : $row['name_2']);

            if (!empty($focus->team_id) && $row['id'] == $focus->team_id) {
                $row['badges']['primary'] = $isPrimaryTeam = true;
            }

            //BEGIN SUGARCRM flav=ent ONLY
            if ($isTBAEnabled && in_array($row['id'], $selectedTeamIds)) {
                $row['badges']['selected'] = $hasBadge = true;
            }
            //END SUGARCRM flav=ent ONLY

            if ($isPrimaryTeam) {
                array_unshift($result, $row);
            } else {
                array_push($result, $row);
            }
        }

        $detailView = new Sugar_Smarty();
        $detailView->assign('teams', $result);
        return $detailView->fetch('modules/Teams/tpls/DetailView.tpl');
    }

	/**
	 * Return a comma delimited list of teams for display purposes
	 *
     * @param string $team_set_id
     * @param string $primary_team_id
	 * @param boolean $for_display
	 * @return string
	 */
	public static function getCommaDelimitedTeams($team_set_id, $primary_team_id = '', $for_display = false){
        $team_set_id = $team_set_id?$team_set_id:$primary_team_id;
		$teams = self::getTeamsFromSet($team_set_id);
		$value = '';
	    $primary = '';
	   	foreach($teams as $row){
	        if(!empty($primary_team_id) && $row['id'] == $primary_team_id){
	        	  if($for_display){
	        	  	 $primary = ", {$row['display_name']}";
	        	  }else{
	        	  	$primary = ", ".(!empty($row['name']) ? $row['name'] : $row['name_2']);
	        	  }
	        }else{
	        	if($for_display){
	        		$value .= ", {$row['display_name']}";
	        	}else{
	   				$value .= ", ".(!empty($row['name']) ? $row['name'] : $row['name_2']);
	        	}
	        }
	   	}
	   	$value = $primary.$value;
	   	return substr($value, 2);
	}

	/**
	 * clear out the cache
	 *
	 */
	public static function flushBackendCache( ) {
    }

    /**
     * Given a particular team id, remove the team from all team sets that it belongs to
     *
     * @param string $team_id The team's id to remove from the team sets
     * @throws DBALException
     */
    public static function removeTeamFromSets($team_id)
    {
        $conn = DBManagerFactory::getConnection();

        /** @var TeamSet $teamSet */
        $teamSet = BeanFactory::newBean('TeamSets');
        $listener = Container::getInstance()->get(Listener::class);

        $query = 'SELECT team_set_id
FROM team_sets_teams
WHERE team_id = ?';
        $stmt1 = $conn->executeQuery($query, array($team_id));

        while (($teamSetId = $stmt1->fetchColumn())) {
            $teamSet->id = $teamSetId;
            $teamSet->removeTeamFromSet($team_id);

            // Now check if the new team_md5 value already exists.  If it does, we have to go and
            // update all the records that to use an existing team_set_id and get rid of this team set since
            // it is essentially a duplicate
            $query = 'SELECT id FROM team_sets WHERE team_md5 = ? AND id != ?';
            $stmt = $conn->executeQuery($query, array($teamSet->team_md5, $teamSet->id));

            if (!($existing_team_set_id = $stmt->fetchColumn())) {
                continue;
            }

            $query = <<<SQL
SELECT module_table_name
 FROM team_sets_modules
 WHERE team_set_id = ?
SQL;
            $stmt2 = $conn->executeQuery($query, [$teamSetId]);

            //Update the records
            while (($table = $stmt2->fetchColumn())) {
                $conn->update($table, array(
                    'team_set_id' => $existing_team_set_id,
                ), array(
                    'team_set_id' => $teamSet->id,
                ));
            }

            //Remove the team set entry
            $conn->delete('team_sets', array(
                'id' => $teamSetId,
            ));

            //Remove the team_sets_teams entries
            $conn->delete('team_sets_teams', array(
                'team_set_id' => $teamSetId,
            ));

            //Remove the team_sets_modules entries
            $conn->delete('team_sets_modules', array(
                'team_set_id' => $teamSetId,
            ));

            $listener->teamSetDeleted($teamSetId);
        }
    }
}
