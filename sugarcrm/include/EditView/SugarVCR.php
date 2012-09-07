<?php
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
 define('VCREND', '50');
 define('VCRSTART', '10');
 /**
  * @api
  */
 class SugarVCR{

 	/**
 	 * records the query in the session for later retrieval
 	 */
 	function store($module, $query){
 		$_SESSION[$module .'2_QUERY'] = $query;
 	}

 	/**
 	 * This function retrieves a query from the session
 	 */
 	function retrieve($module){
 		return (!empty($_SESSION[$module .'2_QUERY']) ? $_SESSION[$module .'2_QUERY'] : '');
 	}

 	/**
 	 * return the start, prev, next, end
 	 */
 	function play($module, $offset){
 		//given some global offset try to determine if we have this
 		//in our array.
 		$ids = array();
 		if(!empty($_SESSION[$module.'QUERY_ARRAY']))
 			$ids = $_SESSION[$module.'QUERY_ARRAY'];
 		if(empty($ids[$offset]) || empty($ids[$offset+1]) || empty($ids[$offset-1]))
 			$ids = SugarVCR::record($module, $offset);
 		$menu = array();
 		if(!empty($ids[$offset])){
 			//return the control given this id
 			$menu['PREV'] = ($offset > 1) ? $ids[$offset-1] : '';
 			$menu['CURRENT'] = $ids[$offset];
 			$menu['NEXT'] = !empty($ids[$offset+1]) ? $ids[$offset+1] : '';
 		}
 		return $menu;
 	}

    function menu($module, $offset, $isAuditEnabled, $saveAndContinue = false ){
        $html_text = "";
        if ($offset < 0)
        {
            $offset = 0;
        }

        //this check if require in cases when you visit the edit view before visiting that modules list view.
        //you can do this easily either from home, activities or sitemap.
        $stored_vcr_query = SugarVCR::retrieve($module);

        // bug 15893 - only show VCR if called as an element in a set of records
        if (!empty($_REQUEST['record']) and !empty($stored_vcr_query) and isset($_REQUEST['offset']) and (empty($_REQUEST['isDuplicate']) or $_REQUEST['isDuplicate'] == 'false'))
        {
            //syncing with display offset;
            $offset ++;
            $action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : 'EditView');

            $menu = SugarVCR::play($module, $offset);

            $list_link = '';
            if ($saveAndContinue && !empty($menu['NEXT']))
            {
                $list_link = ajaxLink('index.php?action=' . $action . '&module=' . $module . '&record=' . $menu['NEXT'] . '&offset=' . ($offset + 1));
            }

            $previous_link = "";
            if (!empty($menu['PREV']))
            {
                $previous_link = ajaxLink('index.php?module=' . $module . '&action=' . $action . '&offset=' . ($offset - 1) . '&record=' . $menu['PREV']);
            }

            $next_link = "";
            if (!empty($menu['NEXT']))
            {
                $next_link = ajaxLink('index.php?module=' . $module . '&action=' . $action . '&offset=' . ($offset + 1) . '&record=' . $menu['NEXT']);
            }

            $ss = new Sugar_Smarty();
            $ss->assign('app_strings', $GLOBALS['app_strings']);
            $ss->assign('module', $module);
            $ss->assign('action', $action);
            $ss->assign('menu', $menu);
            $ss->assign('list_link', $list_link);
            $ss->assign('previous_link', $previous_link);
            $ss->assign('next_link', $next_link);

            $ss->assign('offset', $offset);
            $ss->assign('total', '');
            $ss->assign('plus', '');

            if (!empty($_SESSION[$module . 'total']))
            {
                $ss->assign('total', $_SESSION[$module . 'total']);
                if (
                    !empty($GLOBALS['sugar_config']['disable_count_query'])
                    && (($_SESSION[$module. 'total']-1) % $GLOBALS['sugar_config']['list_max_entries_per_page'] == 0)
                )
                {
                    $ss->assign('plus', '+');
                }
            }

            if (is_file('custom/include/EditView/SugarVCR.tpl'))
            {
                $html_text .= $ss->fetch('custom/include/EditView/SugarVCR.tpl');
            }
            else
            {
                $html_text .= $ss->fetch('include/EditView/SugarVCR.tpl');
            }
        }
        return $html_text;
    }

 	function record($module, $offset){
 		$GLOBALS['log']->debug('SUGARVCR is recording more records');
 		$start = max(0, $offset - VCRSTART);
 		$index = $start;
	    $db = DBManagerFactory::getInstance();

 		$result = $db->limitQuery(SugarVCR::retrieve($module),$start,($offset+VCREND),false);
 		$index++;

 		$ids = array();
 		while(($row = $db->fetchByAssoc($result)) != null){
 			$ids[$index] = $row['id'];
 			$index++;
 		}
 		//now that we have the array of ids, store this in the session
 		$_SESSION[$module.'QUERY_ARRAY'] = $ids;
 		return $ids;
 	}

 	function recordIDs($module, $rids, $offset, $totalCount){
 		$index = $offset;
 		$index++;
 		$ids = array();
 		foreach($rids as $id){
 			$ids[$index] = $id;
 			$index++;
 		}
 		//now that we have the array of ids, store this in the session
 		$_SESSION[$module.'QUERY_ARRAY'] = $ids;
 		$_SESSION[$module.'total'] = $totalCount;
 	}

 	function erase($module){
 		unset($_SESSION[$module. 'QUERY_ARRAY']);
 	}

 }
?>
