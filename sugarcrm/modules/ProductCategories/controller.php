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
class ProductCategoriesController extends SugarController {
	function ProductCategoriesController(){
		parent::SugarController();
	}

	public function process(){
		// BEGIN SUGARINTERNAL CUSTOMIZATION - ITREQUEST 2205 and 6130 - PROVIDE ACCESS TO PRODUCT CATALOG TO MICHELLE, bobby and brendan
		if (!is_admin($GLOBALS['current_user']) && (!is_admin_for_module($GLOBALS['current_user'],'Products')) && $GLOBALS['current_user']->user_name != 'michelle' && $GLOBALS['current_user']->user_name != 'bhurwitz' && $GLOBALS['current_user']->user_name != 'rasmar' && $GLOBALS['current_user']->user_name != 'balcisto') {
		// END SUGARINTERNAL CUSTOMIZATION - ITREQUEST 2205 and 6130 - PROVIDE ACCESS TO PRODUCT CATALOG TO MICHELLE, bobby and brendan
			$this->hasAccess = false;
		}
		parent::process();
	}
	
}
?>
