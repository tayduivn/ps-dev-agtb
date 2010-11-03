<?php
$layout_defs["KBDocuments"]["subpanel_setup"]["cases2"] = array (
  'order' => 100,
  'module' => 'Cases',
  'subpanel_name' => 'ForKB',
  'get_subpanel_data' => 'cases2',
  'add_subpanel_data' => 'case_id',
  'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
    // provide a way to pull in the subpanel from the custom dir so it's upgrade safe
    // jwhitcraft - 3.23.10
  'override_subpanel_name' => 'ForKB'
    // end upgrade safe customization
);

