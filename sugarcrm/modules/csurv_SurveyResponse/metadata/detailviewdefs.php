<?php
$module_name = 'csurv_SurveyResponse';
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
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
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
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
          1 => NULL,
        ),
        3 => 
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
        4 => 
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
        5 => 
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
        6 => 
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
        7 => 
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
