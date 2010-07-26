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

require_once('include/MVC/View/views/view.list.php');

class UsersViewList extends ViewList
{
 	public function preDisplay()
 	{
 	    if (   !is_admin($GLOBALS['current_user'])
           //BEGIN SUGARCRM flav=sales ONLY
           && $GLOBALS['current_user']->user_type != 'UserAdministrator'
           //END SUGARCRM flav=sales ONLY
 	       && !is_admin_for_module($GLOBALS['current_user'],'Users') ) 
 	        sugar_die("Unauthorized access to administration.");
 	    
 	    $this->lv = new ListViewSmarty();
 		$this->lv->delete = false;
 	}

//BEGIN SUGARCRM flav=sales ONLY
 	public function listViewProcess(){
 		$this->processSearchForm();
		$this->lv->searchColumns = $this->searchForm->searchColumns;
		
		if(!$this->headers)
			return;
		if(empty($_REQUEST['search_form_only']) || $_REQUEST['search_form_only'] == false){
			$this->lv->ss->assign("SEARCH",true);
			if(!is_admin($GLOBALS['current_user'])){
				if(!empty($this->where)){
					$this->where .= "AND";
				}
				$this->where = " users.is_admin = '0'";
			}
			$this->lv->setup($this->seed, 'include/ListView/ListViewGeneric.tpl', $this->where, $this->params);
			$savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
			echo $this->lv->display();
		}
 	}
//END SUGARCRM flav=sales ONLY
}
