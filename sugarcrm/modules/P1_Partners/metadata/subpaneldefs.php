<?php
//subpanel to show related interactions in OppQ screen
$layout_defs["P1_Partners"]["subpanel_setup"]["for_opp_q"] = array (
	'order' => 110,
	'module' => 'Interactions',
	'sort_order' => 'desc',
	'sort_by' => 'date_modified',
	'subpanel_name' => 'forOppQ',
	'get_subpanel_data'=>"function:get_related_interactions_query",
	 'function_parameters'=>array('bean_id'=>$this->_focus->id,'import_function_file'=>'custom/modules/Opportunities/get_related_interactions_query.php'),
	'title_key' => 'LBL_OPPORTUNITY_LEADCOMPANY_INTERACTION_SUBPANEL_TITLE',
	'top_buttons' => array(),
);

//subpanel to show related interactions in OppQ screen
$layout_defs["P1_Partners"]["subpanel_setup"]["history_for_oppq"] = array (
	'order' => 25,
	'sort_order' => 'desc',
	'sort_by' => 'date_modified',
	'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
	'type' => 'collection',
	'subpanel_name' => 'history',   //this values is not associated with a physical file.
	'module'=>'History',

	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopSummaryButton'),
	),

	'collection_list' => array(
			'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'meetings',
					'override_subpanel_name'=>'ForHistoryOppQ',

			),
			'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'tasks',
					'override_subpanel_name'=>'ForHistoryOppQ',

			),
			'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'calls',
					'override_subpanel_name'=>'ForHistoryOppQ',

			),
			'notes' => array(
					'module' => 'Notes',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'notes',
					'override_subpanel_name'=>'ForHistoryOppQ',

			),
			'emails' => array(
					'module' => 'Emails',
					'subpanel_name' => 'ForHistory',
					'override_subpanel_name'=>'ForHistoryOppQ',
					'get_subpanel_data' => 'emails',
			),
	)
);
?>
