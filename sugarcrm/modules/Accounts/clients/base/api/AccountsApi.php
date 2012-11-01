<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'clients/base/api/ListApi.php';
require_once 'data/BeanFactory.php';

class AccountsApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'sales_by_country' => array(
                'reqType' => 'GET',
                'path' => array('Accounts','by_country'),
                'pathVars' => array('', ''),
                'method' => 'salesByCountry',
                'shortHelp' => 'Get opportunities won by country',
                'longHelp' => '',
            ),
            'opportunity_stats' => array(
                'reqType' => 'GET',
                'path' => array('Accounts','?', 'opportunity_stats'),
                'pathVars' => array('module', 'record'),
                'method' => 'opportunityStats',
                'shortHelp' => 'Get opportunity statistics for current record',
                'longHelp' => '',
            ),

        );
    }

    public function salesByCountry($api, $args) {
        $data = array();
        $seed = BeanFactory::newBean('Accounts');
        $accounts = $seed->get_full_list();
        foreach($accounts as $account) {
            if (!$account->ACLAccess('view')) {
                continue;
            }
            // Load up the relationship
            if (!$account->load_relationship('opportunities')) {
                // The relationship did not load, I'm guessing it doesn't exist
                continue;
            }
            // Figure out what is on the other side of this relationship, check permissions
            $linkModuleName = $account->opportunities->getRelatedModuleName();
            $linkSeed = BeanFactory::newBean($linkModuleName);
            if (!$linkSeed->ACLAccess('view')) {
                continue;
            }
            $opportunities = $account->opportunities->query(array());
            foreach ($opportunities['rows'] as $opportunityId => $value) {
                $opportunity = BeanFactory::getBean('Opportunities', $opportunityId);
                if(empty($data[$account->billing_address_country])) {
                    $data[$account->billing_address_country] = 0;
                }
                if($opportunity->sales_stage == "Closed Won") {
                    $data[$account->billing_address_country] += $opportunity->amount_usdollar;
                }
            }
        }
        return $data;
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

    protected function getOpportunities($api, $args, $limit = null)
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
        if (!$record->load_relationship('opportunities')) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship name opportunities');
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->opportunities->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$linkModuleName);
        }

        $opportunities = $record->opportunities->query(array());
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
        return $data['records'];
    }
}
