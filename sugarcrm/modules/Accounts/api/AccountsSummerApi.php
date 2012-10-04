<?php
//FILE SUGARCRM flav=free ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/api/ListApi.php';
require_once 'data/BeanFactory.php';

class AccountsSummerApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
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


    public function opportunityStats($api, $args)
    {
        $data = $this->getOpportunities($api, $args);
        $return = array();
        foreach ($data as $record) {
            if (!isset($return[$record['sales_stage']])) {
                $return[$record['sales_stage']] = array('amount_usdollar' => 0, 'count' => 0);
            }
            $return[$record['sales_stage']]['amount_usdollar'] += $record['amount_usdollar'];
            $return[$record['sales_stage']]['count']++;
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
