<?php

$dependencies['Opportunities']['related_acct_to_currency']['triggerFields'] = array('account_id', 'account_name');
$dependencies['Opportunities']['related_acct_to_currency']['hooks'] = array('edit');
$dependencies['Opportunities']['related_acct_to_currency']['onload'] = false;
$dependencies['Opportunities']['related_acct_to_currency']['actions'][] = array(
    'name' => 'SetValue',
    'params' => array(
        'target' => 'currency_id',
        'value' => 'getRecordFieldValue("Accounts", $account_id, "currency_id")',
    ),
);
