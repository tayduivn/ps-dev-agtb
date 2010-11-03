<?php 
 //WARNING: The contents of this file are auto-generated


/*
** @author: Sadek
** SUGARINTERNAL CUSTOMIZATION
** M2 Checkin
** Description: replaced create leads link with create touchpoint link
*/

foreach($module_menu as $key => $menus_defs){
	if($menus_defs[3] == 'Leads'){
		unset($module_menu[$key]);
	}
}

if(ACLController::checkAccess('Touchpoints', 'edit', true))$module_menu[] =     Array("index.php?module=Touchpoints&action=EditView&return_module=Touchpoints&return_action=DetailView", $mod_strings['LNK_NEW_TOUCHPOINT'],"CreateTouchpoints", 'Leads');
// END CUSTOMIZATION


?>