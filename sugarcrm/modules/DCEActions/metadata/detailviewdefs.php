<?php
$module_name = 'DCEActions';
$viewdefs = array (
$module_name =>
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
//          0 => 'EDIT',
//          1 => 'DUPLICATE',
//          0 => 'DELETE',
          array('customCode'=>'<input title="{$APP.LBL_DCERESTARTACTION_LABEL}" class="button" type="submit" name="DCERestartAction" value="{$APP.LBL_DCERESTARTACTION_LABEL}"  id="dcerestartaction_button" onclick="this.form.return_module.value=\'DCEActions\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'RestartAction\';">',
              //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
              'sugar_html' => array(
                  'type' => 'submit',
                  'value' => '{$APP.LBL_DCERESTARTACTION_LABEL}',
                  'htmlOptions' => array(
                      'title' => '{$APP.LBL_DCERESTARTACTION_LABEL}',
                      'class' => 'button',
                      'name' => 'DCERestartAction',
                      'id' => 'dcerestartaction_button',
                      'onclick' => 'this.form.return_module.value=\'DCEActions\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'RestartAction\';',
                  ),
              ),
          ),
          array('customCode'=>'<input title="{$APP.LBL_DCERESTARTEMAIL_LABEL}" class="button"  type="submit" name="DCERestartEmail" value="{$APP.LBL_DCERESTARTEMAIL_LABEL}"  id="dcerestartemail_button" onclick="this.form.return_module.value=\'DCEActions\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'RestartEmail\';">',
              //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
              'sugar_html' => array(
                  'type' => 'submit',
                  'value' => '{$APP.LBL_DCERESTARTEMAIL_LABEL}',
                  'htmlOptions' => array(
                      'title' => '{$APP.LBL_DCERESTARTEMAIL_LABEL}',
                      'class' => 'button',
                      'name' => 'DCERestartEmail',
                      'id' => 'dcerestartemail_button',
                      'onclick' => 'this.form.return_module.value=\'DCEActions\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'RestartEmail\';',
                  ),
              ),
          ),
             
           
        ),
      ),
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
    ),
    'panels' => 
    array (
      '' => 
      array (
        10 => 
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
        30 => 
        array (
          0 => 
          array (
            'name' => 'cluster_name',
            'label' => 'LBL_CLUSTER_NAME',
          ),
          1 => 
          array (
            'name' => 'type',
            'label' => 'LBL_TYPE',
          ),
        ),
        35 => 
        array (
          0 => 
          array (
            'name' => 'template_name',
            'label' => 'LBL_TEMPLATE_NAME',
          ),
          1 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        36 => 
        array (
          0 => 
          array (
            'name' => 'instance_name',
            'label' => 'LBL_INSTANCE_NAME',
          ),
          1 => 
          array (
            'name' => 'date_started',
            'label' => 'LBL_DATE_STARTED',
          ),
        ),
        40 => 
        array (
          0 => 
          array (
            'name' => 'client_name',
            'label' => 'LBL_NODE',
          ),
          1 => 
          array (
            'name' => 'date_completed',
            'label' => 'LBL_DATE_COMPLETED',
          ),
        ),
        50 => 
        array(
            array (
                'name' => 'logs',
                'label' => 'LBL_LOGS',
            )
        ),
      ),
    ),
  ),
)
);
?>
