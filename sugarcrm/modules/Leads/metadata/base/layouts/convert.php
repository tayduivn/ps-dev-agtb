<?php

$viewdefs['Leads']['base']['layout']['convert'] = array(
    'Contacts' => array(
        'required' => true,
        'leadRelationship' => 'contact_leads',
        // fields with the same name will be mapped automatically
        // additional fields to map are defined here
        'additionalFieldMapping' => array(
        )
    ),
    'Accounts' =>array(
        'required' => true,
        'leadRelationship' => 'account_leads',
        'additionalFieldMapping' => array(
            'account_name' => 'name',
        )
    ),
    'Opportunities' => array(
        'required' => true,
        'leadRelationship' => 'opportunity_leads',
        'additionalFieldMapping' => array(
            'opportunity_name' => 'name'
        )
    ),
);