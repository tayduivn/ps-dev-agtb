<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'modules/Accounts/clients/base/api/AccountsInteractionsApi.php';
require_once 'data/BeanFactory.php';

class ContactsInteractionsApi extends AccountsInteractionsApi
{
    public function registerApiRest()
    {
        return array(
            'interactions' => array(
                'reqType' => 'GET',
                'path' => array('Contacts', '?', 'interactions'),
                'pathVars' => array('module', 'record'),
                'method' => 'interactions',
                'shortHelp' => 'Get interactions for current record',
                'longHelp' => 'modules/Contacts/clients/base/api/help/ContactsInteractionsApi.html',
            ),
        );
    }
    
    protected function loadAccountBean(ServiceBase $api, array $args) 
    {
        $record = $this->loadBean($api, $args);
        // Load up the relationship
        if (!$record->load_relationship('accounts')) {
            throw new SugarApiExceptionNotFound('Could not find a relationship name accounts');
        }

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->accounts->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $accounts = $record->accounts->query(array());
        foreach ($accounts['rows'] as $accountId => $value) {
            $account = BeanFactory::getBean('Accounts', $accountId);
            if (empty($account)) {
                throw new SugarApiExceptionNotFound('Could not find parent record '.$accountId.' in module Accounts');
            }
            if (!$account->ACLAccess('view')) {
                throw new SugarApiExceptionNotAuthorized('No access to view records for module: Accounts');
            }

            // Only one account, so we can return inside the loop.
            return $account;
        }
    }
}
