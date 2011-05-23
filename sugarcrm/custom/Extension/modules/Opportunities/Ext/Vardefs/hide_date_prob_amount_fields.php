<?php

$dictionary['Opportunity']['fields']['date_closed']['dependency'] = 'or(equal($sales_stage, "01"), equal($sales_stage, ""))';
$dictionary['Opportunity']['fields']['probability']['dependency'] = 'or(equal($sales_stage, "01"), equal($sales_stage, ""))';
$dictionary['Opportunity']['fields']['amount']['dependency'] = 'or(equal($sales_stage, "01"), equal($sales_stage, ""))';

$dictionary['Opportunity']['fields']['date_closed']['required'] = false;
$dictionary['Opportunity']['fields']['amount']['required'] = false;
