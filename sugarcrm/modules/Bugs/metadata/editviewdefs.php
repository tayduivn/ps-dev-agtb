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
$viewdefs['Bugs']['EditView'] = array(
    'templateMeta' => array('form'=>array('hidden'=>array('<input type="hidden" name="account_id" value="{$smarty.request.account_id}">',
    											          '<input type="hidden" name="contact_id" value="{$smarty.request.contact_id}">')
    											          ),
							'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),                                                                                                                                    
                                            ),
                                            
                                            
 'panels' =>array (
	  'lbl_bug_information' => 
		  array (
		    
		    array (
		      array (
		        'name' => 'bug_number',
		        'type' => 'readonly',
		      ),
		    ),
		    
		    array (
		      array('name'=>'name', 'displayParams'=>array('size'=>60, 'required'=>true)),
		    ),		    
		    
		    array (
		      'priority',
		      'type',
		    ),
		    
		    array (
		      'source',
		      'status',
		
		    ),
		    
		    array (
		      'product_category',
		      'resolution',
		    ),
		    
		    
		    array (
		      'found_in_release',
		      'fixed_in_release'
		    ),
		    
		    array (
		      array (
			      'name' => 'description',
			      'nl2br' => true,
		      ),
		    ),
		    
		    
		    array (
		      array (
			      'name' => 'work_log',
			      'nl2br' => true,
		      ),
		    ),
		    
		  //BEGIN SUGARCRM flav=ent ONLY
		  array(
			  array('name'=>'portal_viewable',
			        'customLabel'=>'{if ($PORTAL_ENABLED)}{sugar_translate label="LBL_SHOW_IN_PORTAL" module="Bugs"}{/if}',
			        'customCode'=>' {if ($PORTAL_ENABLED)}
									{if $fields.portal_viewable.value == "1"}
									{assign var="checked" value="CHECKED"}
									{else}
									{assign var="checked" value=""}
									{/if}
									<input type="hidden" name="{$fields.portal_viewable.name}" value="0"> 
									<input type="checkbox" name="{$fields.portal_viewable.name}" value="1" tabindex="1" {$checked}>
			        		        {/if}',
			  ), 
		  )  
		  //END SUGARCRM flav=ent ONLY
	  ),

      'LBL_PANEL_ASSIGNMENT' => 
      array (
        array (
            array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
	      //BEGIN SUGARCRM flav=pro ONLY
          'team_name',
	      //END SUGARCRM flav=pro ONLY     
        ),
      ),  
),
                        
);
?>