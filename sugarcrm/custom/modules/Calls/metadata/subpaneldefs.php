<?php
// remove the default leads subpanel and add the sub panel for the M2 Project
// jwhitcraft - 3.12.10
unset($layout_defs['Calls']['subpanel_setup']['leads']);
$layout_defs['Calls']['subpanel_setup']['leadcontacts'] = array(
			'order' => 30,
			'module' => 'LeadContacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForCalls',
			'get_subpanel_data' => 'leadcontacts',
			'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
			'top_buttons' => array(),
		);
// end jwhitcraft