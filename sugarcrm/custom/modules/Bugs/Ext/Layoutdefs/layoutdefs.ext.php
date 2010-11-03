<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2008-10-06 05:00:56
$layout_defs["Bugs"]["subpanel_setup"]["bugs_e1_escalations"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_BUGS_E1_ESCALATIONS_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'bugs_e1_escalations',
);


// created: 2009-06-08 17:15:59
$layout_defs["Bugs"]["subpanel_setup"]["bugs_bugs"] = array (
  'order' => 100,
  'module' => 'Bugs',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_BUGS_BUGS_FROM_BUGS_TITLE',
  'get_subpanel_data' => 'bugs_bugs',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'Bugs',
      'mode' => 'MultiSelect',
    ),
  ),
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Bugs']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";

// created: 2008-10-06 05:00:56
$layout_defs["Bugs"]["subpanel_setup"]["bugs_e1_escalations"] = array (
  'order' => 100,
  'module' => 'E1_Escalations',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_BUGS_E1_ESCALATIONS_FROM_E1_ESCALATIONS_TITLE',
  'get_subpanel_data' => 'bugs_e1_escalations',
);


 

$layout_defs['Bugs']['subpanel_setup']['projects'] =  array(
    'order' => 60,
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


 

$layout_defs['Bugs']['subpanel_setup']['itrequests'] =  array(
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



// created: 2009-11-18 15:33:24
$layout_defs["Bugs"]["subpanel_setup"]["spec_usecases_bugs"] = array (
  'order' => 100,
  'module' => 'Spec_UseCases',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SPEC_USECASES_BUGS_FROM_SPEC_USECASES_TITLE',
  'get_subpanel_data' => 'spec_usecases_bugs',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);



/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$layout_defs["Bugs"]["subpanel_setup"]["users"] = array (
  'order' => 4,
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
					'popup_module' => 'Bugs',
					'mode' => 'MultiSelect',
				),
			),
);





//auto-generated file DO NOT EDIT
$layout_defs['Bugs']['subpanel_setup']['bugs_bugs']['override_subpanel_name'] = 'Bugdefault';

?>