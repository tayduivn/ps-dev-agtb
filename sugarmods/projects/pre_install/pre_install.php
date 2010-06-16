<?php

function checkProjectTaskDependency(){
	//BEGIN SUGARCRM flav=pro ONLY 
	$db = &PearDatabase::getInstance();
	
	$query = "SELECT PT1.id as id, PT1.name as project_task_name, P1.name AS project_name, P2.name AS external_project_name " . 
			 "FROM project_task PT1, project_task PT2, project P1, project P2 " . 
			 "WHERE PT1.depends_on_id = PT2.id AND PT2.parent_id <> PT1.parent_id AND P1.id=PT1.parent_id AND P2.id=PT2.parent_id AND PT1.deleted=0 AND PT2.deleted=0";
			 
	$result = $db->query($query, true, "Unable to retrieve number of tasks for project_id");
	$row = $db->fetchByAssoc($result);
	
	if ($row != null){
		$errors[] = "<b>External Project Dependency Failed</b> - ";
		$errors[] .= "There is at least one project task that depends on a project that it is not a part of. This is not supported in Advanced Project Management. Please check the list and fix it, and rerun the module loader again.";
		$errors[] .= "<hr />";
		while ($row != null){
			$errors[] .= "Project Task <b><a href='index.php?module=ProjectTask&action=DetailView&record=" . $row['id'] . "'>" . $row['project_task_name'] . "</a></b> is a task of Project <b>" . $row['project_name'] . "</b> but depends on a task from <b>" . $row['external_project_name'] . "</b>";		
			$row = $db->fetchByAssoc($result);
		}
		$errors[] .= "<br />";				
		return $errors;
	}	
	//END SUGARCRM flav=pro ONLY 
	return true;
}

function backupCustomizations(){
	$customized = false;
	$customizations = array( "custom/modules/Project", 
							 "custom/modules/ProjectTask",
							 "cache/studio/modules/Project",
							 "cache/studio/modules/ProjectTask", );
	
	foreach($customizations as $path){
		if (file_exists($path)){
			$customized = true;
			mkdir_recursive("$path-MI_backed", true);
			copy_recursive($path, "$path-MI_backed");
			rmdir_recursive($path);
			echo("Note: $path has been renamed as $path-MI_backed.<br />");		
		}			
	}
	
	if ($customized){
		echo("Please make a note of these backups in case you need to make similar customizations in the future.<br />");
	}
		
	return true;	
}

///////////////////////////////////////////////////////////////////////////////
////	BEGIN PRE INSTALL 

// BEGIN SUGARCRM flav=pro ONLY 
global $path;

// CHECK PROJECT TASK DEPENDENCIES
$GLOBALS['log']->debug("Checking for External Project Dependency", $path);
if (($errors = checkProjectTaskDependency()) !== true){
	$this->abort($errors);
}

// BACK UP PROJECT / PROJECT TASK CUSTOMIZATIONS
$GLOBALS['log']->debug("Backing up Project Customizations", $path);
backupCustomizations();

// END SUGARCRM flav=pro ONLY 

////	END PRE INSTALL
///////////////////////////////////////////////////////////////////////////////
?>