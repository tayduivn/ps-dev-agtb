<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 //Request object must have these property values:
 //		Module: module name, this module should have a file called TreeData.php
 //		Function: name of the function to be called in TreeData.php, the function will be called statically.
 //		PARAM prefixed properties: array of these property/values will be passed to the function as parameter.

require_once('include/JSON.php');
require_once('include/entryPoint.php');
require_once('include/upload_file.php');
require_once('vendor/ytree/Tree.php');
require_once('vendor/ytree/Node.php');
require_once('modules/UpgradeWizard/dbSchema_utils.php');

$json = getJSONobj();

$GLOBALS['log']->fatal('************ relation **************************$$$$$$ ');
$tableAndRelation = $json->decode(html_entity_decode($_REQUEST['tableAndRelation']));
 if(isset($tableAndRelation['jsonObject']) && $tableAndRelation['jsonObject'] != null){
	$tableAndRelation = $tableAndRelation['jsonObject'];
  }
//$selectedRelationship = $json->decode(html_entity_decode($_REQUEST['selectedRelationship']));

$GLOBALS['log']->fatal('************ relation ********* '.$tableAndRelation[1]);
$GLOBALS['log']->fatal('***table**** '.$tableAndRelation[0]);

$table_name = $tableAndRelation[0];
$relationship_name = $tableAndRelation[1];
$check_relation = $relationship_name;
$relsDrop = '';
$relsDrop = traceTableRelations($table_name,$check_relation);

foreach($relsDrop as $rel_name=>$rel){
	if($rel_name == $relationship_name){
		$relsDrop = $relationship_name;
		break;
	}
}
//$GLOBALS['log']->fatal('***RELATION LATER**** '.$relsDrop);
/*
if(trim($relationship_name) == trim($table_name).'_Relationships'){
	$check_relation = 'All';
	$relsDrop = traceTableRelations($table_name,$check_relation);
}
else{
	$relsDrop = $relationship_name;
}
*/
function traceTableRelations($table_name,$check_relation){
	include ('include/modules.php') ;


	global $current_user, $beanFiles;
	global $dictionary;

	//clear cache before proceeding..
	VardefManager::clearVardef () ;
    $relsDrop = '';
	// loop through all of the modules and create entries in the Relationships table (the relationships metadata) for every standard relationship, that is, relationships defined in the /modules/<module>/vardefs.php
	// SugarBean::createRelationshipMeta just takes the relationship definition in a file and inserts it as is into the Relationships table
	// It does not override or recreate existing relationships
	$table_found = false;
	foreach ( $beanFiles as $bean => $file )
	{
	    if (strlen ( $file ) > 0 && file_exists ( $file ))
	    {
	        if (! class_exists ( $bean ))
	        {
	            require ($file) ;
	        }
	        $focus = BeanFactory::newBeanByName($bean);
	        $empty = '' ;
	        if(trim(strtolower($table_name)) == trim(strtolower($focus->table_name))) {
	       		$table_found = true;
	       		$relsDrop = traceRelations( $focus->getObjectName (),$table_name, $empty, $focus->module_dir);
	       		break;
	        }
	    }
	}
	if(!$table_found){
	    $dictionary = array ( ) ;
	    foreach(SugarAutoLoader::existing('modules/TableDictionary.php',
	        'custom/application/Ext/TableDictionary/tabledictionary.ext.php') as $file) {
	        include $file;
	    }
	    $rel_dictionary = $dictionary ;
	    foreach ( $rel_dictionary as $rel_name => $rel_data )
	    {
	        if($table_name == $rel_data [ 'table' ]){
	        	$relsDrop = traceRelations( $rel_name, $table_name, $rel_dictionary, '');
	        	break;
	        }
	    }
	}
	return $relsDrop;
}
function traceRelations($key,$tablename,$dictionary,$module_dir)
{
	$table_relationships = array();
	//load the module dictionary if not supplied.
	if (empty($dictionary) && !empty($module_dir))
	{
		if ($key == 'User')
		{
			// a very special case for the Employees module
			// this must be done because the Employees/vardefs.php does an include_once on
			// Users/vardefs.php
			$filename='modules/Users/vardefs.php';
		}
		else
		{
			$filename='modules/'. $module_dir . '/vardefs.php';
		}

		if(file_exists($filename))
		{
			include($filename);
			if(empty($dictionary) || !empty($GLOBALS['dictionary'][$key]))
			{
				$dictionary = $GLOBALS['dictionary'];
			}
		}
		else
		{
			//$GLOBALS['log']->debug("createRelationshipMeta: no metadata file found" . $filename);
			return;
		}
	}

	if (!is_array($dictionary) or !array_key_exists($key, $dictionary))
	{
		//$GLOBALS['log']->fatal("createRelationshipMeta: Metadata for table ".$tablename. " does not exist");
		//display_notice("meta data absent for table ".$tablename." keyed to $key ");
	}
	else
	{
		if (isset($dictionary[$key]['relationships']))
		{
			$RelationshipDefs = $dictionary[$key]['relationships'];
			foreach ($RelationshipDefs as $rel_name=>$rel_def)
			{
				//$GLOBALS['log']->fatal('************TABLE RELS***** '.' '.$rel_name);
				//check whether relationship exists or not first.
				$table_relationships[$rel_name]= $rel_name;
			}
		}
		else
		{
			//todo
			//log informational message stating no relationships meta was set for this bean.
		}
	}
    return $table_relationships;
}

if(function_exists('checkSchema')){
	$GLOBALS['log']->fatal('************RELs TABLE ***** '.function_exists('checkSchema').' '.$relsDrop);
}
$checkThisRel = $relsDrop;
$relScan = checkSchema(false,false,$checkThisRel);
$relScan .="\n";
$relScan .=checkTableComments($table_name);
$relScan .="\n";
$relScan .=checkTableComments($table_name);

$GLOBALS['log']->fatal('************REL SCAN ***** '.$relScan);
$response=$relScan;

if (!empty($response)) {
	echo $response;
}
sugar_cleanup();
exit();
