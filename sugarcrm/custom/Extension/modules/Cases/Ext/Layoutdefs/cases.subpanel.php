<?php
$layout_defs['Cases']['subpanel_setup']['contact_history'] =  array(
        			'order' => 21,
		        	'sort_order' => 'desc',
        			'sort_by' => 'date_entered',
        			'title_key' => 'LBL_CONTACT_HISTORY_SUBPANEL_TITLE',
					'module' => 'Emails',
	                'subpanel_name' => 'ForContactHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query_via_link',
	    		    'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'link' => 'contacts'),
	                'generate_select'=>true,
				    'get_distinct_data' => true,
                    'top_buttons' => array(),
);
