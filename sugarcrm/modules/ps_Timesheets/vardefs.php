<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:17974
** Description: Timeshssets module enhancements
** Wiki customization page: 
*/
$dictionary['ps_Timesheets'] = array(
	'table'=>'ps_timesheets',
	'audited'=>true,
	'fields'=>array (
  'activity_date' => 
  array (
    'required' => true,
    'name' => 'activity_date',
    'vname' => 'LBL_ACTIVITY_DATE',
    'type' => 'date',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
  ),
  'activity_type' => 
  array (
    'required' => true,
    'name' => 'activity_type',
    'vname' => 'LBL_ACTIVITY_TYPE',
    'type' => 'enum',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => 100,
    'options' => 'activity_type_list',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'time_spent' => 
  array (
    'required' => true,
    'name' => 'time_spent',
    'vname' => 'LBL_TIME_SPENT',
    'type' => 'float',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 0,
    'reportable' => 1,
    'len' => '5',
    'precision' => '2',
  ),
),
	'relationships'=>array (
),
	'optimistic_lock'=>true,
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('ps_Timesheets','ps_Timesheets', array('basic','team_security','assignable'));
