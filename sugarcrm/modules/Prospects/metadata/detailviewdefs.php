<?php
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
$viewdefs['Prospects']['DetailView'] = array(
'templateMeta' => array('form' => array('buttons' => array('EDIT', 'DUPLICATE', 'DELETE',
                                                     array('customCode' => '<input title="{$MOD.LBL_CONVERT_BUTTON_TITLE}" accessKey="{$MOD.LBL_CONVERT_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Prospects\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\';this.form.module.value=\'Leads\';this.form.action.value=\'EditView\';" type="submit" name="CONVERT_LEAD_BTN" value="{$MOD.LBL_CONVERT_BUTTON_LABEL}"/>'),
                                                     array('customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Prospects\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}"/>'),
                                       ),
                                        'hidden'=>array('<input type="hidden" name="prospect_id" value="{$fields.id.value}">'),
                        				'headerTpl'=>'modules/Prospects/tpls/DetailViewHeader.tpl',
                        ),
                        'maxColumns' => '2',
                        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
                        'useTabs' => true,
                        //END SUGARCRM flav=pro || flav=sales ONLY
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' =>array (
  'lbl_prospect_information' => array(
  array (
    'full_name',
    
    
    ),

  array (
    'title',
    array (
      'name' => 'phone_work',
      'label' => 'LBL_OFFICE_PHONE',
    ),
  ),
  
  array (
    'department',
    'phone_mobile',
  ),
  
  array (
    'account_name',
  	'phone_fax',
  ),
  
  array (
      array (
	      'name' => 'primary_address_street',
	      'label'=> 'LBL_PRIMARY_ADDRESS',
	      'type' => 'address',
	      'displayParams'=>array('key'=>'primary'),
      ),
      
      array (
	      'name' => 'alt_address_street',
	      'label'=> 'LBL_ALTERNATE_ADDRESS',
	      'type' => 'address',
	      'displayParams'=>array('key'=>'alt'),      
      ),
  ),
  
  array (
    'email1',
  ),
  
  array (
    'description',
  ),
  
  ),
  'LBL_MORE_INFORMATION' => array(
    array (
    'email_opt_out',
    'do_not_call',
  ),
    ),
  'LBL_PANEL_ASSIGNMENT' => array(
  array (
      'assigned_user_name',
    array (
      'name' => 'modified_by_name',
      'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;',
      'label' => 'LBL_DATE_MODIFIED',
    ),
  ),
  
  array (
    //BEGIN SUGARCRM flav=pro ONLY
		'team_name', 
	//END SUGARCRM flav=pro ONLY
    array (
      'name' => 'created_by_name',
      'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
      'label' => 'LBL_DATE_ENTERED',
    ),
  ),
  ),
  
  
  
  
)


   
);
?>