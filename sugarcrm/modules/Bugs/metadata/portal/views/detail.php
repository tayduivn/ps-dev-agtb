<?php
$viewdefs ['Bugs']['DetailView'] = 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        array (
          'label' => '10',
          'field' => '30',
        ),
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      array (
        'label' => 'default',
        'fields' => 
        array (
          array (
            'name' => 'bug_number',
            'displayParams' => 
            array (
              'colspan' => 2,
            ),
          ),
          array (
            'name' => 'status',
          ),
          array (
            'name' => 'priority',
          ),
          array (
            'name' => 'source',
          ),
          array (
            'name' => 'product_category',
          ),
          array (
            'name' => 'resolution',
          ),
          array (
            'name' => 'type',
          ),
          array (
            'name' => 'date_modified',
          ),
          array (
            'name' => 'modified_by_name',
          ),
          array (
            'name' => 'created_by_name',
          ),
          array (
            'name' => 'date_entered',
          ),
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'colspan' => 2,
            ),
          ),
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'colspan' => 2,
            ),
          ),
          array (
            'name' => 'work_log',
            'displayParams' => 
            array (
              'colspan' => 2,
            ),
          ),
        ),
      ),
    ),
);
?>
