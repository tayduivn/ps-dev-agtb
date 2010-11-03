<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 5
 * Dashlet Code for Sales Ops to see pending orders.  Hardcoded the salesops username in the build_where function to the $salesop_id var
 */

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/Orders/Orders.php');

class SalesOpPendingOrdersDashlet extends DashletGeneric { 
     
    function SalesOpPendingOrdersDashlet($id, $def = null) {
		global $current_user, $app_strings;
		require('custom/modules/Orders/Dashlets/SalesOpPendingOrdersDashlet/SalesOpPendingOrdersDashlet.data.php');

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = 'SalesOps Pending Orders';

	$this->showMyItemsOnly = false;
       	$this->searchFields = $dashletData['SalesOpPendingOrdersDashlet']['searchFields'];
       	$this->columns = $dashletData['SalesOpPendingOrdersDashlet']['columns'];
       	$this->isConfigurable = true;
       	$this->seedBean = new Orders();

    }
   

	function buildWhere() {
		global $current_user;

		require_once('modules/Orders/Orders.php');
		$order = new Orders();
		$order_table_name = $order->table_name;
		require_once('custom/si_custom_files/MoofCartHelper.php');
		$salesop_id = MoofCartHelper::$salesop_id;

		$where_clauses = array( );
		if($current_user->check_role_membership('Sales Operations') || $current_user->check_role_membership('Sales Operations Opportunity Admin')) {
			//array_push( $where_clauses, "{$order_table_name}.assigned_user_id IN ('{$current_user->id}','$salesop_id')" );
			array_push( $where_clauses, "{$order_table_name}.status = 'pending_salesops'" );
		}
		else {
			array_push( $where_clauses, "{$order_table_name}.order_id = -1" );

		}
		
		return $where_clauses;
	}
}
