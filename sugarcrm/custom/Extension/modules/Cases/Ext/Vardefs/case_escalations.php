<?php
//DEE CUSTOMIZATION - ADDING CHECKBOX TO TRACK MY ESCALATED CASES
$dictionary['Case']['fields']['escalate_case'] =   array (
	'massupdate' => false,
       	'name' => 'escalate_case',
        'vname' => 'LBL_ESCALATE_CASE',
        'type' => 'bool',
        'source' => 'non-db',
        'comment' => 'Escalate this case',
	'reportable' => true,
);

$dictionary['Case']['fields']['user_escalation'] =   array (
        'name' => 'user_escalation',
        'vname' => 'LBL_USER_ESCALATION',
        'type' => 'link',
	'relationship' => 'cases_users',
        'source' => 'non-db',
	'reportable' => true,
);
//END DEE CUSTOMIZATION
?>
