<?php
$layout_defs['Cases']['subpanel_setup']['history']['collection_list']['linkedemails_contacts'] =  array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query_via_link',
	    		    'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'link' => 'contacts'),
	                'generate_select'=>true,
				    'get_distinct_data' => true,
);
