<?php

$dependencies['Accounts']['country_to_state_bill']['hooks'] = array('edit');
$dependencies['Accounts']['country_to_state_bill']['triggerFields'] = array('billing_address_country');
$dependencies['Accounts']['country_to_state_bill']['onload'] = true;
$dependencies['Accounts']['country_to_state_bill']['actions'][] = array(
        'name' => 'SetStateOptions',
        'params' => array(
                'countryCode' => 'billing_address_country',
                'stateField' => 'billing_address_state',
        ),
);

$dependencies['Accounts']['country_to_state_ship']['hooks'] = array('edit');
$dependencies['Accounts']['country_to_state_ship']['triggerFields'] = array('shipping_address_country');
$dependencies['Accounts']['country_to_state_ship']['onload'] = true;
$dependencies['Accounts']['country_to_state_ship']['actions'][] = array(
        'name' => 'SetStateOptions',
        'params' => array(
                'countryCode' => 'shipping_address_country',
                'stateField' => 'shipping_address_state',
        ),
);
