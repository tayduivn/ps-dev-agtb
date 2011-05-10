<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
 //Request object must have these property values:
 //		Module: module name, this module should have a file called TreeData.php
 //		Function: name of the function to be called in TreeData.php, the function will be called statically.
 //		PARAM prefixed properties: array of these property/values will be passed to the function as parameter.

require_once('include/JSON.php');
require_once('include/entryPoint.php');
require_once('include/upload_file.php');
require_once('include/ytree/Tree.php');
require_once('include/ytree/Node.php');
require_once('modules/KBTags/TreeData.php');

$json = getJSONobj();
$selectedTable = $json->decode(html_entity_decode($_REQUEST['selectedTable']));
 if(isset($tagArticleIds['jsonObject']) && $tagArticleIds['jsonObject'] != null){
	$selectedTable = $selectedTable['jsonObject'];
  }
//$GLOBALS['log']->fatal('************ comes here *********');
//$GLOBALS['log']->fatal($_REQUEST['selectedTable']);

function traceTableRelations($table_name){
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
	        $focus = new $bean();
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
	    require ('modules/TableDictionary.php') ;
	    //for module installer incase we alredy loaded the table dictionary
	    if (file_exists ( 'custom/application/Ext/TableDictionary/tabledictionary.ext.php' ))
	    {
	        include ('custom/application/Ext/TableDictionary/tabledictionary.ext.php') ;
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

$relsDrop = traceTableRelations($_REQUEST['selectedTable']);

$relsDropDown ="<div id='rels'>";
$relsDropDown .="<select name='relation_id'  >";
$relsDropDown .="</select>";
$relsDropDown .="</div>";

if($relsDrop != null){
	$relsDropDown ="<div id='rels'>";
	$relsDropDown .="<select name='relation_id'  >";
	$relsDropDown .="<option value=".$_REQUEST['selectedTable']."_Relationships>".$_REQUEST['selectedTable']." Relationships</option>";
	foreach($relsDrop as $rel){
		$relsDropDown .="<option value={$rel}>{$rel}</option>";
	}
	$relsDropDown .="</select>";
	$relsDropDown .="</div>";
}
//$response = "<script>document.getElementById('select_column').innerHTML=$colsDropDown</script>";
$response = $relsDropDown;

if (!empty($response)) {
	echo $response;
}
sugar_cleanup();
exit();
?>