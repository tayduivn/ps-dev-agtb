<?php
/*
** @author: Jon Whitcraft
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 12893
** Description: disables expired Subscriptions
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/SiSubscriptionsAutoDisable.php
*/

chdir('..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
$GLOBALS['log']->info("Subscriptions Auto Disable Starting");

$query = "SELECT s.id, s.subscription_id, a.name, s.expiration_date
 FROM subscriptions s
 INNER JOIN accounts a ON a.id = s.account_id
 WHERE s.expiration_date <= NOW()
    and s.status = 'enabled'
    and s.deleted = '0'
    and s.ignore_expiration_date = '0'";
$res = $GLOBALS['db']->query($query);

while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
    $GLOBALS['log']->info("Expiring Subscription for {$row['name']} which expired on {$row['expiration_date']}");
    $subscription = new Subscription();
    $subscription->disable_row_level_security = true;
    $subscription->retrieve($row['id']);

    $subscription->status = 'disabled';
    $subscription->save();
    unset($subscription);
}

unset($res, $row, $query);

$GLOBALS['log']->info("Subscriptions Auto Disable Finished");
