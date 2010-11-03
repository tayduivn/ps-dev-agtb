<?php
/*
** @author: Jesse Mullan
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 4650
** Description: Sets the "Order Type" field in Opportunities as the payment method reported by xCart
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/SiSetOrderTypes.php
*/

chdir('..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
$GLOBALS['log']->fatal("----->siSetOrderTypes started");
$queries = array();
$query = "
SELECT
    `xcart_orders`.`orderid`,
    `xcart_orders`.`payment_method`
FROM
    `xcart_orders`
WHERE
    `xcart_orders`.`orderid` IS NOT NULL
    AND `xcart_orders`.`orderid` <> ''
    AND `xcart_orders`.`date` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
";
$mambo_db = mysql_connect('online-comdb2', 'sugarcrm_com', '08yag81g9Ag91');
mysql_select_db('sugarcrm_com', $mambo_db);
$r = mysql_query($query, $mambo_db);
$order_details = array();

while ($row = mysql_fetch_assoc($r)) {
    $orderid = mysql_real_escape_string($row['orderid']);
    $payment_method = mysql_real_escape_string($row['payment_method']);
    $queries[] = "
UPDATE
    `opportunities_cstm`
SET
    `order_type_c` = '$payment_method'
WHERE
    `order_number` = '$orderid'
";
}
foreach ($queries as $query) {
    // echo "$query\n";
    $res = $GLOBALS['db']->query($query);
    if (!$res) {
	$GLOBALS['log']->fatal("----->siSetOrderTypes query failed: " . $query);
	exit;
    }
}
$GLOBALS['log']->fatal("----->siSetOrderTypes finished");
