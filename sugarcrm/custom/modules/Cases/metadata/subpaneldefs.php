<?php

// Added a new SubPanel to an upgrade safe location - jwhitcraft
$layout_defs['Cases']['subpanel_setup']['cases'] = array(
    'order' => 1,
    'module' => 'Cases',
    'sort_order' => 'desc',
    'sort_by' => 'case_number',
    'subpanel_name' => 'default',
    'get_subpanel_data' => 'function:get_open_cases',
    'function_paramters' => array(),
    'add_subpanel_data' => 'case_number',
    'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
    'top_buttons' => array(),
);
// end add new subpanel for an upgrade safe location - jwhitcraft