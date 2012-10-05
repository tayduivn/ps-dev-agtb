<?php
$result = $GLOBALS['db']->query('SELECT o.sales_stage as name, count( o.sales_stage ) as value
FROM opportunities o
WHERE deleted =0
GROUP BY o.sales_stage');
while($row = $GLOBALS['db']->fetchByAssoc($result)){
    $data[] = $row;
}