<?php
$layout_defs['Emails']['subpanel_setup']['contacts_snip'] =  array(
			'order' => 20,
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
	    	'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE_SNIP',
		    'set_subpanel_data' => 'contacts',
        	'module' => 'Contacts',
    		'subpanel_name' => 'ForEmailsByAddr',
            'get_subpanel_data' => 'function:get_beans_by_email_addr',
            'generate_select'=>true,
            'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'module'=>'Contacts'),
);
