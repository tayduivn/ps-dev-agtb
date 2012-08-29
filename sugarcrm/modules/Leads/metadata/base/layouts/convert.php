<?php

$viewdefs['Leads']['base']['layout']['convert'] = array(
    'Contacts' => array(
        'required' => true,
        'leadRelationship' => 'contact_leads',
        // fields with the same name will be mapped automatically
        // additional fields to map are defined here
        'additionalFieldMapping' => array(
            //contact field => lead field
        )
    ),
    'Accounts' =>array(
        'required' => true,
        'leadRelationship' => 'account_leads',
        'additionalFieldMapping' => array(
            //account field => lead field
            'name' => 'account_name',
            'billing_address_street' => 'primary_address_street',
            'billing_address_city' => 'primary_address_city',
            'billing_address_state' => 'primary_address_state',
            'billing_address_postalcode' => 'primary_address_postalcode',
            'billing_address_country' => 'primary_address_country',
            'shipping_address_street' => 'primary_address_street',
            'shipping_address_city' => 'primary_address_city',
            'shipping_address_state' => 'primary_address_state',
            'shipping_address_postalcode' => 'primary_address_postalcode',
            'shipping_address_country' => 'primary_address_country',
        )
    ),
    'Opportunities' => array(
        'required' => true,
        'leadRelationship' => 'opportunity_leads',
        'additionalFieldMapping' => array(
            //opportunity field => lead field
            'name' => 'opportunity_name',
            'phone_work' => 'phone_office',
        )
    ),
);