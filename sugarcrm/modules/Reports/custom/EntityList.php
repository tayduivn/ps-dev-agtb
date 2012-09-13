<?php

$queries = array(
    'Users' => 'SELECT id, CONCAT(first_name, " ", last_name) name FROM users',
    'Contacts' => 'SELECT id, CONCAT(first_name, " ", last_name) name FROM contacts',
    'Opportunities' => 'SELECT id, name FROM opportunities',
    'Accounts' => 'SELECT id, name FROM accounts'
);

foreach($queries as $module => $sql) {
    $result = $GLOBALS['db']->query($sql);

    while($row = $GLOBALS['db']->fetchByAssoc($result)){
        if(is_null($row['id']) || is_null($row['name'])) continue;
        $row['module'] = $module;
        $data[] = $row;
    }
}
