<?php

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
require_once('include/utils/tracker_utils.php');

//we don't want the parent module's string file, but rather the string file specifc to this subpanel

global $current_language;

$current_module_strings = return_module_language($current_language, 'Users');

require_once('modules/Administration/updater_utils.php');










class ViewHome_Mobile extends SugarView{
        var $type ='detail';
        
        function ViewHome_Mobile(){
                parent::SugarView();
		$this->options['show_title'] = true;
		$this->options['show_header'] = false;
		$this->options['show_footer'] = false;
        }
        function display(){
		global $current_user;

		$track_object = new Tracker();
		$recent_objects = $track_object->get_recently_viewed($current_user->id,'');

// Retrieve username and password from the session if possible.
global $sugar_config;

                $lp = new SUI_page('SugarCRM');

		require_once('custom/include/sugarmobile/metadata/fields.php');
		global $app_list_strings;
		global $app_strings;
		
		$lp->add_text('Welcome, '.$current_user->user_name.'!');	

		$lp->add_text('Your Recent Sugar Items:');
                
		foreach ($recent_objects as $recent_object) {
                        $lp->add_link($recent_object['module_name'].' / '.$recent_object['item_summary'],'index.php?module='.$recent_object['module_name'].'&action=detail_mobile&record='.$recent_object['item_id']);
                }

                $lp->add_text('<br />');

		require_once('custom/include/sugarmobile/metadata/fields.php');
                $module_select = new SUI_form('index.php');
                $module_select->add_select($module_list_mobile,'module',$this->module);
                $module_select->add_hidden('action','list_mobile');
                $module_select->add_button('go...');

	echo $lp->render();
	$module_select->render();
}
}
?>

