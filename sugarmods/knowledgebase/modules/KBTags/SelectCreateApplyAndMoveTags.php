<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
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
require_once('include/utils/file_utils.php');
require_once('data/SugarBean.php');
require_once('include/JSON.php');
require_once('include/entryPoint.php');
require_once('include/upload_file.php');
require_once('include/ytree/Tree.php');
require_once('include/ytree/Node.php');
require_once('modules/KBTags/TreeData.php');
require_once('modules/KBTags/KBTree.php');

//session_start();
$ret=array();
$params1=array();
$nodes=array();
global $sugar_config;


function authenticate()
{
	global $sugar_config;
 	$user_unique_key = (isset($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : "";
 	$server_unique_key = (isset($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : "";

 	if ($user_unique_key != $server_unique_key) {
		$GLOBALS['log']->debug("JSON_SERVER: user_unique_key:".$user_unique_key."!=".$server_unique_key);
        session_destroy();
        return null;
 	}

 	if(!isset($_SESSION['authenticated_user_id']))
 	{
 		$GLOBALS['log']->debug("JSON_SERVER: authenticated_user_id NOT SET. DESTROY");
        session_destroy();
        return null;
 	}

 	$current_user = new User();

 	$result = $current_user->retrieve($_SESSION['authenticated_user_id']);
 	$GLOBALS['log']->debug("JSON_SERVER: retrieved user from SESSION");

 	if($result == null)
 	{
		$GLOBALS['log']->debug("JSON_SERVER: could get a user from SESSION. DESTROY");
   		session_destroy();
   		return null;
 	}
	return $result;
}

if(!empty($sugar_config['session_dir'])) {
	session_save_path($sugar_config['session_dir']);
	$GLOBALS['log']->debug("JSON_SERVER:session_save_path:".$sugar_config['session_dir']);
}

//get language
$current_language = $sugar_config['default_language'];
// if the language is not set yet, then set it to the default language.
if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
	$current_language = $_SESSION['authenticated_user_language'];
} 

//validate user.
$current_user = authenticate();

global $app_strings;
if (empty($app_strings)) {
    //set module and application string arrays based upon selected language
    $app_strings = return_application_language($current_language);
}

//get theme
$theme = $sugar_config['default_theme'];
if(isset($_SESSION['authenticated_user_theme']) && $_SESSION['authenticated_user_theme'] != '') {
	$theme = $_SESSION['authenticated_user_theme'];
}
//set image path
$image_path = 'themes/'.$theme.'/images/';

$json = getJSONobj();
$tagsMode = $json->decode(html_entity_decode($_REQUEST['tagsMode']));
 if(isset($tagsMode['jsonObject']) && $tagsMode['jsonObject'] != null){
	$tagsMode = $tagsMode['jsonObject'];
  }

	if (!empty($_REQUEST['searchTagName'])) {
		 $search_tag_name = $json->decode(html_entity_decode($_REQUEST['searchTagName']));
		 if(isset($search_tag_name['jsonObject']) && $search_tag_name['jsonObject'] != null){
			$search_tag_name = $search_tag_name['jsonObject'];
			$tagsMode = 'Search Tags';
		  }
	}  

require_once('modules/KBTags/KBTag.php');
require_once('modules/KBDocuments/KBDocument.php');
   $KBTag = new KBTag();  
   $response = '';   
   if($tagsMode == 'Select Create Tags'){
	    $tagstree=new Tree('tagstree');
	    $tagstree->set_param('module','KBTags'); 
	    $tagstree->set_param('moduleview','modal');        
	   //$nodes = get_tags_nodes_cached(null); 
	    $nodes=get_tags_nodes(false,false,null);        
	    $root_node = new Node('All_Tags', $mod_strings['LBL_TAGS_ROOT_LABEL']);
	     //$tagstree->add_node($root_node);
	    foreach ($nodes as $node) {                                         
	      $root_node->add_node($node);                        
	    }       
	    $href_string = "javascript:handler:SUGAR.kb.modalClose('tagstree')";
		if ($root_node) {
		    $root_node->set_property("href",$href_string);   	
		}   
	    $root_node->expanded = true;    
	    $tagstree->add_node($root_node);          	 
	    $response = $tagstree->generate_nodes_array(); 	    
   }
      
   if($tagsMode == 'Move Tags'){
	    $tagstreeModal=new Tree('tagstreeMoveDocsModal');
	    $tagstreeModal->set_param('module','KBTags');
	    $tagstreeModal->set_param('moduleview','modalMoveDocs');	
	    $nodes=get_tags_modal_nodes(null,false);
	    foreach ($nodes as $node) {
	        $tagstreeModal->add_node($node);
	    }	
	   $response = $tagstreeModal->generate_nodes_array();
   }
   if($tagsMode == 'Apply Tags'){
	    $tagstreeApply=new Tree('tagstreeApplyTags');
	    $tagstreeApply->set_param('module','KBTags');
	    $tagstreeApply->set_param('moduleview','applyTags');	
	    $nodes=get_tags_modal_nodes(null,true);	    
	    foreach ($nodes as $node) {
	        $tagstreeApply->add_node($node);
	    }	
	    $response = $tagstreeApply->generate_nodes_array();
   }
   //ADDING FOR SEARCH
  if($tagsMode == 'Search Tags'){	 
    global $mod_strings; 	
  
	   if(!empty($search_tag_name)){			      	        	        
	        $search_tag_name = PearDatabase::quote(from_html($search_tag_name[0]));
	        
	        $query="select id,tag_name from kbtags where tag_name='$search_tag_name' and deleted = 0";	
	        $result = $GLOBALS['db']->query($query);    
	        //$searched_tagIds  =  $GLOBALS['db']->fetchByAssoc($result);
	        
	        $searched_and_related_Ids = array();
	        $searched_ids = array();	        
	        //for each search tag id find all the way to the root and save the nodes enroute
	        //in the sequence.
	        $found=0;
	        while (($searchIds=$GLOBALS['db']->fetchByAssoc($result))!= null) {
	        	$found=1;
	        	$searched_and_related_Ids[$searchIds['id']] = getRootNode($searchIds['id'],$searchIds['tag_name']);                  	
	        }    
	       
	        if ($found==1) { 
		        $root_node=get_searched_tags_nodes($searched_and_related_Ids);
		        $tagstree=new KBTree('tagstree');
		        $tagstree->set_param('module','KBTags');
		        $tagstree->set_param('moduleview','modal');
			    $tagstree->add_node($root_node); 
//			    $response = generate_nodes_array_custom($tagstree);
			    $response = $tagstree->generate_nodes_array($tagstree);
	        } else {
                $not_found_msg = $mod_strings['LBL_NO_MATHING_TAG_FOUND'];
	        	$response="<script>alert('$not_found_msg')</script>";	
	        }
		    echo $response;
			sugar_cleanup();
			exit();

	   }
   }
   
if (!empty($response)) {	
	//echo $response;
	//echo 'result = ' . $json->encode($response);
	 echo 'result = ' . $json->encode((array('body' => $response)));                                            
}
sugar_cleanup();
exit();
?>