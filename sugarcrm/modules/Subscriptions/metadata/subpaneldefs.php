<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Subscriptions'] = array(
	'subpanel_setup' => array(
        // ITR:14106 - jwhitcraft - remove these subpanels as the modules don't exist any more
		/*'orders' => array(
			'order' => 10,
			'module' => 'Orders',
			'sort_order' => 'desc',
			'sort_by' => 'order_number',
			'get_subpanel_data' => 'orders',
			'add_subpanel_data' => 'id',
			'subpanel_name' => 'default',
			'title_key' => 'LBL_ORDERS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				//array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
		'portalusers' => array(
			'order' => 20,
			'module' => 'PortalUsers',
			'sort_order' => 'asc',
			'sort_by' => 'user_name',
			'get_subpanel_data' => 'portalusers',
			'add_subpanel_data' => 'id',
			'subpanel_name' => 'default',
			'title_key' => 'LBL_PORTALUSERS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				//array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),*/
        // end ITR: 14106
		'distgroups' => array(
			'order' => 30,
			'module' => 'DistGroups',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'get_subpanel_data' => 'distgroups',
			'add_subpanel_data' => 'distgroup_id',
			'subpanel_name' => 'ForSubscriptions',
			'title_key' => 'LBL_DISTGROUPS_SUBPANEL_TITLE',
			'top_buttons' => array(
				//array('widget_class' => 'SubPanelTopButtonQuickCreate'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
		),
		'history' => array(
                        'order' => 30,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_modified',
                        'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
                        'type' => 'collection',
                        'subpanel_name' => 'history',   //this values is not associated with a physical file.
                        'module'=>'History',

                        'top_buttons' => array(
                                array('widget_class' => 'SubPanelTopCreateNoteButton'),
                                array('widget_class' => 'SubPanelTopSummaryButton'),
                        ),

                        'collection_list' => array(
                                'meetings' => array(
                                        'module' => 'Meetings',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'meetings',
                                ),
                                'tasks' => array(
                                        'module' => 'Tasks',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'tasks',
                                ),
                                'notes' => array(
                                        'module' => 'Notes',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'notes',
                                ),
                        )
                ),

	),
);

?>
