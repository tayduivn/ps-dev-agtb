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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



require_once('data/SugarBean.php');

// Contact is used to store customer information.
class SubscriptionDistGroupRelationship extends SugarBean {
	// Stored fields
	var $id;
	var $subscription_id;
	var $quantity;
	var $distgroup_id;

	// Related fields
	var $subscription_name;
	var $distgroup_name;

	var $table_name = "subscriptions_distgroups";
	var $object_name = "SubscriptionDistGroupRelationship";
	var $column_fields = Array(
		"id",
		"subscription_id",
		"distgroup_id",
		"quantity",
		"date_modified",
	);

	var $new_schema = true;
	
	var $additional_column_fields = Array();
	var $field_defs = array(
		'id'=>array('name' =>'id', 'type' =>'char', 'len'=>'36', 'default'=>''),
		'subscription_id'=>array('name' =>'subscription_id', 'type' =>'char', 'len'=>'36', ),
		'distgroup_id'=>array('name' =>'distgroup_id', 'type' =>'char', 'len'=>'36',),
		'quantity'=>array('name' =>'quantity', 'type' =>'int', 'len'=>'20'),
		'date_modified'=>array ('name' => 'date_modified','type' => 'datetime'),
		'deleted'=>array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true),
	);
	
	function SubscriptionDistGroupRelationship() {
		$this->db = & PearDatabase::getInstance();
		$this->dbManager = & DBManagerFactory::getInstance();

		$this->disable_row_level_security=true;
	}

	function fill_in_additional_detail_fields()
	{
		if(isset($this->subscription_id) && $this->subscription_id != "")
		{
			$this->subscription_name = $this->subscription_id;
		}
		if(isset($this->distgroup_id) && $this->distgroup_id != "")
		{
			$query = "SELECT name from distgroups where id='$this->distgroup_id' AND deleted=0";
			$result =$this->db->query($query,true," Error filling in additional detail fields: ");
			// Get the id and the name.
			$row = $this->db->fetchByAssoc($result);

			if($row != null)
			{
				$this->distgroup_name = $row['name'];
			}
		}
	}

	function create_list_query(&$order_by, &$where)
	{
		/*
		$query = "SELECT id, first_name, last_name, phone_work, title, email1 FROM contacts ";
		$where_auto = "deleted=0";

		if($where != "")
			$query .= "where $where AND ".$where_auto;
		else
			$query .= "where ".$where_auto;

		$query .= " ORDER BY last_name, first_name";

		return $query;
		*/
		return '';
	}
}



?>
