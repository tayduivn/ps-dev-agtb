<?php 
 //WARNING: The contents of this file are auto-generated


if (isset($layout_defs['ProspectLists']['subpanel_setup']) && isset($layout_defs['ProspectLists']['subpanel_setup']['leads'])) 
unset($layout_defs['ProspectLists']['subpanel_setup']['leads']);
	/*
	** @author: EDDY
	** SUGARINTERNAL CUSTOMIZATION
	** ITRequest #14114:
	** Description: keeping references to leadcontacts instead of leads
	*/
	$layout_defs['ProspectLists']['subpanel_setup']['leadcontacts'] = array(
		'order' => 30,
		'module' => 'LeadContacts',
		'sort_by' => 'last_name, first_name',
		'sort_order' => 'asc',
		'subpanel_name' => 'default',
		'get_subpanel_data' => 'leadcontacts',
		'title_key' => 'LBL_LEAD_CONTACTS_SUBPANEL_TITLE',
		'top_buttons' => array(
		    array('widget_class' => 'SubPanelTopButtonQuickCreate'),
			array('widget_class'=>'SubPanelTopSelectButton','mode'=>'MultiSelect'),
			array('widget_class'=>'SubPanelTopSelectFromReportButton'),
		),
	);


?>