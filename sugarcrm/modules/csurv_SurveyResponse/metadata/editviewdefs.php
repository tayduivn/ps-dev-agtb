<?php
$module_name = 'csurv_SurveyResponse';
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
    ),
    'panels' => 
    array (
      'DEFAULT' => 
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
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
          1 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'case_relate',
            'label' => 'LBL_CASE_RELATE',
          ),
          1 => 
          array (
            'name' => 'contact_relate',
            'label' => 'LBL_CONTACT_RELATE',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'question_1',
            'label' => 'LBL_QUESTION_1',
          ),
          1 => 
          array (
            'name' => 'question_2',
            'label' => 'LBL_QUESTION_2',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'question_3',
            'label' => 'LBL_QUESTION_3',
          ),
          1 => 
          array (
            'name' => 'question_4',
            'label' => 'LBL_QUESTION_4',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'question_5',
            'label' => 'LBL_QUESTION_5',
          ),
          1 => 
          array (
            'name' => 'question_6',
            'label' => 'LBL_QUESTION_6',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'comments',
            'label' => 'LBL_COMMENTS',
          ),
        ),
      ),
    ),
  ),
)
);
?>
