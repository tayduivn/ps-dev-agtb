<?php 

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

?>