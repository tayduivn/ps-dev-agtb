<?php
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
$vardefs = array (
  'fields' => 
  array (
    'reference' => 
    array (
      'required' => false,
      'name' => 'reference',
      'vname' => 'LBL_REFERENCE',
      'type' => 'enum',
      'massupdate' => '1',
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 0,
      'reportable' => 1,
      'len' => 100,
      'size' => '20',
      'options' => 'cr_reference_list',
      'studio' => 'visible',
      'dependency' => false,
    ),
    'follow_up' => 
    array (
      'required' => false,
      'name' => 'follow_up',
      'vname' => 'LBL_FOLLOW_UP',
      'type' => 'date',
      'massupdate' => '1',
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
    ),
    'reference_notes' => 
    array (
      'required' => false,
      'name' => 'reference_notes',
      'vname' => 'LBL_REFERENCE_NOTES',
      'type' => 'text',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'studio' => 'visible',
      'rows' => '8',
      'cols' => '60',
    ),
    'activity_status' => 
    array (
      'required' => false,
      'name' => 'activity_status',
      'vname' => 'LBL_ACTIVITY_STATUS',
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
      'size' => '20',
      'options' => 'activity_status_list',
      'studio' => 'visible',
      'dependency' => false,
    ),
    'activities_completed_date' => 
    array (
      'required' => false,
      'name' => 'activities_completed_date',
      'vname' => 'LBL_ACTIVITIES_COMPLETED_DATE ',
      'type' => 'text',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'studio' => 'visible',
      'rows' => '12',
      'cols' => '60',
    ),
    'gifts_recieved' => 
    array (
      'required' => false,
      'name' => 'gifts_recieved',
      'vname' => 'LBL_GIFTS_RECIEVED',
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
      'size' => '20',
      'options' => 'gifts_recieved_list',
      'studio' => 'visible',
      'dependency' => false,
    ),
    'reference_score' => 
    array (
      'required' => false,
      'name' => 'reference_score',
      'vname' => 'LBL_REFERENCE_SCORE',
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
      'size' => '20',
      'options' => 'reference_score_list',
      'studio' => 'visible',
      'dependency' => false,
    ),
    'account_id_c' => 
    array (
      'required' => '1',
      'name' => 'account_id_c',
      'vname' => '',
      'type' => 'id',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 1,
      'reportable' => 1,
      'len' => '36',
      'size' => '20',
    ),
    'reference_type' => 
    array (
      'required' => false,
      'name' => 'reference_type',
      'vname' => 'LBL_REFERENCE_TYPE',
      'type' => 'multienum',
      'massupdate' => 0,
      'default' => '^^',
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'options' => 'reference_type_list',
      'studio' => 'visible',
      'isMultiSelect' => true,
    ),
    'user_type' => 
    array (
      'required' => false,
      'name' => 'user_type',
      'vname' => 'LBL_USER_TYPE',
      'type' => 'multienum',
      'massupdate' => 0,
      'default' => '^^',
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'options' => 'user',
      'studio' => 'visible',
      'isMultiSelect' => true,
    ),
    'contact_id_c' => 
    array (
      'required' => false,
      'name' => 'contact_id_c',
      'vname' => '',
      'type' => 'id',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => '0',
      'audited' => 0,
      'reportable' => 1,
      'len' => '36',
      'size' => '20',
    ),
    'reference_deliverables' => 
    array (
      'required' => false,
      'name' => 'reference_deliverables',
      'vname' => 'LBL_REFERENCE_DELIVERABLES',
      'type' => 'multienum',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'options' => 'reference_deliverables_list',
      'studio' => 'visible',
      'isMultiSelect' => true,
    ),
    'reference_activity' => 
    array (
      'required' => false,
      'name' => 'reference_activity',
      'vname' => 'LBL_REFERENCE_ACTIVITY',
      'type' => 'multienum',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'options' => 'reference_activity_list',
      'studio' => 'visible',
      'isMultiSelect' => true,
      'default' => '^^',
    ),
    'solution' => 
    array (
      'required' => false,
      'name' => 'solution',
      'vname' => 'LBL_SOLUTION',
      'type' => 'multienum',
      'massupdate' => 0,
      'comments' => '',
      'help' => '',
      'importable' => 'true',
      'duplicate_merge' => 'disabled',
      'duplicate_merge_dom_value' => 0,
      'audited' => 0,
      'reportable' => 1,
      'size' => '20',
      'options' => 'solution_list',
      'studio' => 'visible',
      'isMultiSelect' => true,
    ),
  ),
  'relationships' => 
  array (
  ),
);
?>
