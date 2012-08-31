<?php

$viewdefs['Leads']['base']['layout']['convert'] = array(
    'Contacts' => array(
        'required' => true,
        'fieldMapping' => array(
            //contact field => lead field
            'salutation' => 'salutation',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'title' => 'title',
            'department' => 'department',
            'description' => 'description',
            'team_id' => 'team_id',
            'do_not_call' => 'do_not_call',
            'phone_home' => 'phone_home',
            'phone_mobile' => 'phone_mobile',
            'phone_work' => 'phone_work',
            'phone_fax' => 'phone_fax',
            'primary_address_street' => 'primary_address_street',
            'primary_address_city' => 'primary_address_city',
            'primary_address_state' => 'primary_address_state',
            'primary_address_postalcode' => 'primary_address_postalcode',
            'primary_address_country' => 'primary_address_country',
        )
    ),
    'Accounts' =>array(
        'required' => true,
        'contactRelateField' => "account_name",
        'fieldMapping' => array(
            //account field => lead field
            'name' => 'account_name',
            'team_id' => 'team_id',
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
            'campaign_id' => 'campaign_id',
        )
    ),
    'Opportunities' => array(
        'required' => true,
        'fieldMapping' => array(
            //opportunity field => lead field
            'name' => 'opportunity_name',
            'phone_work' => 'phone_office',
            'team_id' => 'team_id',
            'campaign_id' => 'campaign_id',
            'lead_source' => 'lead_source',
        )
    ),
);