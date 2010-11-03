<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 

global $current_user, $mod_strings;

$module_menu = array();
if(is_admin($current_user)){
	$module_menu[]=Array("index.php?module=fonuae_PBXSettings&action=EditView&return_module=fonuae_PBXSettings&return_action=index", $mod_strings['LNK_NEW_RECORD'],"Createfonuae_PBXSettings", 'fonuae_PBXSettings');
	$module_menu[]=Array("index.php?module=fonuae_PBXSettings&action=index&return_module=fonuae_PBXSettings&return_action=DetailView", $mod_strings['LNK_LIST'],"fonuae_PBXSettings", 'fonuae_PBXSettings');
}
?>
