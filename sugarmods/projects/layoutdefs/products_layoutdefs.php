<?php 
// FILE SUGARCRM flav=pro ONLY 
$layout_defs['Products']['subpanel_setup']['projects'] = array(
    'order' => 40,
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