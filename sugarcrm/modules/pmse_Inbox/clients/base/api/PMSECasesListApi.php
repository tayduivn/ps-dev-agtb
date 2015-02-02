<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once 'clients/base/api/FilterApi.php';
require_once 'include/SugarQuery/SugarQuery.php';
require_once 'modules/pmse_Inbox/engine/PMSE.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

class PMSECasesListApi extends FilterApi
{
    public function __construct()
    {
        $this->pmse = PMSE::getInstance();
    }

    /**
     *
     * @return type
     */
    public function registerApiRest()
    {
        return array(
            'getModuleCaseList' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'casesList'),
                'pathVars' => array('module', 'casesList'),
                'method' => 'selectCasesList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'getLoadLogs' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'getLog', '?'),
                'pathVars' => array('module', 'getLog', 'typelog'),
                'method' => 'selectLogLoad',
                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'getConfigLogs' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'logGetConfig'),
                'pathVars' => array('module', 'logGetConfig'),
                'method' => 'configLogLoad',
                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'setConfigLogs' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'logSetConfig'),
                'pathVars' => array('module', ''),
                'method' => 'configLogPut',
//                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'getProcessUsers' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'processUsersChart', '?'),
                'pathVars' => array('module', '', 'filter'),
                'method' => 'returnProcessUsersChart',
//                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'getProcessStatus' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'processStatusChart', '?'),
                'pathVars' => array('module', '', 'filter'),
                'method' => 'returnProcessStatusChart',
