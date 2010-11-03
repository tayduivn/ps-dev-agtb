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
$dictionary['sales_SETicket'] = array(
	'table'=>'sales_seticket',
	'audited'=>true,
	'fields'=>array (
  'status' => 
  array (
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'options' => 'sales_seticket_status_dom',
    'len' => 100,
    'audited' => true,
    'comment' => 'The status of the issue',
    'merge_filter' => 'enabled',
    'required' => false,
    'massupdate' => 0,
    'default' => 'New',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'reportable' => true,
    'size' => '20',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'hoursspent' => 
  array (
    'required' => false,
    'name' => 'hoursspent',
    'vname' => 'LBL_HOURSSPENT',
    'type' => 'decimal',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'len' => '18',
    'size' => '20',
    'precision' => '2',
  ),
  'inperson' => 
  array (
    'required' => false,
    'name' => 'inperson',
    'vname' => 'LBL_INPERSON',
    'type' => 'bool',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'len' => '255',
    'size' => '20',
  ),
  'recurrence' => 
  array (
    'required' => false,
    'name' => 'recurrence',
    'vname' => 'LBL_RECURRENCE',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'none',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'len' => 100,
    'size' => '20',
    'options' => 'recurrence_list',
    'studio' => 'visible',
    'dependency' => false,
  ),
  'tickettype' => 
  array (
    'required' => false,
    'name' => 'tickettype',
    'vname' => 'LBL_TICKETTYPE',
    'type' => 'multienum',
    'massupdate' => 0,
    'default' => '^^',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => false,
    'reportable' => true,
    'size' => '20',
    'options' => 'tickettype_list',
    'studio' => 'visible',
    'isMultiSelect' => true,
  ),
  'event' => 
  array (
    'required' => false,
    'name' => 'event',
    'vname' => 'LBL_EVENT',
    'type' => 'date',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'size' => '20',
  ),
  'description' => 
  array (
    'name' => 'description',
    'vname' => 'LBL_DESCRIPTION',
    'type' => 'text',
    'comment' => 'Full text of the note',
    'rows' => '4',
    'cols' => '150',
    'required' => true,
    'massupdate' => 0,
    'comments' => '',
    'help' => 'Detail project specifications, itemized demo expectations, etc.',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'size' => '20',
    'studio' => 'visible',
  ),
  'name' => 
  array (
    'name' => 'name',
    'vname' => 'LBL_SUBJECT',
    'type' => 'name',
    'dbType' => 'varchar',
    'len' => '255',
    'audited' => true,
    'unified_search' => true,
    'comment' => 'The short description of the bug',
    'merge_filter' => 'selected',
    'required' => true,
    'importable' => 'required',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'reportable' => true,
    'size' => '20',
  ),
  'objective' => 
  array (
    'required' => false,
    'name' => 'objective',
    'vname' => 'LBL_OBJECTIVE',
    'type' => 'text',
    'massupdate' => 0,
    'comments' => '',
    'help' => 'Meeting Next Steps, Project Business Case, etc.',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => false,
    'reportable' => true,
    'size' => '20',
    'studio' => 'visible',
    'rows' => '1',
    'cols' => '150',
  ),
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('sales_SETicket','sales_SETicket', array('basic','team_security','assignable','issue'));