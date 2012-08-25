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
//BEGIN SUGARCRM flav!=dce && flav!=sales ONLY
                                                         array('customCode'=>'<input type="submit" class="button" title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}"/>',
                                                             //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                                                             'sugar_html' => array(
                                                                 'type' => 'submit',
                                                                 'value' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
                                                                 'htmlOptions' => array(
                                                                     'class' => 'button',
                                                                     'id' => 'manage_subscriptions_button',
                                                                     'title' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
                                                                     'onclick' => 'this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';',
                                                                     'name' => 'Manage Subscriptions',
                                                                 ),
                                                             ),

                                                         ),
//END SUGARCRM flav!=dce && flav!=sales ONLY
                                                        ),
                                       ),
                        'maxColumns' => '2',
                        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
                        'useTabs' => true,
                        //END SUGARCRM flav=pro || flav=sales ONLY
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'),
                                        array('label' => '10', 'field' => '30')
                                        ),

						'includes'=> array(
										//BEGIN SUGARCRM flav!=sales ONLY
                            			array('file'=>'modules/Leads/Lead.js'),
										//END SUGARCRM flav!=sales ONLY
                         				),
                        ),



    'panels' =>
    array (
      'lbl_contact_information' =>
      array (

        array (

          array (
            'name' => 'full_name',
            'label' => 'LBL_NAME',
            //BEGIN SUGARCRM flav=pro ONLY
            'displayParams' => 
              array (
                'enableConnectors' => true,
                'module' => 'Contacts',
                'connectors' => 
                array (
                  0 => 'ext_rest_twitter',
                ),
            ),
            //END SUGARCRM flav=pro ONLY         
          ),

	      //BEGIN SUGARCRM flav!=com ONLY
	      array (
	      	'name' => 'picture',
	        'label' => 'LBL_PICTURE_FILE',
	      ),
	      //END SUGARCRM flav!=com ONLY
        ),

        array (

          array (
            'name' => 'title',
            'comment' => 'The title of the contact',
            'label' => 'LBL_TITLE',
          ),
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
          ),
        ),

        array (
          array (
                'name' => 'department',
                'label' => 'LBL_DEPARTMENT',
          ),
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
          ),
        ),

        array (
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
            'displayParams' =>
              array (
                'enableConnectors' => true,
                'module' => 'Contacts',
                'connectors' => 
                array (
                  0 => 'ext_rest_linkedin',
                ),
            ),
          ),
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
          ),
        ),

        array (

          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' =>
            array (
              'key' => 'primary',
            ),
          ),

          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALTERNATE_ADDRESS',
            'type' => 'address',
            'displayParams' =>
            array (
              'key' => 'alt',
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
            'comment' => 'Full text of the note',
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

		    array (
		      'name' => 'campaign_name',
		      'label' => 'LBL_CAMPAIGN',
		    ),

	  	),
	  	//END SUGARCRM flav!=sales ONLY

	    //BEGIN SUGARCRM flav=ent ONLY
		array (
		    array('name'=>'portal_name',
		          'label' => 'LBL_PORTAL_NAME',
		          'hideIf' => 'empty($PORTAL_ENABLED)',
		          ),
		    array('name'=>'portal_active',
		          'label' => 'LBL_PORTAL_ACTIVE',
 		    	  'hideIf' => 'empty($PORTAL_ENABLED)',
		        ),
		    ),
        array(
            array('name' => 'preferred_language',
                  'label' => 'LBL_PREFERRED_LANGUAGE',
            ),
        ),
	    //END SUGARCRM flav=ent ONLY

      ),
      'LBL_PANEL_ASSIGNMENT' =>
      array (

        array (

          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),

          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),

        array (
          //BEGIN SUGARCRM flav=pro ONLY
          'team_name',
          //END SUGARCRM flav=pro ONLY

          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),

        ),
      ),
     )
);
?>