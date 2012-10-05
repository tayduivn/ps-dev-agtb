<?php
$month_start = date('Y-m-d',mktime(12,1,1,date('m'),-1,date('Y')));
$month_end = date('Y-m-d',mktime(12,1,1,date('m')+1,1,date('Y')));
$query = "SELECT u.id user_id, CONCAT(u.first_name, ' ', u.last_name)  user_name, SUM(o.amount) amount, count(o.id) sales
FROM opportunities o
LEFT JOIN users u ON o.assigned_user_id = u.id
WHERE o.sales_stage = 'Closed Won'
AND o.date_closed BETWEEN '$month_start' AND '$month_end'
GROUP BY u.id";

$result = $GLOBALS['db']->query($query,true);
$data = array();

while($row = $GLOBALS['db']->fetchByAssoc($result)){
    $data[] = $row;
}
