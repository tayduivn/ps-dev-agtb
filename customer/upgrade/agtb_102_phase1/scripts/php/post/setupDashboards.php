<?php

$logger = LoggerManager::getLogger();

$sqlCommands = [
    "INSERT INTO dashboards (id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, dashboard_module, view_name, metadata, default_dashboard, team_id, team_set_id, acl_team_set_id, sync_key, assigned_user_id) VALUES('38525a7a-2570-11eb-aa7a-0242ac140007', 'Position Dashboard', '2020-11-13 05:22:27', '2020-11-13 06:42:51', '1', '1', '', 0, 'gtb_positions', 'record', '{\"dashlets\":[{\"context\":{\"module\":\"Contacts\",\"skipFetch\":true},\"view\":{\"module\":\"Contacts\",\"limit\":\"5\",\"display_columns\":[\"name\",\"title\",\"org_unit_c\",\"function_c\",\"gtb_cluster_c\"],\"label\":\"System Suggested Candidates\",\"type\":\"candidates-matching-dashlet\",\"last_state\":{\"id\":\"dashable-list\"},\"intelligent\":\"0\",\"filter_id\":\"all_records\",\"auto_refresh\":\"5\"},\"autoPosition\":false,\"x\":0,\"y\":0,\"width\":12,\"height\":6,\"id\":\"70fd2c20-9af7-4594-8f98-a06454e6d68d\"}]}', 1, '1', '1', NULL, NULL, '1');",
    "UPDATE dashboards SET name='LBL_HOME_DASHBOARD', date_entered='2020-11-13 03:11:49', date_modified='2020-11-13 15:50:38', modified_user_id='1', created_by='1', description='', deleted=0, dashboard_module='Home', view_name=NULL, metadata='{\"dashlets\":[{\"view\":{\"type\":\"dashablelist\",\"label\":\"New Candidates\",\"display_columns\":[\"name\",\"title\",\"org_unit_c\",\"function_c\",\"date_entered\"],\"limit\":15,\"module\":\"Contacts\",\"skipFetch\":true,\"last_state\":{\"id\":\"dashable-list\"},\"componentType\":\"view\",\"intelligent\":\"0\",\"filter_id\":\"recently_created\"},\"context\":{\"module\":\"Contacts\",\"link\":null,\"skipFetch\":true},\"width\":8,\"x\":0,\"y\":6,\"height\":5,\"id\":\"e8dce3d4-da2e-499b-bfe7-bfd33382f418\"},{\"view\":{\"limit\":10,\"visibility\":\"user\",\"label\":\"Active Tasks\",\"type\":\"active-tasks\",\"template\":\"tabbed-dashlet\",\"last_state\":{\"id\":\"0a209cbe-255e-11eb-bd98-0242ac140007:active-tasks\",\"defaults\":[]}},\"autoPosition\":false,\"x\":4,\"y\":0,\"width\":4,\"height\":6,\"id\":\"514b4058-c246-4283-b4e8-e7c3040d6c15\"},{\"context\":{\"module\":\"Activities\",\"skipFetch\":true},\"view\":{\"module\":\"Activities\",\"limit\":5,\"label\":\"Activity Stream\",\"type\":\"activitystream-dashlet\",\"auto_refresh\":0,\"currentFilterId\":\"all_records\"},\"autoPosition\":false,\"x\":8,\"y\":0,\"width\":4,\"height\":6,\"id\":\"aac8ff28-dd03-4e96-bd1f-49025220ba58\"},{\"view\":{\"limit\":\"10\",\"date\":\"today\",\"visibility\":\"user\",\"label\":\"Planned Activities\",\"type\":\"planned-activities\",\"template\":\"tabbed-dashlet\",\"last_state\":{\"id\":\"0a209cbe-255e-11eb-bd98-0242ac140007:planned-activities\",\"defaults\":[]}},\"autoPosition\":false,\"x\":0,\"y\":0,\"width\":4,\"height\":6,\"id\":\"0f80c1f9-c843-4d33-b80f-533b79e4425d\"},{\"context\":{\"module\":\"gtb_positions\",\"link\":null,\"skipFetch\":true},\"view\":{\"orderBy\":{\"field\":\"date_modified\",\"direction\":\"desc\"},\"label\":\"New Positions\",\"type\":\"dashablelist\",\"module\":\"gtb_positions\",\"last_state\":{\"id\":\"dashable-list\"},\"intelligent\":\"0\",\"limit\":5,\"filter_id\":\"recently_created\",\"display_columns\":[\"name\",\"pos_function\",\"region\",\"org_unit\",\"gtb_cluster\",\"date_entered\"],\"skipFetch\":true,\"componentType\":\"view\"},\"autoPosition\":false,\"x\":0,\"y\":11,\"width\":8,\"height\":5,\"id\":\"3294a220-7338-47df-b367-12fedc912894\"},{\"context\":{\"module\":\"gtb_matches\",\"link\":null},\"view\":{\"label\":\"Open Matches\",\"type\":\"saved-reports-chart\",\"module\":\"gtb_matches\",\"saved_report_id\":\"5e6fd772-2526-11eb-a4e0-0242ac140007\",\"saved_report\":\"Open Matches\",\"allowScroll\":true,\"colorData\":\"class\",\"hideEmptyGroups\":true,\"reduceXTicks\":true,\"rotateTicks\":true,\"show_controls\":false,\"show_title\":true,\"show_x_label\":true,\"show_y_label\":true,\"staggerTicks\":true,\"wrapTicks\":true,\"x_axis_label\":\"Stage\",\"y_axis_label\":\"Count Matches\",\"report_title\":\"Total is 2\",\"show_legend\":true,\"stacked\":true,\"allow_drillthru\":\"1\",\"vertical\":true,\"direction\":\"ltr\",\"skipFetch\":true,\"componentType\":\"view\"},\"autoPosition\":false,\"x\":8,\"y\":6,\"width\":4,\"height\":10,\"id\":\"60a98162-5302-4b6e-9f00-d859f6b9b1d4\"}]}', default_dashboard=1, team_id='1', team_set_id='1', acl_team_set_id=NULL, sync_key=NULL, assigned_user_id='1' WHERE dashboard_module = 'Home' and name = 'LBL_HOME_DASHBOARD'",
    "UPDATE dashboards SET name='Candidates List Dashboard', date_entered='2020-11-13 11:39:22', date_modified='2020-11-13 16:11:00', modified_user_id='1', created_by='1', description='', deleted=0, dashboard_module='Contacts', view_name='records', metadata='{\"dashlets\":[{\"context\":{\"module\":\"gtb_matches\",\"skipFetch\":true},\"view\":{\"label\":\"Open Matches\",\"type\":\"saved-reports-chart\",\"module\":\"gtb_matches\",\"saved_report_id\":\"5e6fd772-2526-11eb-a4e0-0242ac140007\",\"saved_report\":\"Open Matches\",\"allowScroll\":true,\"colorData\":\"class\",\"hideEmptyGroups\":true,\"reduceXTicks\":true,\"rotateTicks\":true,\"show_controls\":false,\"show_title\":true,\"show_x_label\":true,\"show_y_label\":true,\"staggerTicks\":true,\"wrapTicks\":true,\"x_axis_label\":\"Stage\",\"y_axis_label\":\"Count\",\"report_title\":\"Total is 2\",\"show_legend\":true,\"stacked\":true,\"allow_drillthru\":\"1\",\"vertical\":true,\"direction\":\"ltr\"},\"autoPosition\":false,\"x\":\"0\",\"y\":\"0\",\"width\":\"12\",\"height\":\"6\",\"id\":\"e1c77cef-d229-449c-8e34-1d5522915996\"}]}', default_dashboard=1, team_id='1', team_set_id='1', acl_team_set_id=NULL, sync_key=NULL, assigned_user_id='1' WHERE dashboard_module = 'Contacts' and view_name = 'records'",
];

$db = DBManagerFactory::getInstance();
foreach ($sqlCommands as $query) {
    $logger->debug('Executing SQL Command: ' . $query);
    $db->query($query);
}

$logger->debug('Finished Executing SQL Commands');
