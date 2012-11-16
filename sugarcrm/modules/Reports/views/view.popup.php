<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/MVC/View/views/view.popup.php');
class ReportsViewPopup extends ViewPopup
{
	var $type ='list';

	function display()
	{
		global $popupMeta, $mod_strings;

        if(($this->bean instanceOf SugarBean) && !$this->bean->ACLAccess('list')){
            ACLController::displayNoAccess();
            sugar_cleanup(true);
        }

		if(isset($_REQUEST['metadata']) && strpos($_REQUEST['metadata'], "..") !== false) {
		    ACLController::displayNoAccess();
		    sugar_cleanup(true);
		}

		$popupMeta = SugarAutoLoader::loadPopupMeta($this->module, isset($_REQUEST['metadata'])?$_REQUEST['metadata']:null);

		$defs = $this->loadWithPopup('listviewdefs');
		if(is_array($defs)) {
		    $listViewDefs[$this->module] = $defs;
		} elseif(!empty($defs)) {
		    require $defs;
		}

		$defs = $this->loadWithPopup('searchdefs');
		if(is_array($defs)) {
			$searchdefs[$this->module]['layout']['advanced_search'] = $defs;
		} elseif(!empty($defs)) {
			require $defs;
		}

        if(!empty($this->bean) && isset($_REQUEST[$this->module.'2_'.strtoupper($this->bean->object_name).'_offset'])) {//if you click the pagination button, it will populate the search criteria here
            if(!empty($_REQUEST['current_query_by_page'])) {
                $blockVariables = array('mass', 'uid', 'massupdate', 'delete', 'merge', 'selectCount', 'lvso', 'sortOrder', 'orderBy', 'request_data', 'current_query_by_page');
                $current_query_by_page = unserialize(base64_decode($_REQUEST['current_query_by_page']));
                foreach($current_query_by_page as $search_key=>$search_value) {
                    if($search_key != $this->module.'2_'.strtoupper($this->bean->object_name).'_offset' && !in_array($search_key, $blockVariables)) {
						$_REQUEST[$search_key] = $GLOBALS['db']->quote($search_value);
                    }
                }
            }
        }

		foreach(SugarAutoLoader::existing('modules/' . $this->module . '/Popup_picker.php',
		    'include/Popups/Popup_picker.php') as $file) {
		    require_once $file;
		    break;
		}

		$popup = new Popup_Picker();
		$popup->_hide_clear_button = true;
		echo $popup->process_page();
	}
}
