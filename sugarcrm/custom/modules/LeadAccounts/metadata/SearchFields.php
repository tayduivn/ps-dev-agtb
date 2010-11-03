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
$searchFields['LeadAccounts'] = 
    array (
        'name' => array( 'query_type'=>'default'),
        'lead_source'=> array('query_type'=>'default'),
        'score'=> array('query_type'=>'default'),
        'description'=> array('query_type'=>'default'),
        'date_entered'=> array('query_type'=>'default'),
        'phone'=> array('query_type'=>'default','db_field'=>array('phone_alternate','phone_fax','phone_office')),
        'email'=> array(
			'query_type' => 'default',
			'operator' => 'subquery',
			'subquery' => 'SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE',
			'db_field' => array(
				'id',
			)
		),
        'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
		'address_street'=> array('query_type'=>'default','db_field'=>array('billing_address_street','shipping_address_street')),
		'address_city'=> array('query_type'=>'default','db_field'=>array('billing_address_city','shipping_address_city')),
		'address_state'=> array('query_type'=>'default','db_field'=>array('billing_address_state','shipping_address_state')),
		'address_postalcode'=> array('query_type'=>'default','db_field'=>array('billing_address_postalcode','shipping_address_postalcode')),
		'address_country'=> array('query_type'=>'default','db_field'=>array('billing_address_country','shipping_address_country')),
		'team_name'=> array('query_type'=>'default'),
        'date_modified'=> array('query_type'=>'default'),
        'industry'=> array('query_type'=>'default'),
        'annual_revenue'=> array('query_type'=>'default'),
        'leadaccount_type'=> array('query_type'=>'default'),
        'website'=> array('query_type'=>'default'),
        'rating'=> array('query_type'=>'default'),
        'assigned_user_name'=> array('query_type'=>'default'),
        'modified_by_name'=> array('query_type'=>'default'),
        'converted'=> array('query_type'=>'default'),
        'created_by_name'=> array('query_type'=>'default'),
        'status'=> array('query_type'=>'default'),
        'portal_name'=> array('query_type'=>'default'),
        'conversion_date'=> array('query_type'=>'default'),
        'lead_source'=> array('query_type'=>'default'),
        'call_back_c'=> array('query_type'=>'default'),
        'region_c'=> array('query_type'=>'default'),
        'assigned_user_id'=> array('query_type'=>'default'),
    );
?>
