<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-02 19:32:01
$dictionary["ps_Timesheets"]["fields"]["ps_timesheets_tasks"] = array (
  'name' => 'ps_timesheets_tasks',
  'type' => 'link',
  'relationship' => 'ps_timesheets_tasks',
  'source' => 'non-db',
  'vname' => 'LBL_PS_TIMESHEETS_TASKS_FROM_TASKS_TITLE',
);
$dictionary["ps_Timesheets"]["fields"]["ps_timesheets_tasks_name"] = array (
  'name' => 'ps_timesheets_tasks_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_PS_TIMESHEETS_TASKS_FROM_TASKS_TITLE',
  'save' => true,
  'id_name' => 'ps_timeshed71akstasks_ida',
  'link' => 'ps_timesheets_tasks',
  'table' => 'tasks',
  'module' => 'Tasks',
  'rname' => 'name',
);
$dictionary["ps_Timesheets"]["fields"]["ps_timeshed71akstasks_ida"] = array (
  'name' => 'ps_timeshed71akstasks_ida',
  'type' => 'link',
  'relationship' => 'ps_timesheets_tasks',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_PS_TIMESHEETS_TASKS_FROM_PS_TIMESHEETS_TITLE',
);

?>