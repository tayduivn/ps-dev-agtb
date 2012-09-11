<?php
$opportunity_result = $GLOBALS['db']->query('SELECT assigned_user_id, name, id, amount_usdollar as value, sales_stage FROM opportunities');

$user_result = $GLOBALS['db']->query('SELECT assigned_user_id as id, users.first_name, users.last_name FROM opportunities JOIN users ON users.id = opportunities.assigned_user_id GROUP BY assigned_user_id');

// TODO: Un-hardcode this by doing a query like the following:
// SELECT sales_stage FROM opportunities GROUP BY sales_stage
$stages = array('Prospecting', 'Closed Lost', 'Closed Won', 'Qualification');
$temp = array();
$data = array('name' => "Opportunities", 'children' => array());

while($row = $GLOBALS['db']->fetchByAssoc($user_result)) {
    $temp[$row['id']] = array('name' => $row['first_name'].' '.$row['last_name'],
        'children' => array()
    );
    foreach($stages as $stage) {
        $temp[$row['id']]['children'][$stage] = array(
            'name' => $stage,
            'children' => array()
        );
    }
}

while($row = $GLOBALS['db']->fetchByAssoc($opportunity_result)){
    $row['value'] = (int)$row['value'];
    $temp[$row['assigned_user_id']]['children'][$row['sales_stage']]['children'][] = $row;
}

foreach($temp as $user) {
    $new_array = array();
    foreach($user['children'] as $sales_stage) {
        $new_array[] = $sales_stage;
    }
    $user['children'] = $new_array;
    $data['children'][] = $user;
}
