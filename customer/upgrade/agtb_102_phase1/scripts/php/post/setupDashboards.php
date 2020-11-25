<?php

$logger = LoggerManager::getLogger();

$sqlCommands = [
    "INSERT INTO dashboards (id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, dashboard_module, view_name, metadata, default_dashboard, team_id, team_set_id, acl_team_set_id, sync_key, assigned_user_id) VALUES('38525a7a-2570-11eb-aa7a-0242ac140007', 'Position Dashboard', '2020-11-13 05:22:27', '2020-11-13 06:42:51', '1', '1', '', 0, 'gtb_positions', 'record', '{\"dashlets\":[{\"context\":{\"module\":\"Contacts\",\"skipFetch\":true},\"view\":{\"module\":\"Contacts\",\"limit\":\"5\",\"display_columns\":[\"name\",\"title\",\"org_unit_c\",\"function_c\",\"gtb_cluster_c\"],\"label\":\"System Suggested Candidates\",\"type\":\"candidates-matching-dashlet\",\"last_state\":{\"id\":\"dashable-list\"},\"intelligent\":\"0\",\"filter_id\":\"all_records\",\"auto_refresh\":\"5\"},\"autoPosition\":false,\"x\":0,\"y\":0,\"width\":12,\"height\":6,\"id\":\"70fd2c20-9af7-4594-8f98-a06454e6d68d\"}]}', 1, '1', '1', NULL, NULL, '1');",
];

$db = DBManagerFactory::getInstance();
foreach ($sqlCommands as $query) {
    $logger->debug('Executing SQL Command: ' . $query);
    $db->query($query);
}

$logger->debug('Finished Executing SQL Commands');
