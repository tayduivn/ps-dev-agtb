<?php
/*
** @author: Jesse Mullan, Julian Ostrow
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (original unknown), 7809
** Description: performs a 1-to-1 mapping of sugarcrm.com usernames to Contact/LeadPerson/Touchpoint portal names, based on e-mail address 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Moofcart/SyncPortalUsersToPersonRecords.php
*/

chdir('../../');
define('sugarEntry', true);
 
require_once('include/entryPoint.php');

if(empty($current_language)) {
	$current_language = $sugar_config['default_language'];
}

$app_list_strings = return_app_list_strings_language($current_language);
$app_strings = return_application_language($current_language);

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

require_once('custom/si_custom_files/MoofCartHelper.php');

MoofCartHelper::syncPortalUsersToPersonRecords();

$exit_on_cleanup = true;
sugar_cleanup($exit_on_cleanup);
