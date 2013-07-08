<?php
$viewdefs['Meetings']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'set_complete',
                    'width' => '1%',
                    'label' => 'LBL_LIST_CLOSE',
                    'link' => true,
                    'sortable' => false,
                    'default' => true,
                    'enabled' => true,
                    'related_fields' => array('status',),
                ),
                //BEGIN SUGARCRM flav!=com ONLY
                array(
                    'name' => 'join_meeting',
                    'label' => 'LBL_LIST_JOIN_MEETING',
                    'link' => true,
                    'sortable' => false,
                    'default' => false,
                    'enabled' => true,
                ),
                //END SUGARCRM flav!=com ONLY
                array(
                    'name' => 'name',
                    'label' => 'LBL_LIST_SUBJECT',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_LIST_CONTACT',
                    'link' => true,
                    'id' => 'CONTACT_ID',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'parent_name',
                    'label' => 'LBL_LIST_RELATED_TO',
                    'id' => 'PARENT_ID',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'sortable' => false,
                ),
                array(
                    'name' => 'date_start',
                    'label' => 'LBL_LIST_DATE',
                    'link' => false,
                    'default' => true,
                    'enabled' => true,
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name' => 'team_name',
                    'label' => 'LBL_LIST_TEAM',
                    'default' => false,
                    'enabled' => true,
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
                    'id' => 'ASSIGNED_USER_ID',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'direction',
                    'type' => 'enum',
                    'label' => 'LBL_LIST_DIRECTION',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'status',
                    'label' => 'LBL_LIST_STATUS',
                    'link' => false,
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'date_entered',
                    'width' => '10%',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => true,
                    'enabled' => true,
                ),  
            ),
        ),
    ),
);