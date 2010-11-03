<?php
die('Sugar Wireless temporarily unavailable');
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004 - 2007 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/
/*********************************************************************************

 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('custom/include/sugarmobile/ui.php');

//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'Users');
require_once('modules/Administration/updater_utils.php');

class ViewLogin_Mobile extends SugarView{
	var $type ='detail';
	
	function ViewLogin_Mobile(){
		parent::SugarView();
		$this->options['show_title'] = true;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = true;
	}
	
	function display(){
		// Retrieve username and password from the session if possible.
		global $sugar_config;
		if(isset($_SESSION["login_user_name"])) {
			if (isset($_REQUEST['default_user_name']))
				$login_user_name = $_REQUEST['default_user_name'];
			else
				$login_user_name = $_SESSION['login_user_name'];
		} else {
			if(isset($_REQUEST['default_user_name'])) {
				$login_user_name = $_REQUEST['default_user_name'];
			} elseif(isset($_REQUEST['ck_login_id_20'])) {
				$login_user_name = get_user_name($_REQUEST['ck_login_id_20']);
			} else {
				$login_user_name = $sugar_config['default_user_name'];
			}
			$_SESSION['login_user_name'] = $login_user_name;
		}
		
		$current_module_strings['VLD_ERROR'] = $GLOBALS['app_strings']["\x4c\x4f\x47\x49\x4e\x5f\x4c\x4f\x47\x4f\x5f\x45\x52\x52\x4f\x52"];
		
		// Retrieve username and password from the session if possible.
		if(isset($_SESSION["login_password"])) {
			$login_password = $_SESSION['login_password'];
		} else {
			$login_password = $sugar_config['default_password'];
			$_SESSION['login_password'] = $login_password;
		}
		
		if(isset($_SESSION["login_error"])) {
			$login_error = $_SESSION['login_error'];
		}
		
		//echo get_module_title($current_module_strings['LBL_MODULE_NAME'], $current_module_strings['LBL_LOGIN'], false);
		
		$lp = new SUI_page('SugarCRM');
		$lf = new SUI_form('index.php');
		$lf->add_hidden('module','Users');
		$lf->add_hidden('action','Authenticate');
		$lf->add_hidden('return_module','Users');
		$lf->add_hidden('return_action','Login_Mobile');
		$lf->add_hidden('login_action','home_mobile');
		$lf->add_hidden('login_module','Users');
		require_once('custom/include/sugarmobile/metadata/fields.php');
		global $app_list_strings;
		//$lf->add_select($module_list_mobile,'login_module','Accounts');
		global $app_strings;
		//$lp->add_text($app_strings['NTC_LOGIN_MESSAGE']);
		
		global $current_module_strings;
		/*	
		if(isset($login_error) && $login_error != "") {
			$error_string = new HAW_Text($current_module_strings['LBL_ERROR']);
		} 
		*/

		$lf->add_input('user_name','',$current_module_strings['LBL_USER_NAME']);
		$lf->add_password('user_password','',$current_module_strings['LBL_PASSWORD']);
		$lf->add_button('Login','Login');
		$lf->render();
		//$lp->add_text($log_message);
		if (isset($error_string)) {
			$lp->add_text($error_string);
		}

		echo $lp->render();
	}
}
?>

