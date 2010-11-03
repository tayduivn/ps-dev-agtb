<?php
if (isset($layout_defs['Meetings']['subpanel_setup']) && isset($layout_defs['Meetings']['subpanel_setup']['leads'])) unset($layout_defs['Meetings']['subpanel_setup']['leads']);
$layout_defs['Meetings']['subpanel_setup']['leadcontacts'] = array(
			'order' => 30,
			'module' => 'LeadContacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForMeetings',
			'get_subpanel_data' => 'leadcontacts',
			'title_key' => 'LBL_LEADCONTACTS_SUBPANEL_TITLE',
			'top_buttons' => array(	),
		);

