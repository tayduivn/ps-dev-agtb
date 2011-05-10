<?php
$module_name = 'DCEClusters';
$viewdefs = array (
$module_name =>
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
      'javascript' => <<<EOQ
{literal}
<script type="text/javascript">
if(document.forms.EditView.record.value){
    document.getElementById('url').readOnly=true;
    document.getElementById('url_format').disabled=true;
}
</script>
{/literal}
EOQ
    ),
    'panels' => 
    array (
      'default' => 
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
            'name' => 'server_status',
            'label' => 'LBL_SERVER_STATUS',
            'type' => 'readonly',
          ),
        ),
        30 => 
        array (
          0 => 
          array (
            'name' => 'url',
            'label' => 'LBL_URL',
          ),
          1 => 
          array (
            'name' => 'url_format',
            'label' => 'LBL_URL_FORMAT',
          ),
        ),
        35 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        40 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
        ),
      ),
    ),
  ),
)
);
?>
