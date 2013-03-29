<?php

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

        $query = new SugarQuery();
        $query->select(array('accounts.billing_address_country', 'amount_usdollar'));
        $query->from($linkSeed);
        $query->where()->equals('sales_stage', 'Closed Won');
        $query->join('accounts');
        // TODO: When we can sum on the database side through SugarQuery, we can
        // use the group by statement.

        $results = $query->execute();
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
        // TODO make all APIs wrapped on tries and catches
        // TODO: move this to own module (in this case accounts)

        // TODO: Fix information leakage if user cannot list or view records not
        // belonging to them. It's hard to tell if the user has access if we
        // never get the bean.

        // Check for permissions on both Accounts and opportunities.
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);
        if (!$record->ACLAccess('view')) {
            return;
        }

        // Load up the relationship
        if (!$record->load_relationship('opportunities')) {
            // The relationship did not load, I'm guessing it doesn't exist
            return;
        }

        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $record->opportunities->getRelatedModuleName();
        $linkSeed = BeanFactory::newBean($linkModuleName);
        if (!$linkSeed->ACLAccess('view')) {
            return;
        }

        $query = new SugarQuery();
        $query->select(array('sales_status', 'amount_usdollar'));
        $query->from($linkSeed);
        // making this more generic so we can use this on contacts also as soon
        // as we move it to a proper module
        $query->join('accounts', array('alias' => 'record'));
        $query->where()->equals('record.id', $record->id);
        // FIXME add the security query here!!!
        // TODO: When we can sum on the database side through SugarQuery, we can
        // use the group by statement.

        $results = $query->execute();

        // TODO this can't be done this way since we can change the status on
        // studio and add more
        $data = array(
            'won' => array('amount_usdollar' => 0, 'count' => 0),
            'lost' => array('amount_usdollar' => 0, 'count' => 0),
            'active' => array('amount_usdollar' => 0, 'count' => 0)
        );

        foreach ($results as $row) {
            $map = array(
                'Closed Lost' => 'lost',
                'Closed Won' => 'won',
            );
            if (array_key_exists($row['sales_status'], $map)) {
                $status = $map[$row['sales_status']];
            } else {
                $status = 'active';
            }
            $data[$status]['amount_usdollar'] += $row['amount_usdollar'];
            $data[$status]['count']++;
        }
        return $data;
    }
}
