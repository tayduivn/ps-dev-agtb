<?php
$result = $GLOBALS['db']->query('SELECT assigned_user_id, name, id, amount_usdollar, sales_stage FROM opportunities');
while($row = $GLOBALS['db']->fetchByAssoc($result)){
    if(!isset($data[$row['assigned_user_id']])) {
        $data[$row['assigned_user_id']] = array();
    }
    if(!isset($data[$row['assigned_user_id']][$row['sales_stage']])) {
        $data[$row['assigned_user_id']][$row['sales_stage']] = array();
    }
    $data[$row['assigned_user_id']][$row['sales_stage']] = $row;
}
