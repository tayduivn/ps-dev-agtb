<?php
$viewdefs['Cases']['base']['layout']['list-dashboard'] = array (
  'metadata' => array (
    'components' => array (
      array (
        'rows' => array (
          array (
            array (
              'view' => array (
                'name' => 'dashablelist',
                'label' => 'My Assigned Bugs',
                'display_columns' => array (
                  'bug_number',
                  'name',
                  'status',
                ),
                'my_items' => '1',
                'display_rows' => 5,
                'status' => 'Assigned',
              ),
              'context' => array (
                'module' => 'Bugs',
              ),
              'width' => 12,
            ),
          ),
          array (
            array (
              'view' => array (
                'name' => 'twitter',
                'label' => 'Twitter Dashlet',
                'twitter' => 'sugarcrm',
                'limit' => '5',
              ),
              'context' => array (
                'module' => 'Home',
              ),
              'width' => 12,
            ),
          ),
        ),
        'width' => 12,
      ),
    ),
  ),
  'name' => 'My Dashboard',
);
