<?php
//FILE SUGARCRM flav=free ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/api/ListApi.php';
require_once 'data/BeanFactory.php';

class ContactsSummerApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'opportunity_stats' => array(
                'reqType' => 'GET',
                'path' => array('Contacts','?', 'opportunity_stats'),
                'pathVars' => array('module', 'record'),
                'method' => 'opportunityStats',
                'shortHelp' => 'Get opportunity statistics for current record',
                'longHelp' => '',
            ),
        );
    }


    public function opportunityStats($api, $args)
    {
        $data = $this->getOpportunities($api, $args);
        $return = array(
            'won' => array('amount_usdollar' => 0, 'count' => 0),
            'lost' => array('amount_usdollar' => 0, 'count' => 0),
            'active' => array('amount_usdollar' => 0, 'count' => 0)
        );
        foreach ($data as $record) {
            switch($record['sales_stage']) {
                case "Closed Lost":
                    $status = 'lost';
                    break;
                case "Closed Won":
                    $status = 'won';
                    break;
                default:
                    $status = 'active';
                    break;
            }
            $return[$status]['amount_usdollar'] += $record['amount_usdollar'];
            $return[$status]['count']++;
        }
        return $return;
    }

    protected function getOpportunities($api, $args, $limit = 5)
    {
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
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
        $rowCount = 1;

        $accountData = array();
        $data['records'] = array();
        foreach ($accounts['rows'] as $accountId => $value) {
            $rowCount++;
            $account = BeanFactory::getBean('Accounts', $accountId);
            if (empty($account)) {
                throw new SugarApiExceptionNotFound('Could not find parent record '.$accountId.' in module Accounts');
            }
            if (!$account->ACLAccess('view')) {
                throw new SugarApiExceptionNotAuthorized('No access to view records for module: Accounts');
            }
            // Load up the relationship
            if (!$account->load_relationship('opportunities')) {
                // The relationship did not load, I'm guessing it doesn't exist
                throw new SugarApiExceptionNotFound('Could not find a relationship name opportunities');
            }
            // Figure out what is on the other side of this relationship, check permissions
            $linkModuleName2 = $account->opportunities->getRelatedModuleName();
            $linkSeed2 = BeanFactory::newBean($linkModuleName2);
            if (!$linkSeed2->ACLAccess('view')) {
                throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName2);
            }

            $opportunities = $account->opportunities->query(array());
            $rowCount = 1;

            $data['records'] = array();
            foreach ($opportunities['rows'] as $opportunityId => $value) {
                $rowCount++;
                $opportunity = BeanFactory::getBean('Opportunities', $opportunityId);
                $data['records'][] = $this->formatBean($api, $args, $opportunity);
                if (!is_null($limit) && $rowCount == $limit) {
                    // We have hit our limit.
                    break;
                }
            }
        }
        return $data['records'];
    }
}
