<?php
$viewdefs ['ProjectTask'] = 
array (
  'EditView' => 
  array (
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
          'file' => 'modules/ProjectTask/ProjectTask.js',
        ),
      ),
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 
          array (
            'customCode' => '{if $FROM_GRID}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'Save\'; this.form.return_module.value=\'Project\'; this.form.return_action.value=\'EditGridView\'; this.form.return_id.value=\'{$project_id}\'; return check_form(\'EditView\');"	type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>{else}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'Save\'; return check_form(\'EditView\');" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if}&nbsp;',
          ),
          1 => 
          array (
            'customCode' => '{if $FROM_GRID}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="SUGAR.grid.closeTaskDetails()"; type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">{else}{if !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($fields.id.value))}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'DetailView\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">{elseif !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($smarty.request.return_id))}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'DetailView\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">{else}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'index\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">{/if}{/if}&nbsp;',
          ),
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
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
            'name' => 'project_task_id',
            'type' => 'readonly',
            'label' => 'LBL_TASK_ID',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'duration',
            'type' => 'readonly',
            'customCode' => '{$fields.duration.value}&nbsp;{$fields.duration_unit.value}',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'date_start',
            'type' => 'readonly',
          ),
          1 => 
          array (
            'name' => 'date_finish',
            'type' => 'readonly',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'customCode' => '<select name="{$fields.status.name}" id="{$fields.status.name}" title="" tabindex="s">{if isset($fields.status.value) && $fields.status.value != ""}{html_options options=$fields.status.options selected=$fields.status.value}{else}{html_options options=$fields.status.options selected=$fields.status.default}{/if}</select>',
          ),
          1 => 
          array (
            'name' => 'priority',
            'label' => 'LBL_PRIORITY',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'percent_complete',
        	'customCode' => '<input type="text" name="{$fields.percent_complete.name}" id="{$fields.percent_complete.name}" size="30" value="{$fields.percent_complete.value}" title="" tabindex="0" /></tr>',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'resource_id',
            'customCode' => '{$resource}',
            'label' => 'LBL_RESOURCE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'type' => 'readonly',
            'label' => 'LBL_TEAM',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'milestone_flag',
            'label' => 'LBL_MILESTONE_FLAG',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'project_name',
            'type' => 'readonly',
            'customCode' => '<a href="index.php?module=Project&action=DetailView&record={$fields.project_id.value}">{$fields.project_name.value}&nbsp;</a>',
            'label' => 'LBL_PROJECT_NAME',
          ),
          1 => 
          array (
            'name' => 'actual_duration',
            'customCode' => '<input id="actual_duration" type="text" tabindex="2" value="{$fields.actual_duration.value}" size="3" name="actual_duration"/>&nbsp;{$fields.duration_unit.value}',
            'label' => 'LBL_ACTUAL_DURATION',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'task_number',
            'label' => 'LBL_TASK_NUMBER',
          ),
          1 => 
          array (
            'name' => 'order_number',
            'label' => 'LBL_ORDER_NUMBER',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_USER_NAME',
          ),
          1 => '',
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'estimated_effort',
            'label' => 'LBL_ESTIMATED_EFFORT',
          ),
          1 => 
          array (
            'name' => 'utilization',
            'label' => 'LBL_UTILIZATION',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'rows' => '10',
              'cols' => '120',
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
