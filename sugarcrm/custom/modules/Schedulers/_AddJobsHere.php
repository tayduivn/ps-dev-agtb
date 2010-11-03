<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Set up an array of Jobs with the appropriate metadata
 * 'jobName' => array (
 * 		'X' => 'name',
 * )
 * 'X' should be an increment of 1
 * 'name' should be the EXACT name of your function
 *
 * Your function should not be passed any parameters
 * Always  return a Boolean. If it does not the Job will not terminate itself
 * after completion, and the webserver will be forced to time-out that Job instance.
 * DO NOT USE sugar_cleanup(); in your function flow or includes.  this will
 * break Schedulers.  That function is called at the foot of cron.php
 */

/**
 * This array provides the Schedulers admin interface with values for its "Job"
 * dropdown menu.
 */
$job_strings[10] = "zeroOutExpiredLearningCredits";
$job_strings[11] = "associateSubscriptionsAndAccounts";
$job_strings[13] = "rescoreIncremental";
$job_strings[14] = "rescoreTotal";

/**
 * Job 10
 */
function zeroOutExpiredLearningCredits() {
    $GLOBALS['log']->info('----->Scheduler fired job of type zeroOutExpiredLearningCredits()');
    $db = DBManagerFactory::getInstance();

    /* This is the query to get the account numbers */
    $query = "
SELECT
    `id`,
    `remaining_training_credits_c`,
    `training_credits_exp_date_c`
FROM
    `accounts`
INNER JOIN
    `accounts_cstm` ON `accounts`.`id` = `accounts_cstm`.`id_c`
WHERE
    `remaining_training_credits_c` > 0
    AND `training_credits_exp_date_c` < now()
    AND `accounts`.`deleted` = 0
ORDER BY
    `id`
";


    /* Get the list from the database */
    $r = $db->query($query);
    $rows = array();
    $accounts = array();
    while ($row = $db->fetchByAssoc($r)) {
        $rows[] = $row;
        $accounts[$row['id']] = $row;
    }

    /* We can use the extra info for debugging, but we don't need it for this
     * script, so we prepare a simpler array to iterate over.
     */
    $accounts_with_expired_credits = array();
    foreach ($rows as $row) {
        $accounts_with_expired_credits[] = $row['id'];
    }
    sort($accounts_with_expired_credits);
    $accounts_with_expired_credits = array_unique($accounts_with_expired_credits);
    $row_count = count($accounts_with_expired_credits);

    $GLOBALS['log']->info('----->Scheduler Attempting to expire credits in ' . $row_count . 'accounts.');
    /* This is where we do the actual work.  Each account is loaded, adjusted, and
     * saved. */
    $previewed = 0;
    $saved = 0;
    $errors = 0;
    $audit_dry_ran = 0;
    $audited = 0;
    $date_created = date('Y-m-d H:i:s');
    $field_name = 'remaining_training_credits_c';
    $after_value_string = '0';
    foreach ($accounts_with_expired_credits as $account_id) {
        if (isset($accounts[$account_id])) {
            $before_value_string = $accounts[$account_id]['remaining_training_credits_c'];
        } else {
            $before_value_string = '0';
        }
        $guid = create_guid();
        $query = "
INSERT INTO `accounts_audit`
    (`id`,
     `parent_id`,
     `date_created`,
     `created_by`,
     `field_name`,
     `data_type`,
     `before_value_string`,
     `after_value_string`
    )
VALUES
    ('$guid',
     '$account_id',
     '$date_created',
     '1',
     '$field_name',
     'int',
     '$before_value_string',
     '$after_value_string')
";
        $r = $db->query($query);
        $r || $errors++;
        $audited++;

        $query = "
UPDATE `accounts_cstm`
SET `remaining_training_credits_c` = '0'
WHERE `id_c` = '$account_id'
LIMIT 1
";
        $r = $db->query($query);
        $r || $errors++;
        $saved++;
    }
    $GLOBALS['log']->info("----->Scheduler Zeroed expired credits on $saved accounts");
    return true;

}


/**
 * Job 11
 */
function associateSubscriptionsAndAccounts() {
    $GLOBALS['log']->info('----->Scheduler fired job of type associateSubscriptionsAndAccounts()');
    $db = DBManagerFactory::getInstance();

    $query = "
SELECT DISTINCT
    subscriptions.id as subscription_id,
    accounts_opportunities.account_id AS found_account_id

FROM subscriptions
INNER JOIN
    subscriptions_orders
    ON subscriptions.id = subscriptions_orders.subscription_id
INNER JOIN
    opportunities_cstm
    ON subscriptions_orders.order_number = opportunities_cstm.order_number
INNER JOIN
    opportunities
    ON opportunities.id = opportunities_cstm.id_c
INNER JOIN
    accounts_opportunities
    ON opportunities_cstm.id_c = accounts_opportunities.opportunity_id
WHERE
    subscriptions.account_id = ''
    AND subscriptions_orders.order_number <> ''
    AND subscriptions.deleted = 0
    AND subscriptions_orders.deleted = 0
    AND opportunities.deleted = 0
    AND accounts_opportunities.deleted = 0
";


    /* Get the list from the database */
    $r = $db->query($query);
    $subscriptions = array();
    while ($row = $db->fetchByAssoc($r)) {
        $subscriptions[$row['subscription_id']] = $row['found_account_id'];
        //echo $row['subscription_id'] . "\t" . $row['found_account_id'] . "\n";
    }
    $errors = 0;
    $saved = 0;
    foreach ($subscriptions as $subscription_id => $account_id) {
        $query = "
UPDATE
    subscriptions
SET
    subscriptions.account_id = '" . $account_id . "'
WHERE
    subscriptions.id = '" . $subscription_id . "'
LIMIT 1
";

        $r = $db->query($query);
        ($r && ++$saved) || $errors++;
    }
    $GLOBALS['log']->info("----->Scheduler Zeroed expired credits on $saved accounts with $errors errors.");
    return true;

}

function rescoreIncremental() {
    $GLOBALS['log']->info('----->Scheduler fired job of type rescoreIncremental()');
    $start_time = time();
    require_once('modules/Score/Rescore.php');
    runIncrementalRescore();
    $end_time = time();
    $GLOBALS['log']->info('----->rescoreIncremental finished, took ' . ($end_time - $start_time) . ' seconds');
    return true;
}

function rescoreTotal() {
    $GLOBALS['log']->info('----->Scheduler fired job of type rescoreTotal()');
    $start_time = time();
    require_once('modules/Score/Rescore.php');
    runTotalRescore();
    $end_time = time();
    $GLOBALS['log']->info('----->Scheduler rescoreTotal finished, took ' . ($end_time - $start_time) . ' seconds');
    return true;
}