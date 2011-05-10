<?php
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
$dictionary['cases_threads'] = array(
	'table' => 'cases_threads',
	'fields' => array(
		array('name' =>'id', 'type' =>'char', 'len'=>'36', 'required'=>true, 'default'=>''),
		array('name' =>'case_id', 'type' =>'char', 'len'=>'36', 'required'=>true, 'default'=>''),
		array('name' =>'thread_id', 'type' =>'char', 'len'=>'36', 'required'=>true, 'default'=>''),
		array('name' =>'deleted', 'type' =>'tinyint', 'len'=>'1', 'required'=>true, 'default'=>'0'),
		array('name' =>'relationship_type', 'type' =>'varchar', 'len'=>'50', 'required'=>true, 'default'=>'Cases'),
		array('name' =>'date_modified', 'type' =>'datetime'),
	),
	'relationships' => array(
		'cases_threads' => array(
			'lhs_module'=> 'Cases',
			'lhs_table'=> 'cases',
			'lhs_key' => 'id',
			'rhs_module'=> 'Threads',
			'rhs_table'=> 'threads',
			'rhs_key' => 'id',
			'join_table' => 'cases_threads',
			'join_key_lhs' => 'case_id',
			'join_key_rhs' => 'thread_id',
			'relationship_type'=>'many-to-many',
		)
	),
	'indices' => array (
		array(
			'name' =>'cases_threadspk',
			'type' =>'primary',
			'fields'=>array('id')
		),
		array(
			'name' =>'idx_cas_thr_cas',
			'type' =>'index',
			'fields'=>array('case_id')
		),
		array(
			'name' =>'idx_cas_thr_thr',
			'type' =>'index',
			'fields'=>array('thread_id')
		),
		array(
			'name' => 'idx_cases_threads',
			'type'=>'alternate_key',
			'fields'=> array(
				'case_id',
				'thread_id',
			)
		)
	)
);

?>