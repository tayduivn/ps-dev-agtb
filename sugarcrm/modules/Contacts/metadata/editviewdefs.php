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
$viewdefs['Contacts']['EditView'] = array(
    'templateMeta' => array('form'=>array('hidden'=>array('<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
    											          '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
    											          '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
    											          '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
    											          '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">')),
							'maxColumns' => '2',
							//BEGIN SUGARCRM flav=pro || flav=sales ONLY
							'useTabs' => true,
							//END SUGARCRM flav=pro || flav=sales ONLY
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30'),
                                        ),
),


    'panels' =>
    array (
      'lbl_contact_information' =>
      array (
        array (
          array (
            'name' => 'first_name',
            'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}' 
	      . '&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
	      ),
          
	      //BEGIN SUGARCRM flav!=com ONLY
	      'picture',
	      //END SUGARCRM flav!=com ONLY

        ),
        array (
          array (
            'name' => 'last_name',
          ),
          array (
            'name' => 'phone_work',
            'comment' => 'Work phone number of the contact',
            'label' => 'LBL_OFFICE_PHONE',
          ),
        ),

        array (

          array (
            'name' => 'title',
            'comment' => 'The title of the contact',
            'label' => 'LBL_TITLE',
          ),
          array (
            'name' => 'phone_mobile',
            'comment' => 'Mobile phone number of the contact',
            'label' => 'LBL_MOBILE_PHONE',
          ),

        ),

        array (
          'department',
          array (
            'name' => 'phone_fax',
            'comment' => 'Contact fax number',
            'label' => 'LBL_FAX_PHONE',
          ),
        ),
        array(
          array (
            'name' => 'account_name',
            'displayParams' =>
            array (
              'key' => 'billing',
              'copy' => 'primary',
              'billingKey' => 'primary',
              'additionalFields' =>
              array (
                'phone_office' => 'phone_work',
              ),
            ),
          ),
        ),

        array (

          array (
            'name' => 'primary_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' =>
            array (
              'key' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),

          array (
            'name' => 'alt_address_street',
            'hideLabel' => true,
            'type' => 'address',
            'displayParams' =>
            array (
              'key' => 'alt',
              'copy' => 'primary',
              'rows' => 2,
              'cols' => 30,
              'maxlength' => 150,
            ),
          ),
        ),

        array (

          array (
            'name' => 'email1',
            'studio' => 'false',
            'label' => 'LBL_EMAIL_ADDRESS',
          ),
        ),

        array (

          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
      ),


      'LBL_PANEL_ADVANCED' =>
      array (

        array (

          array (
            'name' => 'report_to_name',
            'label' => 'LBL_REPORTS_TO',
          ),

          array (
            'name' => 'sync_contact',
            'comment' => 'Synch to outlook?  (Meta-Data only)',
            'label' => 'LBL_SYNC_CONTACT',
          ),
        ),

        array (

          array (
            'name' => 'lead_source',
            'comment' => 'How did the contact come about',
            'label' => 'LBL_LEAD_SOURCE',
          ),

          array (
            'name' => 'do_not_call',
            'comment' => 'An indicator of whether contact can be called',
            'label' => 'LBL_DO_NOT_CALL',
          ),
        ),

        //BEGIN SUGARCRM flav!=sales ONLY
        array (
	      'campaign_name',
        ),
        //END SUGARCRM flav!=sales ONLY
      ),

	  //BEGIN SUGARCRM flav=ent ONLY
	  'lbl_portal_information' =>
	  array (

	    array (
	      array('name'=>'portal_name',
	            'customCode'=>'<table border="0" cellspacing="0" cellpadding="0"><tr><td>
	                           <input id="portal_name" name="portal_name" type="text" size="30" maxlength="{$fields.portal_name.len|default:\'30\'}" value="{$fields.portal_name.value}" autocomplete="off">
	                           <input type="hidden" id="portal_name_existing" value="{$fields.portal_name.value}" autocomplete="off">
	                           </td><tr><tr><td><input type="hidden" id="portal_name_verified" value="true"></td></tr></table>',
	      ),
	      'portal_active',
	    ),
	    array (
	      array('name'=>'portal_password1',
	            'type'=>'password',
	            'customCode'=>'<input id="portal_password1" name="portal_password1" type="password" size="32" maxlength="{$fields.portal_password.len|default:\'32\'}" value="{$fields.portal_password.value}" autocomplete="off">',
	            'label'=>'LBL_PORTAL_PASSWORD'
	      ),
	    ),

	    array (
	      array('name'=>'portal_password',
	            'customCode'=>'<input id="portal_password" name="portal_password" type="password" size="32" maxlength="{$fields.portal_password.len|default:\'32\'}" value="{$fields.portal_password.value}" autocomplete="off">' .
	            		      '<input name="old_portal_password" type="hidden" value="{$fields.portal_password.value}" autocomplete="off">',
	            'label'=>'LBL_CONFIRM_PORTAL_PASSWORD'
	      ),
	    ),
	  ),
	  //END SUGARCRM flav=ent ONLY


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
    )
);
?>