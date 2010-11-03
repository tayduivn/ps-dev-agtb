<?php
/*
** @author: Sadek Baroudi
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 10345
** Description: clears the "Ignore Expiration Date" checkbox in Subscriptions if the Subscription has been expired for 10+ days 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Moofcart/ResetSubscriptionIgnoreExpDate.php
*/

chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Users/User.php');
require_once('modules/Subscriptions/Subscription.php');

$GLOBALS['current_user'] = new User();
$GLOBALS['current_user']->retrieve('1');
$query = "select id from subscriptions where ignore_expiration_date = 1 and deleted = 0 and CURDATE() >= date_add(expiration_date, INTERVAL '10' DAY)";
$res = $GLOBALS['db']->query($query);
while($row = $GLOBALS['db']->fetchByAssoc($res)){
	$sub = new Subscription();
	$sub->retrieve($row['id']);
	if(!empty($sub->id)){
		$sub->ignore_expiration_date = '0';
		$sub->save(false);
	}
}
