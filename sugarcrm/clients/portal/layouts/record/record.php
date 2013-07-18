<?php
$viewdefs['portal']['layout']['record'] = array (
  'components' => array (
    array (
      'layout' => array (
        'components' => array (
          array (
            'layout' => array (
              'components' => array (
                array (
                  'view' => 'record',
                ),
                array (
                  'view' => 'activity',
                  'context' => array (
                    'link' => 'notes',
                  ),
                ),
                array (
                  'view' => 'editmodal',
                  'context' => array (
                    'link' => 'notes',
                  ),
                ),
              ),
              'type' => 'simple',
              'name' => 'main-pane',
              'span' => 8,
            ),
          ),
          array (
            'layout' => array (
              'components' => array (
              ),
              'type' => 'simple',
              'name' => 'side-pane',
              'span' => 4,
            ),
          ),
          array (
            'layout' => array (
              'components' => array (
              ),
              'type' => 'simple',
              'name' => 'dashboard-pane',
              'span' => 4,
            ),
          ),
          array (
            'layout' => array (
              'components' => array (
                array (
                  'layout' => 'preview',
                ),
              ),
              'type' => 'simple',
              'name' => 'preview-pane',
              'span' => 8,
            ),
          ),
        ),
        'type' => 'default',
        'name' => 'sidebar',
        'span' => 12,
      ),
    ),
  ),
  'type' => 'simple',
  'name' => 'base',
  'span' => 12,
);
