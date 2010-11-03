<?php

// BEGIN jostrow customization
// See ITRequest #15964 -- temporary fix to prevent Pardot scripts from running indefinitely

set_time_limit(3600 * 4);

// END jostrow customization

chdir(dirname(__FILE__));
require_once('pid_functions.php');
script_make_pid('pardot_prospects_sync_activities');
register_shutdown_function('script_clear_pid', 'pardot_prospects_sync_activities');

$get_existing_max = false;
$dry_run = false;
ob_start();
require_once('pardotApi.class.php');


chdir('../..');
define('sugarEntry', true);

// BEGIN jostrow customization
// Temporarily log memory consumption
require_once('scripts/jostrow_log_memory_usage.php');
// END jostrow customization


require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');

global $app_list_strings;
$app_list_strings = return_app_list_strings_language('en_us');


/* pluto is used by all forms on sugarcrm.com */
$assigned_user_name = 'Leads_HotMktg';
$assigned_user_pass = '9139a90b1bd94dc57d8b57a5815a2353';
$assigned_user = new User();
$assigned_user_id = $assigned_user->retrieve_user_id($assigned_user_name);

$current_user = new User();
$user_id = $current_user->retrieve_user_id('admin');
$current_user->retrieve($user_id);

$pardot = pardotApi::magic();

$max_id = 0;
$downloaded = 0;
$lastResultCount = 0;
$runaway_stop = 1000;
$error = 0;

$get_last_activity_sync = "
SELECT
    value
FROM
    config
WHERE
    category = 'pardot' and name = 'last_activity_sync'
";

$last_activity_sync = '2009-12-08 19:00:00';
$activity_sync_insert = true;
if ($res = $GLOBALS['db']->query($get_last_activity_sync)) {
    $row = $GLOBALS['db']->fetchByAssoc($res);
    if (!empty($row['value'])) {
        $last_activity_sync = $row['value'];
        $activity_sync_insert = false;
    }
}

if ($get_existing_max) {
    $max_id_query = "
SELECT
    MAX(`touchpoints_cstm`.`prospect_id_c`) as `max_id`
FROM
    `touchpoints_cstm`
";

    $res = $GLOBALS['db']->query($max_id_query);
    if (!$res) {
        /*
       * If there is a problem with the query, quick early and make a lot of
       * noise in the logs.
       */
        $GLOBALS['db']->checkError('Error with query');
        $GLOBALS['log']->fatal("----->siProspectsToTouchpoints.php query failed: " . $touchpoints_query);
        echo "Error with query\n";
        exit(1);
    } else {
        /*
       * The query did not bomb, so put all of the opportunity ids into an
       * array for future processing.
       */
        while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
            $max_id = $row['max_id'];
        }
    }
}

$ids_to_add = array();
do {
    /*
     * Get the first group of 200
     */
    $criteria = array('last_activity_after' => $last_activity_sync);
    if ($max_id) {
        $criteria['id_greater_than'] = $max_id;
    }
    $prospects = $pardot->getProspectsWhere($criteria, 'mobile', array('id'));

    $lastResultCount = $pardot->getLastResultCount();
    $retrieved_prospects = count($prospects);

    if ($retrieved_prospects) {
        $GLOBALS['db']->query("select 1"); // Just to make sure the database connection doesn't time out
        # echo "Harvested $retrieved_prospects prospects out of $lastResultCount\n";
        $downloaded += $retrieved_prospects;
        $prospect_ids = array();
        foreach ($prospects as $prospect) {
            $prospect_ids[] = intval($prospect['id']);;
            
            unset($prospect);
        }
        // set the max_id before we unset prospects
        $max_id = max(array_keys($prospects));
        unset($prospects);

        $touchpoints_query = "
SELECT
    `touchpoints_cstm`.`id_c`,
    `touchpoints_cstm`.`prospect_id_c`
FROM
    `touchpoints_cstm`
INNER JOIN
    `touchpoints` ON
        `touchpoints`.id = `touchpoints_cstm`.`touchpoints_id`
    AND
        `touchpoints`.`scrubbed` = 1
    AND
        `touchpoints`.`assigned_user_id` <> '21030676-7f66-df76-8afb-44adcda44c25'
    AND
        `touchpoints`.`deleted` = 0
WHERE
    `touchpoints_cstm`.`prospect_id_c` IN (" . join(', ', $prospect_ids) . ")
";

        $res = $GLOBALS['db']->query($touchpoints_query);
        if (!$res) {
            /*
            * If there is a problem with the query, quick early and make a lot of
            * noise in the logs.
            */
            $GLOBALS['db']->checkError('Error with query');
            $GLOBALS['log']->fatal("----->siProspectsToTouchpoints.php query failed: " . $touchpoints_query);
            echo "Error with query $touchpoints_query\n";
            exit(1);
        } else {
            /*
            * The query did not bomb, so put all of the opportunity ids into an
            * array for future processing.
            */
            while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
                $touchpoint_id = $row['id_c'];
                $prospect_id_c = $row['prospect_id_c'];
                $ids_to_add[$prospect_id_c] = $touchpoint_id;
            }
            unset($res); // clear out some memory
            $GLOBALS['db']->query('select 1'); // Hack used just to maintain the database connection. without it, if this loop takes too long, we lose it
        }
    } else {
        # echo "No prospects retrieved\n";
    }
    // echo "downloaded: $downloaded lastResultCount $lastResultCount retrieved_prospects $retrieved_prospects\n";
} while ($lastResultCount && $retrieved_prospects && (0 < $runaway_stop--));

foreach ($ids_to_add as $prospect_id_c => $touchpoint_id) {
    $command = 'php scripts/pardot/syncProspectActivities.php '
            . escapeshellarg($touchpoint_id);
    $output = shell_exec($command);
    $GLOBALS['db']->query('select 1'); // Hack used just to maintain the database connection. without it, if this loop takes too long, we lose it
    // echo $command."\n";
    if (!empty($output)) {
        echo "Error syncing activities from prospect id $prospect_id_c to touchpoint id $touchpoint_id\n";
        echo $output . "\n";
        $error = 1;
    }
}

if ($activity_sync_insert) {
    $activity_sync_query = "insert into config set category = 'pardot', name = 'last_activity_sync', value = FROM_UNIXTIME(UNIX_TIMESTAMP(CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 10 MINUTE), 'SYSTEM', '-5:00')))";
}
else {
    $activity_sync_query = "update config set value = FROM_UNIXTIME(UNIX_TIMESTAMP(CONVERT_TZ(DATE_SUB(NOW(), INTERVAL 10 MINUTE), 'SYSTEM', '-5:00'))) where category = 'pardot' and name = 'last_activity_sync'";
}
$GLOBALS['db']->query($activity_sync_query);

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
    echo $whatevs;
}

if (!$dry_run) {
}

if ($error) {
    die($error);
}
