<?php
//FILE SUGARCRM flav=free ONLY
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
}
