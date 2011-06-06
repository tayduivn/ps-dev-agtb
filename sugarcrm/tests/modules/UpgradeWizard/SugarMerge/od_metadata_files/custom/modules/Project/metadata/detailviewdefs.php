<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
$viewdefs['Project']['DetailView'] = array (
  'templateMeta' => 
  array (
    'maxColumns' => '2',
    'widths' => 
    array (
      0 => 
      array (
        'label' => '10',
        'field' => '30',
      ),
      1 => 
      array (
        'label' => '10',
        'field' => '30',
      ),
    ),
    'includes' => 
    array (
      0 => 
      array (
        'file' => 'modules/Project/Project.js',
      ),
    ),
    'form' => 
    array (
      'buttons' => 
      array (
        0 => 
        array (
          'customCode' => '<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="button" type="submit" name="Edit" id="edit_button" value="{$APP.LBL_EDIT_BUTTON_LABEL}"{if $IS_TEMPLATE}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'ProjectTemplatesEditView\';"{else}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'EditView\';" {/if}"/>',
        ),
        1 => 
        array (
          'customCode' => '<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="Delete" id="delete_button" value="{$APP.LBL_DELETE_BUTTON_LABEL}"{if $IS_TEMPLATE}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesListView\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');"{else}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ListView\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" {/if}"/>',
        ),
        2 => 
        array (
          'customCode' => '{if $EDIT_RIGHTS_ONLY}<input title="{$MOD.LBL_VIEW_GANTT_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="EditProjectTasks" value="  {$MOD.LBL_VIEW_GANTT_TITLE}  " onclick="prep_edit_project_tasks(this.form);" />{/if}',
        ),
        3 => 
        array (
          'customCode' => '<input title="{$SAVE_AS}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="SaveAsTemplate" value="{$SAVE_AS}"{if $IS_TEMPLATE}onclick="prep_save_as_project(this.form)"{else}onclick="prep_save_as_template(this.form) {/if}"/>',
        ),
        4 => 
        array (
          'customCode' => '<input title="{$MOD.LBL_EXPORT_TO_MS_PROJECT}" class="button" type="submit" name="ExportToProject" value="  {$MOD.LBL_EXPORT_TO_MS_PROJECT}  " onclick="prep_export_to_project(this.form);"/>',
        ),
      ),
    ),
  ),
  'panels' => 
  array (
    'lbl_panel_1' => 
    array (
      0 => 
      array (
        0 => 
        array (
          'name' => 'name',
          'label' => 'LBL_NAME',
        ),
        1 => 
        array (
          'name' => 'status',
          'label' => 'LBL_STATUS',
        ),
      ),
      1 => 
      array (
        0 => 
        array (
          'name' => 'estimated_start_date',
          'label' => 'LBL_DATE_START',
        ),
        1 => 
        array (
          'name' => 'estimated_end_date',
          'label' => 'LBL_DATE_END',
        ),
      ),
      2 => 
      array (
        0 => 
        array (
          'name' => 'assigned_user_name',
          'label' => 'LBL_ASSIGNED_USER_ID',
        ),
        1 => 
        array (
          'name' => 'team_name',
        ),
      ),
      3 => 
      array (
        0 => 
        array (
          'name' => 'priority',
          'label' => 'LBL_PRIORITY',
        ),
      ),
      4 => 
      array (
        0 => 
        array (
          'name' => 'description',
          'label' => 'LBL_DESCRIPTION',
        ),
        1 => NULL,
      ),
      5 => 
      array (
        0 => 
        array (
          'name' => 'kiosk_kiosk_project_name',
        ),
      ),
    ),
  ),
);
?>
