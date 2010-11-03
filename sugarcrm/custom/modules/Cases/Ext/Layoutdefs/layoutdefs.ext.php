<?php 
 //WARNING: The contents of this file are auto-generated


$layout_defs["Cases"]["subpanel_setup"]["kbdocuments"] = array (
  'order' => 100,
  'module' => 'KBDocuments',
  'subpanel_name' => 'ForCases',
  'get_subpanel_data' => 'kbdocuments',
  'add_subpanel_data' => 'kbdocument_id',
  'title_key' => 'LBL_KBDOCUMENTS_SUBPANEL_TITLE',
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Cases']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";

//auto-generated file DO NOT EDIT
$layout_defs['Cases']['subpanel_setup']['bugs']['override_subpanel_name'] = 'Casedefault';

 

$layout_defs['Cases']['subpanel_setup']['projects'] =  array(
    'order' => 50,
    'module' => 'Project',
    'sort_order' => 'desc',
    'sort_by' => 'project_id',
    'subpanel_name' => 'default',
	'refresh_page' => 1,
    'get_subpanel_data' => 'projects',
    'add_subpanel_data' => 'project_id',
    'title_key' => 'LBL_PROJECTS_SUBPANEL_TITLE',
    'top_buttons' => array(
        array('widget_class' => 'SubPanelTopSelectButton'),
	),
);


 

$layout_defs['Cases']['subpanel_setup']['itrequests'] =  array(
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




$layout_defs['Cases']['subpanel_setup']['history']['collection_list']['linkedemails_contacts'] =  array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query_via_link',
	    		    'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'link' => 'contacts'),
	                'generate_select'=>true,
				    'get_distinct_data' => true,
);


/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add subpanel between Cases and User
*/

$layout_defs["Cases"]["subpanel_setup"]["users"] = array (
  'order' => 10,
  'module' => 'Users',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'last_name, first_name',
  'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
  'get_subpanel_data' => 'users',
  'add_subpanel_data' => 'id',
		'top_buttons' => array(
				array(
					'widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Cases',
					'mode' => 'MultiSelect',
				),
			),
);





// created: 2010-10-11 16:22:27
$layout_defs["Cases"]["subpanel_setup"]["e1_escalations_cases"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_E1_ESCALATIONS_CASES_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'e1_escalations_cases',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-10-11 16:24:26
$layout_defs["Cases"]["subpanel_setup"]["e1_escalations_cases_1"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_E1_ESCALATIONS_CASES_1_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'e1_escalations_cases_1',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


//auto-generated file DO NOT EDIT
$layout_defs['Cases']['subpanel_setup']['cases']['override_subpanel_name'] = 'Casedefault';


//auto-generated file DO NOT EDIT
$layout_defs['Cases']['subpanel_setup']['contacts']['override_subpanel_name'] = 'Casedefault';


//auto-generated file DO NOT EDIT
$layout_defs['Cases']['subpanel_setup']['kbdocuments']['override_subpanel_name'] = 'CaseForCases';


//auto-generated file DO NOT EDIT
$layout_defs['Cases']['subpanel_setup']['e1_escalations_cases_1']['override_subpanel_name'] = 'Casedefault';

?>