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

// $Id$

$viewdefs['Project']['DetailView'] = array(
	'templateMeta' => array( 
		'maxColumns' => '2',
		'widths' => array(	
			array('label' => '10', 'field' => '30'),
			array('label' => '10', 'field' => '30')
		),
		'includes'=> array(
			 array('file'=>'modules/Project/Project.js'),
	 	),
		'form' => array(
	 		'buttons'=> array(
			 	array( 'customCode' =>
                        '<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" ' .
                        'accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="button" type="submit" ' .
                        'name="Edit" id="edit_button" value="{$APP.LBL_EDIT_BUTTON_LABEL}"'.
                        'onclick="'.
                        '{if $IS_TEMPLATE}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'ProjectTemplatesEditView\';'.
                        '{else}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'EditView\'; '.
                        '{/if}"'.
                        '/>',
                    //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                    'sugar_html' => array(
                        'type' => 'submit',
                        'value' => ' {$APP.LBL_EDIT_BUTTON_LABEL} ',
                        'htmlOptions' => array(
                            'id' => 'edit_button',
                            'class' => 'button',
                            'accessKey' => '{$APP.LBL_EDIT_BUTTON_KEY}',
                            'name' => 'Edit',
                            'onclick' =>
                                '{if $IS_TEMPLATE}'.
                                    'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'ProjectTemplatesEditView\';'.
                                '{else}'.
                                    'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'EditView\'; '.
                                '{/if}',
                        ),
                    ),
				),
				array( 'customCode' =>
                        '<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" ' .
                        'accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="button" ' .
                        'name="Delete" id="delete_button" value="{$APP.LBL_DELETE_BUTTON_LABEL}"'.
                        'onclick="'.
                        '{if $IS_TEMPLATE}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesListView\'; this.form.action.value=\'Delete\'; if( confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\') )  this.form.submit(); '.
                        '{else}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ListView\'; this.form.action.value=\'Delete\'; if( confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\'))  this.form.submit(); '.
                        '{/if}"'.
                        '/>',
                    //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                    'sugar_html' => array(
                        'type' => 'button',
                        'id'    => 'delete_button',
                        'value' => '{$APP.LBL_DELETE_BUTTON_LABEL}',
                        'htmlOptions' => array(
                            'title' => '{$APP.LBL_DELETE_BUTTON_TITLE}',
                            'accessKey' => '{$APP.LBL_DELETE_BUTTON_KEY}',
                            'id'    => 'delete_button',
                            'class' => 'button',
                            'onclick' =>
                                '{if $IS_TEMPLATE}'.
                                    'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesListView\'; this.form.action.value=\'Delete\'; if (confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\')) this.form.submit();'.
                                '{else}'.
                                    'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ListView\'; this.form.action.value=\'Delete\'; if (confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\')) this.form.submit();'.
                                '{/if}',
                        ),

                    ),
				),

				//BEGIN SUGARCRM flav=com ONLY
				 array( 'customCode' =>
                        '<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" ' .
                        'accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="button" type="submit" ' .
                        'name="Duplicate" id="duplicate_button" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}"'.
                        'onclick="'.
                        '{if $IS_TEMPLATE}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.isDuplicate.value=true; this.form.action.value=\'projecttemplateseditview\'; this.form.return_id.value=\'{$id}\';'.
                        '{else}'.
                            'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.isDuplicate.value=true; this.form.action.value=\'EditView\'; this.form.return_id.value=\'{$id}\';'.
                        '{/if}"'.
                        '"/>',
                     //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                     'sugar_html' => array(
                         'type' => 'submit',
                         'value' => '{$APP.LBL_DUPLICATE_BUTTON_LABEL}',
                         'htmlOptions' => array(
                             'title' => '{$APP.LBL_DUPLICATE_BUTTON_TITLE}',
                             'accessKey' => '{$APP.LBL_DUPLICATE_BUTTON_KEY}',
                             'class' => 'button',
                             'name' => 'Duplicate',
                             'id' => 'duplicate_button',
                             'onclick' =>
                                 '{if $IS_TEMPLATE}'.
                                     'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.isDuplicate.value=true; this.form.action.value=\'projecttemplateseditview\'; this.form.return_id.value=\'{$id}\';'.
                                 '{else}'.
                                     'this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.isDuplicate.value=true; this.form.action.value=\'EditView\'; this.form.return_id.value=\'{$id}\';'.
                                 '{/if}',
                         ),
                     ),
                ),
				//END SUGARCRM flav=com ONLY

				//BEGIN SUGARCRM flav=pro ONLY
				array( 'customCode' =>
                        '{if $EDIT_RIGHTS_ONLY}<input title="{$MOD.LBL_VIEW_GANTT_TITLE}" '.
                        'class="button" type="submit" '.
                        'name="EditProjectTasks" id="view_gantt_button" value="  {$MOD.LBL_VIEW_GANTT_TITLE}  " '.
                        'onclick="prep_edit_project_tasks(this.form);" />{/if}',
                    //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                    'sugar_html' => array(
                        'type' => 'submit',
                        'value' => '{$MOD.LBL_VIEW_GANTT_TITLE}',
                        'htmlOptions' => array(
                            'title' => '{$MOD.LBL_VIEW_GANTT_TITLE}',
                            'class' => 'button',
                            'name' => 'EditProjectTasks',
                            'id' => 'view_gantt_button',
                            'onclick' => 'prep_edit_project_tasks(this.form);'
                        ),
                        'template' => '{if $EDIT_RIGHTS_ONLY}[CONTENT]{/if}',
                    )
                ),
				array( 'customCode' =>
                        '<input title="{$SAVE_AS}" ' .
                        'class="button" type="submit" ' .
                        'name="SaveAsTemplate" id="save_as_template_button" value="{$SAVE_AS}"'.
                        'onclick="'.
                        '{if $IS_TEMPLATE}'.
                            'prep_save_as_project(this.form)'.
                        '{else}'.
                            'prep_save_as_template(this.form)'.
                        '{/if}"'.
                        '/>',
                    //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                    'sugar_html' => array(
                        'type' => 'submit',
                        'value' => '{$SAVE_AS}',
                        'htmlOptions' => array(
                            'name' => 'SaveAsTemplate',
                            'class' => 'button',
                            'id'=> 'save_as_template_button',
                            'onclick' =>
                                '{if $IS_TEMPLATE}'.
                                'prep_save_as_project(this.form)'.
                                '{else}'.
                                'prep_save_as_template(this.form)'.
                                '{/if}',

                        ),
                    )
				),
				array( 'customCode' =>
                        '<input title="{$MOD.LBL_EXPORT_TO_MS_PROJECT}" ' .
                        'class="button" type="submit" ' .
                        'name="ExportToProject" id="export_to_ms_project_button" value="{$MOD.LBL_EXPORT_TO_MS_PROJECT}" ' .
                        'onclick="prep_export_to_project(this.form);"/>',
                    //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
                    'sugar_html' => array(
                        'type' => 'submit',
                        'value' => '{$MOD.LBL_EXPORT_TO_MS_PROJECT}',
                        'htmlOptions' => array(
                            'title' => '{$MOD.LBL_EXPORT_TO_MS_PROJECT}',
                            'name' => 'ExportToProject',
                            'id'=> 'export_to_ms_project_button',
                            'onclick' => 'prep_export_to_project(this.form);'
                        ),
                    ),
				),
				//END SUGARCRM flav=pro ONLY
 			),
 		),
	),
	'panels' => array ( 
	  'lbl_project_information' =>
	  array (
			array ( 
				'name', 
				'status', ),
			array (
				array ( 
					'name' => 'estimated_start_date',
					'label' => 'LBL_DATE_START',
  				),
				'priority',
			),
			array (
			    array (
					'name' => 'estimated_end_date',
					'label' => 'LBL_DATE_END',
  				  ),
			  	),
			array (
				'description',
	  		),
  	  ),				
	  'LBL_PANEL_ASSIGNMENT' => 
	  array (
		array (
		  array (
			'name' => 'assigned_user_name',
			'label' => 'LBL_ASSIGNED_TO',
		  ),
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
	),
);
?>