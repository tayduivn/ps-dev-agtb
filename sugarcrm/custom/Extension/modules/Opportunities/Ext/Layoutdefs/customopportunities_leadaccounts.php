<?php
// created: 2009-09-14 16:18:10 #ITR 10318
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_leadaccounts"] = array (
 			'order' => 30,
			'module' => 'LeadAccounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'leadaccounts',
			'add_subpanel_data' => 'leadaccount_id',
			'title_key' => 'LBL_LEADACCOUNTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Opportunities',
					'mode' => 'MultiSelect', 
				),
			),
);
?>
