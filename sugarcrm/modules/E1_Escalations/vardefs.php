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
$dictionary['E1_Escalations'] = array(
	'table'=>'e1_escalations',
	'audited'=>true,
	'fields'=>array (
  'urgency' => 
  array (
    'required' => false,
    'name' => 'urgency',
    'vname' => 'LBL_URGENCY',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'Normal',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'len' => 100,
    'options' => 'urgency',
    'studio' => 'visible',
  ),
  'businessimpact' => 
  array (
    'required' => false,
    'name' => 'businessimpact',
    'vname' => 'LBL_BUSINESSIMPACT',
    'type' => 'text',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'studio' => 'visible',
  ),
  'source' => 
  array (
    'required' => '1',
    'name' => 'source',
    'vname' => 'LBL_SOURCE',
    'type' => 'enum',
    'massupdate' => 0,
    'default' => 'Support',
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'len' => 100,
    'options' => 'source',
    'studio' => 'visible',
  ),
  'escalationdetails' => 
  array (
    'required' => false,
    'name' => 'escalationdetails',
    'vname' => 'LBL_ESCALATIONDETAILS',
    'type' => 'text',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'studio' => 'visible',
  ),
  'reviewcomments' => 
  array (
    'required' => false,
    'name' => 'reviewcomments',
    'vname' => 'LBL_REVIEWCOMMENTS',
    'type' => 'text',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'studio' => 'visible',
  ),
  'dateescalated' => 
  array (
    'required' => false,
    'name' => 'dateescalated',
    'vname' => 'LBL_DATEESCALATED',
    'type' => 'date',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
    'display_default' => 'now',
  ),
  'datereviewed' => 
  array (
    'required' => false,
    'name' => 'datereviewed',
    'vname' => 'LBL_DATEREVIEWED',
    'type' => 'date',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => 1,
    'reportable' => 1,
  ),
),
	'relationships'=>array (
),
	'optimistic_lock'=>true,
);
require_once('include/SugarObjects/VardefManager.php');
VardefManager::createVardef('E1_Escalations','E1_Escalations', array('basic','team_security','assignable'));