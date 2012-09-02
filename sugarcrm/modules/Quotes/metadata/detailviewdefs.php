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
$viewdefs['Quotes']['DetailView'] = array(
'templateMeta' => array('form' =>array('closeFormBeforeCustomButtons' => true,
                        'buttons'=> array('EDIT', 'DUPLICATE', 'DELETE',
		                                         array('customCode'=>'<form action="index.php" method="POST" name="Quote2Opp" id="form"><input type="hidden" name="module" value="Quotes"><input type="hidden" name="record" value="{$fields.id.value}"><input type="hidden" name="user_id" value="{$current_user->id}"><input type="hidden" name="team_id" value="{$fields.team_id.value}"><input type="hidden" name="user_name" value="{$current_user->user_name}"><input type="hidden" name="action" value="QuoteToOpportunity"><input type="hidden" name="opportunity_subject" value="{$fields.name.value}"><input type="hidden" name="opportunity_name" value="{$fields.name.value}"><input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}"><input type="hidden" name="amount" value="{$fields.total.value}"><input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}"><input type="hidden" name="currency_id" value="{$fields.currency_id.value}"><input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit" name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'),
		                                         ),
                        'footerTpl'=>'modules/Quotes/tpls/DetailViewFooter.tpl'),
                        'maxColumns' => '2', 
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' => array (

	'lbl_quote_information' => array(  
	  array (
		    array (
		      'name' => 'name',
		      'label' => 'LBL_QUOTE_NAME',
		    ),
		    array(
	          'name'=>'opportunity_name', 
	        ),
	  ),
	  
	  array (
		    'quote_num',
		    'quote_stage',
	  ),
	  
	  array (
		    'purchase_order_num',
		    
		    array (
		      'name' => 'date_quote_expected_closed',
		      'label' => 'LBL_DATE_QUOTE_EXPECTED_CLOSED',
		    ),
	  ),
	  
	  array (
		    'payment_terms',
		    'original_po_date',
	  ),
	  
	  array (
	        'billing_account_name',
	        'shipping_account_name',
	  ),
	  
	  array (
		    'billing_contact_name',
		    'shipping_contact_name'
	  ),  
	  
	  array (
	  
	      array (
		      'name' => 'billing_address_street',
		      'label'=> 'LBL_BILL_TO',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'billing'),
	      ),
	      
	      array (
		      'name' => 'shipping_address_street',
		      'label'=> 'LBL_SHIP_TO',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'shipping'),      
	      ),
	    ),
	  
	  array (
		    'description',
	  ),
	),
	'LBL_PANEL_ASSIGNMENT' => array(
        array (
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
        ),
        array (
		  //BEGIN SUGARCRM flav=pro ONLY
		  'team_name', 
		  //END SUGARCRM flav=pro ONLY
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          ),
        ),	
	),
  
) 
);

?>
