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

    public function salesByCountry($api, $args)
    {
        $data = array();

        // TODO: Fix information leakage if user cannot list or view records not
        // belonging to them. It's hard to tell if the user has access if we
        // never get the bean.

        // Check for permissions on both Accounts and opportunities.
        $seed = BeanFactory::newBean('Accounts');
        if (!$seed->ACLAccess('view')) {
            return;
        }

        // Load up the relationship
        if (!$seed->load_relationship('opportunities')) {
            // The relationship did not load, I'm guessing it doesn't exist
            return;
        }

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $seed->opportunities->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            return;
        }

        $q = new SugarQuery();
        $q->select(array('accounts.billing_address_country', 'amount_usdollar'));
        $q->from($linkSeed);
        $q->where()->equals('sales_stage', 'Closed Won');
        $q->join('accounts');
        // TODO: When we can sum on the database side through SugarQuery, we can
        // use the group by statement.

        $results = $q->execute();
        foreach ($results as $row) {
            if (empty($data[$row['billing_address_country']])) {
                $data[$row['billing_address_country']] = 0;
            }
            $data[$row['billing_address_country']] += $row['amount_usdollar'];
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
