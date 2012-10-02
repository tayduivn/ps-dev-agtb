<?php
$result = $GLOBALS['db']->query('SELECT accounts.billing_address_country as country, SUM( opportunities.amount ) as amount
FROM opportunities
RIGHT JOIN accounts_opportunities ao ON ao.opportunity_id = opportunities.id
AND ao.deleted =0
RIGHT JOIN accounts ON accounts.id = ao.account_id
AND accounts.deleted =0
WHERE opportunities.sales_stage =  \'Closed Won\'
AND opportunities.deleted =0 GROUP BY accounts.billing_address_country');
while($row = $GLOBALS['db']->fetchByAssoc($result)){
    $data[] = $row;
}