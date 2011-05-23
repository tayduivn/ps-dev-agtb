<?php

$dependencies['Contacts']['country_to_state_primary']['hooks'] = array('edit');
$dependencies['Contacts']['country_to_state_primary']['triggerFields'] = array('primary_address_country');
$dependencies['Contacts']['country_to_state_primary']['onload'] = true;
$dependencies['Contacts']['country_to_state_primary']['actions'][] = array(
        'name' => 'SetStateOptions',
        'params' => array(
                'countryCode' => 'primary_address_country',
                'stateField' => 'primary_address_state',
        ),
);

$dependencies['Contacts']['country_to_state_alt']['hooks'] = array('edit');
$dependencies['Contacts']['country_to_state_alt']['triggerFields'] = array('alt_address_country');
$dependencies['Contacts']['country_to_state_alt']['onload'] = true;
$dependencies['Contacts']['country_to_state_alt']['actions'][] = array(
        'name' => 'SetStateOptions',
        'params' => array(
                'countryCode' => 'alt_address_country',
                'stateField' => 'alt_address_state',
        ),
);
