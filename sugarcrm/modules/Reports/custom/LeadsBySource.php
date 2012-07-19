<?php
$result = $GLOBALS['db']->query('SELECT lead_source, count(lead_source) FROM leads WHERE leads.deleted = 0 GROUP BY lead_source ');
while($row = $GLOBALS['db']->fetchByAssoc($result)){
    $data[] = $row;
}