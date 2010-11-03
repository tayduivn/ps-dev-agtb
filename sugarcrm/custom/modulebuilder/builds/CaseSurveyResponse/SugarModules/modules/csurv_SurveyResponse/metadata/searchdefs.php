<?php
$module_name = 'csurv_SurveyResponse';
$searchdefs = array (
$module_name =>
array (
  'templateMeta' => 
  array (
    'maxColumns' => '3',
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
      'date_entered' => 
      array (
        'label' => 'LBL_DATE_ENTERED',
        'width' => '10',
        'name' => 'date_entered',
      ),
      'case_relate' => 
      array (
        'label' => 'LBL_CASE_RELATE',
        'width' => '10',
        'name' => 'case_relate',
      ),
      'contact_relate' => 
      array (
        'label' => 'LBL_CONTACT_RELATE',
        'width' => '10',
        'name' => 'contact_relate',
      ),
      'question_1' => 
      array (
        'label' => 'LBL_QUESTION_1',
        'width' => '10',
        'name' => 'question_1',
      ),
      'question_2' => 
      array (
        'label' => 'LBL_QUESTION_2',
        'width' => '10',
        'name' => 'question_2',
      ),
      'question_3' => 
      array (
        'label' => 'LBL_QUESTION_3',
        'width' => '10',
        'name' => 'question_3',
      ),
      'question_4' => 
      array (
        'label' => 'LBL_QUESTION_4',
        'width' => '10',
        'name' => 'question_4',
      ),
      'question_5' => 
      array (
        'label' => 'LBL_QUESTION_5',
        'width' => '10',
        'name' => 'question_5',
      ),
      'question_6' => 
      array (
        'label' => 'LBL_QUESTION_6',
        'width' => '10',
        'name' => 'question_6',
      ),
      'comments' => 
      array (
        'label' => 'LBL_COMMENTS',
        'width' => '10',
        'name' => 'comments',
      ),
    ),
  ),
)
);
?>
