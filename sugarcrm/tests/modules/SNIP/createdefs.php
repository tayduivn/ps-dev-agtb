<?php

$createdef['contacts@testsugar.info']['Contacts'] = array(
        'fields' => array(
            'last_name' => '{from_name}',
            'department' => '{email_id}',
            'date_entered' => '{date}',
            'description' => '{description} {email_id} {message_id} {subject} {from}',
            'lead_source' => 'Email',
        ),
);

$createdef['cases@testsugar.info']['Cases'] = array(
        'fields' => array(
	        'name' => '{from_name}',
            'resolution' => '{email_id}',	        
	        'date_entered' => '{date}',
	        'description' => '{description} {email_id} {message_id} {subject} {from}',
        ),
);

$createdef['opp@testsugar.info']['Opportunities'] = array(
        'fields' => array(
            'name' => '{from_name}',
            'sales_stage' => '{email_id}',	        
            'date_entered' => '{date}',
            'description' => '{description} {email_id} {message_id} {subject} {from}',
        ),
);