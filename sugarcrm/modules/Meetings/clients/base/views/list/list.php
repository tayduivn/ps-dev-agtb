<?php
$viewdefs['Meetings']['base']['view']['list'] = array(
    'panels' => array(
        array(
        'label' => 'LBL_PANEL_1',
        'fields' => array(
  //BEGIN SUGARCRM flav!=com ONLY
          array (
            'name' => 'join_meeting',
            'label' => 'LBL_LIST_JOIN_MEETING',
            'link' => true,
            'sortable' => false,
            'default' => true,
          ),
  //END SUGARCRM flav!=com ONLY
          array (
            'name' => 'name',
            'label' => 'LBL_LIST_SUBJECT',
            'link' => true,
            'default' => true,
          ),
          array (
            'name' => 'contact_name',
            'label' => 'LBL_LIST_CONTACT',
            'link' => true,
            'id' => 'CONTACT_ID',
            'default' => true,
          ),
          array (
            'name' => 'parent_name',
            'label' => 'LBL_LIST_RELATED_TO',
            'id' => 'PARENT_ID',
            'link' => true,
            'default' => true,
            'sortable' => false,
          ),
          array (
            'name' => 'date_start',
            'label' => 'LBL_LIST_DATE',
            'link' => false,
            'default' => true,
          ),
  //BEGIN SUGARCRM flav=pro ONLY
          array(
            'name' => 'team_name',
            'label' => 'LBL_LIST_TEAM',
            'default' => false
          ),        
  //END SUGARCRM flav=pro ONLY
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
            'id' => 'ASSIGNED_USER_ID',
            'default' => true,
          ),
          array (
            'name' => 'direction',
            'type' => 'enum',
            'label' => 'LBL_LIST_DIRECTION',
            'default' => false,
          ),
          array (
            'name' => 'status',
            'label' => 'LBL_LIST_STATUS',
            'link' => false,
            'default' => false,
          ),
        ),
            ),
    ),
);