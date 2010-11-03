<?php 

$layout_defs['Bugs']['subpanel_setup']['itrequests'] =  array(
            'order' => 83,
            'module' => 'ITRequests',
            'sort_order' => 'asc',
            'sort_by' => 'date_modified',
            'get_subpanel_data' => 'itrequests',
            'add_subpanel_data' => 'itrequest_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_ITREQUESTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
		);

?>