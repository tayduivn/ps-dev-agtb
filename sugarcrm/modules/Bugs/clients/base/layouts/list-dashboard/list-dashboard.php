<?php
$viewdefs['Bugs']['base']['layout']['list-dashboard'] = array (
  'metadata' => array (
    'components' => array (
      array (
        'rows' => array (
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
