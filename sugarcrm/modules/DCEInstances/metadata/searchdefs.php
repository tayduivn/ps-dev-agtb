<?php
$module_name = 'DCEInstances';
$searchdefs = array (
$module_name =>
array (
  'templateMeta' => 
  array (
    'maxColumns' => '3',
    'maxColumnsBasic' => '4',
    'widths' => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
  'layout' => 
  array (
'basic_search' => 
    array (
      0 => 'name',
      1 => 
      array (
        'name' => 'current_user_only',
        'label' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
      ),
    ),
'advanced_search' => 
     array (
      'name' => 
      array (
        'name' => 'name',
     ),
      array(
        'name' => 'account_name',
        'label' => 'LBL_ACCOUNT', 
        'displayParams' => array(
                            'hideButtons'=>'true', 
                            'size'=>30, 
                            'class'=>'sqsEnabled sqsNoAutofill'
                            )
     ),
      array(
        'name' => 'dcecluster_name',
        'label' => 'LBL_CLUSTER', 
        'displayParams' => array(
                            'hideButtons'=>'true', 
                            'size'=>30, 
                            'class'=>'sqsEnabled sqsNoAutofill'
                            )
     ),
      array(
        'name' => 'dcetemplate_name',
        'label' => 'LBL_TEMPLATE', 
        'displayParams' => array(
                            'hideButtons'=>'true', 
                            'size'=>30, 
                            'class'=>'sqsEnabled sqsNoAutofill'
                            )
     ),
     'sugar_version' => 
      array(
        'label' => 'LBL_SUGAR_VERSION',
        'name' => 'sugar_version', 
      ),
      'sugar_edition' => 
      array( 
        'label' => 'LBL_SUGAR_EDITION',
        'name' => 'sugar_edition', 
      ),
      'status' => 
      array (
        'label' => 'LBL_STATUS',
        'name' => 'status',
      ),
      'type' => 
      array (
        'label' => 'LBL_TYPE',
        'name' => 'type',
      ),
      'license_start_date' => 
      array (
        'label' => 'LBL_LICENSE_START_DATE',
        'name' => 'license_start_date',
      ),
      'license_duration' => 
      array (
        'label' => 'LBL_LICENSE_DURATION',
        'name' => 'license_duration',
      ),
      'assigned_user_id' => 
      array (
        'name' => 'assigned_user_id',
        'label' => 'LBL_ASSIGNED_TO',
        'type' => 'enum',
        'function' => 
        array (
          'name' => 'get_user_array',
          'params' => 
          array (
            0 => false,
          ),
        ),
      ),
/*      'licensed_users' => 
      array (
        'label' => 'LBL_LICENSED_USERS',
        'name' => 'licensed_users',
      ),
      'last_accessed' => 
      array (
        'label' => 'LBL_LAST_ACCESSED',
        'name' => 'last_accessed',
      ),*/
    ),
'dceupgrade_search' => 
    array (
      'dcetemplate_name' => 
      array(
        'width' => '10', 
        'label' => 'LBL_TEMPLATE',
        'name' => 'dcetemplate_name', 
        'displayParams'=>array( 'readOnly'=>'true',
                                'field_to_name_array' => array(
                                    'id' => 'dcetemplate_id_dceupgrade',
                                    'name' => 'dcetemplate_name_dceupgrade',
                                ),
                                'useIdSearch'=>'true',
                            ),
      ),
      'name' => 
      array (
        'name' => 'name',
      ),
      'upgrade_searchForm'=>
      array (
        'name' => 'upgrade_searchForm',
        'displayParams'=>array('field'=>array('style'=>'display:none')),
      ),
    ),
  ),
)
);
?>
