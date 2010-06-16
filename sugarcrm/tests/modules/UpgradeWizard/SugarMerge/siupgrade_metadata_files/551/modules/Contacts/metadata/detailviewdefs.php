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
$viewdefs['Contacts']['DetailView'] = array(
'templateMeta' => array('form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES',
//BEGIN SUGARCRM flav!=dce ONLY
                                                         array('customCode'=>'<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">'),
//END SUGARCRM flav!=dce ONLY
                                                        ),
                                       ),
                        'maxColumns' => '2', 
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        'includes'=> array(
                            			array('file'=>'modules/Leads/Lead.js'),
                         				),		                
                        ),
'panels' =>array (
   'default'=>array(
      array (
	    array (
	      'name' => 'full_name',
	      'label' => 'LBL_NAME',
	    ),
	
	    array (
	      'name' => 'phone_work',
	      'label' => 'LBL_OFFICE_PHONE',
	    ),
	  ),
	  
	  array (
	    'account_name',
	    
	    array (
	      'name' => 'phone_mobile',
	      'label' => 'LBL_MOBILE_PHONE',
	    ),
	  ),
	  
	  array (
	    'lead_source',
	    
	    array (
	      'name' => 'phone_home',
	      'label' => 'LBL_HOME_PHONE',
	    ),
	  ),
	  
	  array (
	    
	    array (
	      'name' => 'campaign_name',
	      'label' => 'LBL_CAMPAIGN',
	    ),
	    
	    array (
	      'name' => 'phone_other',
	      'label' => 'LBL_OTHER_PHONE',
	    ),
	  ),
	  
	  array (
	    'title',
	    array (
	      'name' => 'phone_fax',
	      'label' => 'LBL_FAX_PHONE',
	    ),
	  ),
	  
	  array (
	    'department',
	    'birthdate',
	  ),
	  
	  array (
	    'report_to_name',
	    'assistant',
	  ),
	  
	  array (
	    'sync_contact',
	    'assistant_phone',
	  ),
	  
	  array (
	    'do_not_call',
	    '',
	  ),
	  
	  array (
	     //BEGIN SUGARCRM flav=pro ONLY
			'team_name', 
			//END SUGARCRM flav=pro ONLY
	    
	    array (
	      'name' => 'date_modified',
	      'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
	      'label' => 'LBL_DATE_MODIFIED',
	    ),
	  ),
	  
	  array (
	    'assigned_user_name',
	    
	    array (
	      'name' => 'date_entered',
	      'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
	      'label' => 'LBL_DATE_ENTERED',
	    ),
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
      //BEGIN SUGARCRM flav=ent ONLY
	  array (
	    array('name'=>'portal_name',
	          'customCode'=>'{if $PORTAL_ENABLED}{$fields.portal_name.value}{else}&nbsp;{/if}',
	          'customLabel'=>'{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_NAME" module="Contacts"}{else}&nbsp;{/if}'),
	    array('name'=>'portal_active',
	          'customCode'=>'{if $PORTAL_ENABLED}
	          		         {if strval($fields.portal_active.value) == "1" || strval($fields.portal_active.value) == "yes" || strval($fields.portal_active.value) == "on"}
	          		         {assign var="checked" value="CHECKED"}
                             {else}
                             {assign var="checked" value=""}
                             {/if}
                             <input type="checkbox" class="checkbox" name="{$fields.portal_active.name}" size="{$displayParams.size}" disabled="true" {$checked}>
                             {else}&nbsp;{/if}',
                             'customLabel'=>'{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_ACTIVE" module="Contacts"}{else}&nbsp;{/if}'),
	  ),
      //END SUGARCRM flav=ent ONLY
	  array (
	    'description',
	  ),
	  
	  array (
	    'email1',
	  ),
   ),
)


   
);
?>