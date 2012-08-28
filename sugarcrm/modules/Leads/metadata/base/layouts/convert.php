<?php

$viewdefs['Leads']['base']['layout']['convert'] = array(
    'Contacts' => array(
        'required' => true,
        'leadRelationship' => 'contact_leads', //TODO: verify if this is additional.
        'fieldMapping' => array(
            'fname' => 'first_name',
        )
    ),
    'Accounts' =>array(
        'required' => true,
        'leadRelationship' => 'account_leads',
        'fieldMapping' => array(

        )
    ),
    'Opportunities' => array(
        'required' => true,
        'leadRelationship' => 'opportunity_leads',
        'fieldMapping' => array(

        )
    ),
);