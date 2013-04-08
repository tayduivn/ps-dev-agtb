<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'clients/base/api/FilterApi.php';
require_once 'data/BeanFactory.php';

class InteractionsApi extends FilterApi
{
    protected $defaultLimit = 5;

    public function registerApiRest()
    {
        return array(
            'interactions' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'interactions'),
                'pathVars' => array('module', 'record'),
                'method' => 'interactions',
                'shortHelp' => 'Get interactions for current record',
                'longHelp' => 'modules/Accounts/clients/base/api/help/AccountsInteractionsApi.html',
            ),
        );
    }

    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function interactions($api, $args)
    {
        $accounts = false;
        $record = $this->getBean($api, $args);


        $beans = array($record->module_dir => $record->id);
        if ($record->module_dir == "Accounts") {
            $accounts = array($record->id);
        } else if ($record->load_relationship("accounts")) {
            $accounts = $record->accounts->get();
        } else if (!empty($record->account_id)) {
            $accounts = array($record->account_id);
        }


        if (!empty($accounts)) {
            $beans['Accounts'] = $accounts[0];
        }
        $ret = $this->getResponseStructure();
        $filters = $this->getFilters();
        $baseFilters = array();
        if (!empty($args['list']) && $args['list'] == "my") {
            $baseFilters[] = array('$owner' => true);
        }
        if (!empty($args['filter'])) {
            $filterDate = new SugarDateTime('NOW');
            $filterDate->modify("-{$args['filter']} days");
            $baseFilters[] = array(
                'date_modified' => array(
                    '$gt' => $filterDate->asDb()
                )
            );
        }

        $limits = $this->getLimits();

        foreach ($beans as $module => $id) {
            $seed = BeanFactory::getBean($module);
            $args['module'] = $module;
            $args['record'] = $id;
            foreach ($ret as $type => $result) {
                if (!$seed->load_relationship($result['link']))
                    continue;
                $args['link_name'] = $result['link'];
                $args['filter'] = array_merge($baseFilters, array($filters[$type]));
                $args['max_num'] = $limits[$type];
                $filterData = $this->filterRelated($api, $args);
                if (!empty($filterData['records'])) {
                    $ret[$type]['data'] = array_merge($ret[$type]['data'], $filterData['records']);
                }
            }
        }
        foreach($ret as $type => $result)
        {
            $ret[$type]["count"] = empty($result["data"]) ? 0 : sizeof($result["data"]);
        }


        return $ret;
    }

    /**
     * @return array
     */
    protected function getFilters()
    {
        return array(
            'calls' => array( ),
            'meetings' => array(),
            'emailsRecv' => array(
                "type" => 'inbound'
            ),
            'emailsSent' => array(
                "type" => 'out'
            ),
        );
    }

    /**
     * @return array
     */
    protected function getLimits()
    {
        return array(
            'calls' => $this->defaultLimit,
            'meetings' => $this->defaultLimit,
            'emailsRecv' => $this->defaultLimit,
            'emailsSent' => $this->defaultLimit,
        );
    }

    /**
     * @param $api
     * @param $args
     * @return null|SugarBean
     * @throws SugarApiExceptionNotFound
     * @throws SugarApiExceptionNotAuthorized
     */
    protected function getBean($api, $args)
    {
        // Load up the bean
        $record = BeanFactory::getBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound('Could not find parent record ' . $args['record'] . ' in module ' . $args['module']);
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }
        return $record;
    }

    protected function getResponseStructure()
    {
        return array(
            'calls' => array(
                "data" => array(),
                "link" => "calls",
                "module" => "Calls"
            ),
            'meetings' => array(
                "data" => array(),
                "link" => "meetings",
                "module" => "Meetings"
            ),
            'emailsRecv' => array(
                "data" => array(),
                "link" => "emails",
                "module" => "Emails"
            ),
            'emailsSent' => array(
                "data" => array(),
                "link" => "emails",
                "module" => "Emails"
            ),
        );
    }
}