//                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
        );
    }

    public function selectCasesList($api, $args)
    {
        $flowQuery = new SugarQuery();
        $bean = BeanFactory::getBean('pmse_BpmFlow');
        $flowQuery->from($bean, array('alias' => 'f'));
        $flowQuery->select->fieldRaw('count(f.cas_flow_status)', 'flow_count');
        $flowQuery->where()
            ->equals('f.cas_flow_status', 'ERROR');
        $flowQuery->where()->queryAnd()
            ->addRaw("f.cas_id=a.cas_id");


        $q = new SugarQuery();
        $inboxBean = BeanFactory::getBean('pmse_Inbox');
        if ($args['order_by'] == 'cas_due_date:asc') {
            $args['order_by'] = 'cas_create_date:asc';
        }
        $options = self::parseArguments($api, $args, $inboxBean);
        $fields = array(
            'a.*'
        );
        $q->select($fields);
        $q->from($inboxBean, array('alias' => 'a'));
        $q->joinRaw('INNER JOIN users u ON a.created_by=u.id');
        $q->select->fieldRaw('CONCAT(COALESCE(u.first_name, ""), " ", u.last_name)', 'assigned_user_name');
        //Flow query breaks on mssql due to the use of row_number() / count in a subselect which is not supported
        //Doesn't appear to be used.
        //$q->select->fieldRaw('('.$flowQuery->compileSql().')','flow_error');
        if (!empty($args['q'])) {
            switch ($args['module_list']) {
                case 'all':
                    $q->where()->queryAnd()
                        ->addRaw("a.cas_title LIKE '%" . $args['q'] . "%' OR a.pro_title LIKE '%" . $args['q'] . "%' OR a.cas_status LIKE '%" . $args['q'] . "%' OR last_name LIKE '%" . $args['q'] . "%'");
                    break;
                case translate("LBL_PROCESS_DEFINITION_NAME",'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("a.cas_title LIKE '%" . $args['q'] . "%'");
                    break;
                case translate("LBL_RECORD_NAME",'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("a.pro_title LIKE '%" . $args['q'] . "%'");
                    break;
                case translate("LBL_PMSE_LABEL_STATUS",'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("a.cas_status LIKE '%" . $args['q'] . "%'");
                    break;
                case translate("LBL_OWNER",'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("last_name LIKE '%" . $args['q'] . "%'");
                    break;
            }
        } else {
            switch ($args['module_list']) {
                case translate('LBL_STATUS_COMPLETED', 'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("cas_status = 'COMPLETED'");
                    break;
                case translate('LBL_STATUS_TERMINATED', 'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("cas_status = 'TERMINATED'");
                    break;
                case translate('LBL_STATUS_IN_PROGRESS', 'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("cas_status = 'IN PROGRESS'");
                    break;
                case translate('LBL_STATUS_CANCELLED', 'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("cas_status = 'CANCELLED'");
                    break;
                case translate('LBL_STATUS_ERROR', 'pmse_Inbox'):
                    $q->where()->queryAnd()
                        ->addRaw("cas_status = 'ERROR'");
                    break;
            }
        }
        foreach ($options['order_by'] as $orderBy) {
            $q->orderBy($orderBy[0], $orderBy[1]);
        }
        // Add an extra record to the limit so we can detect if there are more records to be found
        $q->limit($options['limit']);
        $q->offset($options['offset']);

        $offset = $options['offset'] + $options['limit'];
        $count = 0;
        $list = $q->execute();
        foreach ($list as $key => $value) {
            if ($value["cas_status"] === 'IN PROGRESS') {
                $list[$key]["cas_status"] = '<data class="label label-Leads">' . $value["cas_status"] . '</data>';
            } elseif ($value["cas_status"] === 'COMPLETED' || $value["cas_status"] === 'TERMINATED') {
                $list[$key]["cas_status"] = '<data class="label label-success">' . $value["cas_status"] . '</data>';
            } elseif ($value["cas_status"] === 'CANCELLED') {
                $list[$key]["cas_status"] = '<data class="label label-warning">' . $value["cas_status"] . '</data>';
            } else {
                $list[$key]["cas_status"] = '<data class="label label-important">' . $value["cas_status"] . '</data>';
            }
//            if($value["flow_error"]!='0')
//            {
//                $list[$key]["cas_status"]='<data class="label label-important">ERROR</data>';
////                $list[$key]["execute"] = 'Execute';
//            }
            $count++;
        }
        if ($count == $options['limit']) {
            $offset = $options['offset'] + $options['limit'];
        } else {
            $offset = -1;
        }

        $data = array();
        $data['next_offset'] = $offset;
        $data['records'] = $list;
        //$data['options'] = $options;
        //$data['args'] = $args;
        $data['sql'] = $q->compileSql();
        return $data;
    }

    public function selectLogLoad($api, $args)
    {
        $logger = PMSELogger::getInstance();
        $pmse = PMSE::getInstance();

        $showSugarCrm = false;

        if ($args['typelog'] == 'sugar') {
            $showSugarCrm = true;
        }

        if ($showSugarCrm) {
            $log = $pmse->getLogFile('sugarcrm.log');
        } else {
            $log = $pmse->getLogFile($logger->getLogFileNameWithPath());
        }
        return $log;
    }

    public function configLogLoad($api, $args)
    {
        $q = new SugarQuery();
        $configLogBean = BeanFactory::getBean('pmse_BpmConfig');
        $fields = array(
            'c.cfg_value'
        );

        $q->select($fields);
        $q->from($configLogBean, array('alias' => 'c'));
        $q->where()->queryAnd()
            ->addRaw("c.cfg_status='ACTIVE' AND c.name='logger_level'");
        $list = $q->execute();
        if (empty($list)) {
            $bean = BeanFactory::newBean('pmse_BpmConfig');
            $bean->cfg_value = 'warning';
            $bean->name = 'logger_level';
            $bean->description = 'Logger Level';
            $bean->save();

            $list = array(0 => array('cfg_value' => 'warning'));
        }
        $data = array();
        $data['records'] = $list;
        return $data;
    }

    /*
     * config log PMSE log
     */
    public function configLogPut($api, $args)
    {

        $data = $args['cfg_value'];
        $bean = BeanFactory::getBean('pmse_BpmConfig')
            ->retrieve_by_string_fields(array('cfg_status' => 'ACTIVE', 'name' => 'logger_level'));
        $bean->cfg_value = $data;
        $bean->save();

        return array('success' => true);
    }

    public function returnProcessUsersChart($api, $args)
    {
        $filter = $args['filter'];
        return $this->createProcessUsersChartData($filter);
    }

    public function returnProcessStatusChart($api, $args)
    {
        $filter = $args['filter'];
        return $this->createProcessStatusChartData($filter);
    }

    protected function createProcessUsersChartData($filter)
    {
        // set the seed bpm flow
        $seed = BeanFactory::newBean('pmse_BpmFlow');
        // creating the sugar query object
        $q = new SugarQuery();
        // adding the seed bean
        $q->from($seed);
        // joining the users table
        $q->joinRaw('INNER JOIN users ON users.id=pmse_bpm_flow.cas_user_id');
        // joining the process definition table in order to retrieve the process status
        $q->joinRaw('INNER JOIN pmse_bpm_process_definition pdef ON pmse_bpm_flow.pro_id = pdef.id');
        // retrieving the user_name attribute,
        // it could be the first_name or last_name
        $q->select->fieldRaw("users.id", "user_name");
        $q->select->fieldRaw('users.first_name');
        $q->select->fieldRaw('users.last_name');
        // adding a custom field raw call since there is no other way to add an
        // aggregated member
        $q->select->fieldRaw("COUNT(pmse_bpm_flow.id)", "derivation_count");
        // ordering by raw member
        //$q->orderByRaw('derivation_count');
        // grouping by user_name
        $q->groupByRaw('user_name');
        // only retrieve the flows with FORM status
        $q->where()->equals('pmse_bpm_flow.cas_flow_status', 'FORM');
        // only retrieve the flows from ACTIVE definitions
        $q->where()->addRaw("pdef.pro_status <> 'INACTIVE'");

        if ($filter !== 'all') {
            $q->where()->addRaw("pdef.prj_id = '" . $filter . "'");
        }

        $data_bean = $q->execute();

        $data = array();
        $total = 0;
        foreach ($data_bean as $record) {
            if (isset($record['user_name'])) {
                // Maybe it is a good idea to have a function
                // that returns the user name depending the Sugar's configuration
                $name = trim($record['first_name'] . ' ' . $record['last_name']);

                $data[] = array(
                    'key' => $name,
                    'value' => $record['derivation_count'],
                );
                $total += $record['derivation_count'];
            }
        }

        return array(
            "properties" => array(
                "total" => $total,
            ),
            "data" => $data,
        );
    }

    protected function createProcessStatusChartData($filter)
    {
        $seed_processes = BeanFactory::newBean('pmse_Project');
        $qp = new SugarQuery();
        $qp->from($seed_processes);
        $qp->select->field('id');
        $qp->select->field('name');
        $processes = $qp->execute();

        $process_map = array();
        for ($i = 0; $i < sizeof($processes); $i++) {
            $processes[$i]['total'] = 0;
            $processes[$i]['status'] = array(
                'IN PROGRESS' => 0,
                'COMPLETED' => 0,
                'CANCELLED' => 0,
                'ERROR' => 0,
                'TERMINATED' => 0,
            );
            $process_map[$processes[$i]['id']] = $i;
        }


        $seed = BeanFactory::newBean('pmse_Inbox');
        // creating the sugar query object
        $q = new SugarQuery();
        // adding the seed bean
        $q->from($seed);
        // joining the users table
        $q->joinRaw('INNER JOIN pmse_bpmn_process ON pmse_bpmn_process.id=pmse_inbox.pro_id');

        $q->select->field("cas_status");
        $q->select->fieldRaw("COUNT(*) as total");
        $q->select->fieldRaw("prj_id");

        $q->groupByRaw('pro_id, cas_status');

        if ($filter !== 'all') {
            $q->where()->addRaw("pmse_project.id = '" . $filter . "'");
        }

        $data_bean = $q->execute();

        foreach ($data_bean as $row) {
            $index = $process_map[$row['prj_id']];
            $processes[$index]['status'][$row['cas_status']] = (int)$row['total'];
            $processes[$index]['total'] += $row['total'];
        }

        $labels = array();
        $values = array();
        $in_progress = array();
        $completed = array();
        $cancelled = array();
        $terminated = array();
        $error = array();

        for ($i = 0; $i < sizeof($processes); $i++) {
            $labels[] = array(
                "group" => ($i + 1),
                "l" => $processes[$i]['name'],
            );
            $values[] = array(
                "group" => ($i + 1),
                "t" => $processes[$i]['total'],
            );
            $in_progress[] = array(
                "series" => 0,
                "x" => ($i + 1),
                "y" => $processes[$i]['status']['IN PROGRESS'],
                //"y0" => $processes[$i]['status']['IN PROGRESS'],
            );
            $completed[] = array(
                "series" => 1,
                "x" => ($i + 1),
                "y" => $processes[$i]['status']['COMPLETED'],
                //"y0" => $processes[$i]['status']['COMPLETED'],
            );
            $cancelled[] = array(
                "series" => 2,
                "x" => ($i + 1),
                "y" => $processes[$i]['status']['CANCELLED'],
                //"y0" => $processes[$i]['status']['CANCELLED'],
            );
            $terminated[] = array(
                "series" => 3,
                "x" => ($i + 1),
                "y" => $processes[$i]['status']['TERMINATED'],
                //"y0" => $processes[$i]['status']['TERMINATED'],
            );
            $error[] = array(
                "series" => 4,
                "x" => ($i + 1),
                "y" => $processes[$i]['status']['ERROR'],
                //"y0" => $processes[$i]['status']['ERROR'],
            );
        }

        return array(
            "properties" => array(
                "labels" => $labels,
                "values" => $values,
            ),
            "data" => array(
                array(
                    "key" => translate("LBL_PMSE_IN_PROGESS_STATUS"),
                    "type" => "bar",
                    "color" => '#176de5',
                    "values" => $in_progress,
                ),
                array(
                    "key" => translate("LBL_PMSE_COMPLETED_STATUS"),
                    "type" => "bar",
                    "color" => '#33800d',
                    "values" => $completed,
                ),
                array(
                    "key" => translate("LBL_PMSE_CANCELLED_STATUS"),
                    "type" => "bar",
                    "color" => '#e5a117',
                    "values" => $cancelled,
                ),
                array(
                    "key" => translate("LBL_PMSE_TERMINATED_STATUS"),
                    "type" => "bar",
                    "color" => '#6d17e5',
                    "values" => $terminated,
                ),
                array(
                    "key" => translate("LBL_PMSE_ERROR_STATUS"),
                    "type" => "bar",
                    "color" => '#E61718',
                    "values" => $error,
                ),
            ),
        );
    }
}