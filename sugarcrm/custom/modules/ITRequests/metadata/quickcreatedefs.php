<?php
$viewdefs ['ITRequests'] = 
array (
  'QuickCreate' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'form' => 
      array (
        'headerTpl' => 'modules/ITRequests/tpls/header.tpl',
      ),
      'widths' => 
      array (
        0 => 
        array (
          'label' => '15',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '15',
          'field' => '30',
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
            'name' => 'department_c',
            'studio' => 'visible',
            'label' => 'LBL_DEPARTMENT',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'department_category_c',
            'studio' => 'visible',
            'label' => 'LBL_DEPARTMENT_CATEGORY',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'size' => 100,
              'required' => true,
            ),
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'rows' => 12,
              'cols' => 120,
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
