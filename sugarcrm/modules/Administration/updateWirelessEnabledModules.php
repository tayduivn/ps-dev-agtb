<?php
//FILE SUGARCRM flav=pro ONLY
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


require_once('modules/Administration/Forms.php');

global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

require_once('modules/Configurator/Configurator.php');
$configurator = new Configurator();
$configurator->saveConfig();

if ( isset( $_REQUEST['enabled_modules'] ) && ! empty ($_REQUEST['enabled_modules'] ))
{
	$updated_enabled_modules = array () ;
	foreach ( explode (',', $_REQUEST['enabled_modules'] ) as $e )
	{
		$updated_enabled_modules [ $e ] = array () ;
	}

	// transfer across any pre-existing definitions for the enabled modules from the current module registry
	if (file_exists('include/MVC/Controller/wireless_module_registry.php'))
	{
		require('include/MVC/Controller/wireless_module_registry.php');
		if ( ! empty ( $wireless_module_registry ) )
		{
			foreach ( $updated_enabled_modules as $e => $def )
			{
				if ( isset ( $wireless_module_registry [ $e ] ) )
				{
					$updated_enabled_modules [ $e ] = $wireless_module_registry [ $e ] ;
				}

			}
		}
	}

	$filename = 'custom/include/MVC/Controller/wireless_module_registry.php' ;

	mkdir_recursive ( dirname ( $filename ) ) ;
	write_array_to_file ( 'wireless_module_registry', $updated_enabled_modules, $filename );

}

echo "true";

?>