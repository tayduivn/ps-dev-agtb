<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once 'clients/base/api/FilterApi.php';
require_once 'include/SugarQuery/SugarQuery.php';
require_once 'modules/pmse_Inbox/engine/PMSE.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';
class PMSECasesListApi  extends FilterApi
{
    public function __construct(){
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
                'path' => array('pmse_Inbox','casesList'),
                'pathVars'=> array('module','casesList'),
                'method' => 'selectCasesList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
                ),
                'getLoadLogs' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox','getLog','?'),
                'pathVars'=> array('module','getLog','typelog'),
                'method' => 'selectLogLoad',
                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
                ),
                'getConfigLogs' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox','logGetConfig'),
                'pathVars'=> array('module','logGetConfig'),
                'method' => 'configLogLoad',
                'jsonParams' => array(),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
                ),
                'setConfigLogs' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox','logSetConfig'),
                'pathVars'=> array('module',''),
                'method' => 'configLogPut',
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
        $flowQuery->select->fieldRaw('count(f.cas_flow_status)','flow_count');
        $flowQuery->where()
            ->equals('f.cas_flow_status', 'ERROR');
        $flowQuery->where()->queryAnd()
            ->addRaw("f.cas_id=a.cas_id");


        $q = new SugarQuery();
        $inboxBean = BeanFactory::getBean('pmse_Inbox');
        if($args['order_by']=='cas_due_date:asc')
        {
            $args['order_by']='cas_create_date:asc';
        }
        $options = self::parseArguments($api, $args, $inboxBean);
        $fields = array(
            'a.*'
        );
        $q->select($fields);
        $q->from($inboxBean, array('alias' => 'a'));
        $q->joinRaw('INNER JOIN users u ON a.created_by=u.id');
        $q->select->fieldRaw('u.last_name','last_name');
        //Flow query breaks on mssql due to the use of row_number() / count in a subselect which is not supported
        //Doesn't appear to be used.
        //$q->select->fieldRaw('('.$flowQuery->compileSql().')','flow_error');
        if (!empty($args['q'])) {
            if($args['module_list']=='all')
            {
                $q->where()->queryAnd()
                    ->addRaw("a.cas_title LIKE '%".$args['q']."%' OR a.pro_title LIKE '%".$args['q']."%' OR a.cas_status LIKE '%".$args['q']."%' OR last_name LIKE '%".$args['q']."%'");
            }
            else
            {
                if($args['module_list']=='Cases Title')
                {
                    $q->where()->queryAnd()
                        ->addRaw("a.cas_title LIKE '%".$args['q']."%'");
                }
                if($args['module_list']=='Process Name')
                {
                    $q->where()->queryAnd()
                        ->addRaw("a.pro_title LIKE '%".$args['q']."%'");
                }
                if($args['module_list']=='Status')
                {
                    $q->where()->queryAnd()
                        ->addRaw("a.cas_status LIKE '%".$args['q']."%'");
                }
                if($args['module_list']=='Owner')
                {
                    $q->where()->queryAnd()
                        ->addRaw("last_name LIKE '%".$args['q']."%'");
                }
            }
        }
        foreach ($options['order_by'] as $orderBy) {

            $q->orderBy($orderBy[0], $orderBy[1]);
        }
        // Add an extra record to the limit so we can detect if there are more records to be found
        $q->limit($options['limit']);
        $q->offset($options['offset']);

        $offset=$options['offset']+$options['limit'];
        $count=0;
        $list = $q->execute();
        foreach ($list as $key => $value) {
            if($value["cas_status"]==='TODO'){
                $list[$key]["cas_status"]='<data class="label label-Leads">'.$value["cas_status"].'</data>';
            }elseif($value["cas_status"]==='COMPLETED' || $value["cas_status"]==='TERMINATED'){
                $list[$key]["cas_status"]='<data class="label label-success">'.$value["cas_status"].'</data>';
            }elseif($value["cas_status"]==='CANCELLED'){
                $list[$key]["cas_status"]='<data class="label label-warning">'.$value["cas_status"].'</data>';
            }else{
                $list[$key]["cas_status"]='<data class="label label-important">'.$value["cas_status"].'</data>';
            }

//            if($value["flow_error"]!='0')
//            {
//                $list[$key]["cas_status"]='<data class="label label-important">ERROR</data>';
////                $list[$key]["execute"] = 'Execute';
//            }
            $count++;
        }
        if($count==$options['limit']){
            $offset=$options['offset']+$options['limit'];
        }else{
            $offset=-1;
        }

        $data = array();
        $data['next_offset'] = $offset;
        $data['records'] = $list;
//        $data['options'] = $options;
//        $data['args'] = $args;
        $data['sql'] = $q->compileSql();
        return $data;
    }

    public function selectLogLoad($api, $args)
    {
        $logger = PMSELogger::getInstance();
        $pmse = PMSE::getInstance();

        $showSugarCrm = false;

        if ($args['typelog']=='sugar') {
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
        if(empty($list))
        {
            $bean = BeanFactory::newBean('pmse_BpmConfig');
            $bean->cfg_value = 'warning';
            $bean->name = 'logger_level';
            $bean->description='Logger Level';
            $bean->save();

            $list=array(0=>array('cfg_value'=>'warning'));
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
            ->retrieve_by_string_fields(array('cfg_status'=>'ACTIVE','name'=>'logger_level'));
        $bean->cfg_value = $data;
        $bean->save();

        return array('success'=>true);
    }
} 