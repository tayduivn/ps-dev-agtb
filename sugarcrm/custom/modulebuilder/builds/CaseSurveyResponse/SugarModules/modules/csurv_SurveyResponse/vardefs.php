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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$dictionary['csurv_SurveyResponse'] = array(
	'table'=>'csurv_surveyresponse',
	'audited'=>true,
	'fields'=>array (
  'contact_id' => 
  array (
    'required' => false,
    'name' => 'contact_id',
    'vname' => '',
    'type' => 'id',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 0,
    'reportable' => 0,
    'len' => 36,
  ),
  'contact_relate' => 
  array (
    'required' => false,
    'source' => 'non-db',
    'name' => 'contact_relate',
    'vname' => 'LBL_CONTACT_RELATE',
    'type' => 'relate',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '255',
    'id_name' => 'contact_id',
    'ext2' => 'Contacts',
    'module' => 'Contacts',
    'studio' => 'visible',
  ),
  'acase_id' => 
  array (
    'required' => false,
    'name' => 'acase_id',
    'vname' => '',
    'type' => 'id',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 0,
    'reportable' => 0,
    'len' => 36,
  ),
  'case_relate' => 
  array (
    'required' => false,
    'source' => 'non-db',
    'name' => 'case_relate',
    'vname' => 'LBL_CASE_RELATE',
    'type' => 'relate',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '255',
    'id_name' => 'acase_id',
    'ext2' => 'Cases',
    'module' => 'Cases',
    'studio' => 'visible',
  ),
  'question_1' => 
  array (
    'required' => false,
    'name' => 'question_1',
    'vname' => 'LBL_QUESTION_1',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'How would you rate the ease of access to the agent who was primarily responsible for resolving your problem?',
    'help' => 'How would you rate the ease of access to the agent who was primarily responsible for resolving your problem?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'question_2' => 
  array (
    'required' => false,
    'name' => 'question_2',
    'vname' => 'LBL_QUESTION_2',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'How would you rate the total time it took us to provide a solution for your problem?',
    'help' => 'How would you rate the total time it took us to provide a solution for your problem?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'question_3' => 
  array (
    'required' => false,
    'name' => 'question_3',
    'vname' => 'LBL_QUESTION_3',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'How would you rate your primary support agent&#039;s professionalism and willingness to listen?',
    'help' => 'How would you rate your primary support agent&#039;s professionalism and willingness to listen?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'question_4' => 
  array (
    'required' => false,
    'name' => 'question_4',
    'vname' => 'LBL_QUESTION_4',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'How would you rate your primary support agent&#039;s technical knowledge?',
    'help' => 'How would you rate your primary support agent&#039;s technical knowledge?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'question_5' => 
  array (
    'required' => false,
    'name' => 'question_5',
    'vname' => 'LBL_QUESTION_5',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'How would you rate the help we gave you as a satisfactory solution to your problem?',
    'help' => 'How would you rate the help we gave you as a satisfactory solution to your problem?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'question_6' => 
  array (
    'required' => false,
    'name' => 'question_6',
    'vname' => 'LBL_QUESTION_6',
    'type' => 'int',
    'massupdate' => 0,
    'comments' => 'Based on this experience with our support services, what recommendation would you make to other customers?',
    'help' => 'Based on this experience with our support services, what recommendation would you make to other customers?',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'len' => '11',
  ),
  'comments' => 
  array (
    'required' => false,
    'name' => 'comments',
    'vname' => 'LBL_COMMENTS',
    'type' => 'text',
    'massupdate' => 0,
    'comments' => 'Comments',
    'help' => 'Comments',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 1,
    'reportable' => 1,
    'studio' => 'visible',
  ),
),
	'relationships'=>array (
),
	'optimistic_lock'=>true,
);
require_once('include/SugarObjects/VardefManager.php');
VardefManager::createVardef('csurv_SurveyResponse','csurv_SurveyResponse', array('basic','team_security','assignable'));