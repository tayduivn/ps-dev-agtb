<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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

require_once('include/MVC/View/views/view.ajax.php');
require_once('modules/Home/UnifiedSearchAdvanced.php');

class ViewSpot extends ViewAjax
{
    /**
     * @see SugarView::display()
     */
    public function display()
    {
        if(!file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php')) {
            $usa = new UnifiedSearchAdvanced;
            $usa->buildCache();
        }
        
        // load the list of unified search enabled modules
        $modules = array();
        
        // check to see if the user has customized the list of modules available to search
        $users_modules = $GLOBALS['current_user']->getPreference('globalSearch', 'search');
        if(!isset($users_modules)) {
            $unified_search_modules = array();
            include($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php');
            foreach ( $unified_search_modules as $key => $value )
                $modules[$key] = $key;
        }
        // otherwise, just use them all
        else {
            foreach ( $users_modules as $key => $value )
                $modules[$key] = $key;
        }
		
		$offset = -1;
		
		// make sure the current module appears first in the list
		if(isset($modules[$this->module])) {
		    unset($modules[$this->module]);
		    $modules = array_merge(array($this->module=>$this->module),$modules);
		}
		
		if(!empty($_REQUEST['zoom']) && isset($modules[$_REQUEST['zoom']])){
			$modules = array($_REQUEST['zoom']);
			if(isset($_REQUEST['offset'])){
				$offset = $_REQUEST['offset'];
			}
		}
		require_once('include/SearchForm/SugarSpot.php');
		$sugarSpot = new SugarSpot;
		$trimmed_query = trim($_REQUEST['q']);
		echo $sugarSpot->searchAndDisplay($trimmed_query, $modules, $offset);
    }
}
