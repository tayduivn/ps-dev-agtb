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
 * @tasknum 33
 * Dashlet Code for Sales Mgmt to see pending orders.  
 */

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/Orders/Orders.php');

class SalesMgmtPendingOrdersDashlet extends DashletGeneric { 
    function SalesMgmtPendingOrdersDashlet($id, $def = null) {
		global $current_user, $app_strings;
		require('custom/modules/Orders/Dashlets/SalesMgmtPendingOrdersDashlet/SalesMgmtPendingOrdersDashlet.data.php');

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = 'Pending Orders (Rollup)';
	
        $this->showMyItemsOnly = false;

       	$this->searchFields = $dashletData['SalesMgmtPendingOrdersDashlet']['searchFields'];
       	$this->columns = $dashletData['SalesMgmtPendingOrdersDashlet']['columns'];


       	$this->isConfigurable = true;
       	$this->seedBean = new Orders();
    }
   

	function buildWhere() {
		global $current_user;
                $order = new Orders();
                $order_table_name = $order->table_name;
		$db = &DBManagerFactory::getInstance();
                
		require_once('custom/si_custom_files/MoofCartHelper.php');
		// GET THE USERS DOWN THE LINE THAT REPORT TO CURRENT USER
		$moo = new MoofCartHelper( );
		$moo->retrieve_downline( $current_user->id );
	
		$salesop_id = MoofCartHelper::$salesop_id;

                $reports_to = $moo->my_downline;

       		
		$options = $this->loadOptions();
		// SET EXCLUDE SALESOPS BASED ON THE OPTIONS
	        $exclude_salesops = (!isset( $options['filters'][ 'exclude_salesops_c' ] ) ) ? 'yes' : $options['filters'][ 'exclude_salesops_c' ][0];
	
		// IF WE NEED TO EXCLUDE SALESOPS 
		if( $exclude_salesops == 'yes') {
			if( !empty( $reports_to ) ) {
		
			$qry = "SELECT users.id 
				FROM users, roles, roles_users 
				WHERE 	users.deleted = 0 AND
					users.reports_to_id = '{$current_user->id}' AND
					users.id = roles_users.user_id AND
					roles_users.role_id = roles.id AND
					roles.name IN ( 'Sales Operations', 'Sales Operations Opportunity Admin' ) AND
					roles_users.deleted = 0 AND
					roles.deleted = 0";	
	
			$result = $db->query( $qry );

                	while($row = $db->fetchByAssoc($result)) {
				// FIND AND UNSET THEM
				if( $unset = array_search( $row[ 'id' ], $reports_to ) ) {
					unset( $reports_to[ $unset ] );
				}
                	}
			// UNSET SALESOP USER IF IT IS IN THERE
			if( $unset = array_search( $salesop_id, $reports_to ) ) {
				unset( $reports_to[ $unset ] );
			}
			}
	
		}
		else {
			$reports_to[] = $salesop_id;
		}

		$where_clauses = array( );
		array_push( $where_clauses, "{$order_table_name}.deleted = 0" );		
		array_push( $where_clauses, "{$order_table_name}.status = 'pending_salesops'" );
		// IF THEY HAVE PEOPLE THAT REPORT TO THEM IMPLODE TE ARRAY AND USE THAT, ELSE JUST GET THEIR ORDERS
		if( is_array( $reports_to ) && !empty( $reports_to ) ) {
			array_push( $where_clauses, "{$order_table_name}.assigned_user_id IN ('" . implode( "','", $reports_to ) . "')" );
		}
		else {
			array_push( $where_clauses, "{$order_table_name}.assigned_user_id = {$current_user->id}" );
		}
		return $where_clauses;
	}
}
