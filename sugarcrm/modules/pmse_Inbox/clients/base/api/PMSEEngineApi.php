<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/api/SugarApi.php');
require_once('modules/pmse_Inbox/engine/PMSE.php');
//require_once('modules/pmse_Inbox/engine/PMSEEngine.php');
require_once('modules/pmse_Inbox/engine/PMSEHistoryData.php');
require_once('modules/pmse_Inbox/engine/wrappers/PMSEHistoryLogWrapper.php');
require_once('modules/pmse_Inbox/engine/PMSEHandlers/PMSEDirectRequestHandler.php');
require_once('modules/pmse_Inbox/engine/PMSEHandlers/PMSEEngineRequestHandler.php');
require_once('modules/pmse_Inbox/engine/PMSEHandlers/PMSECaseFlowHandler.php');
require_once('modules/pmse_Inbox/engine/PMSEHandlers/PMSEUserAssignmentHandler.php');
require_once('modules/pmse_Inbox/engine/wrappers/PMSECaseWrapper.php');
require_once('modules/pmse_Project/clients/base/api/wrappers/PMSEWrapper.php');

/*
 * Record List API implementation
 */

class PMSEEngineApi extends SugarApi
{

    private $caseFlowHandler;
    private $userAssignmentHandler;
    private $requestHandler;

    public function __construct()
    {
        $this->caseFlowHandler = new PMSECaseFlowHandler();
        $this->userAssignmentHandler = new PMSEUserAssignmentHandler();
        $this->pmse = PMSE::getInstance();
        $this->wrapper = new PMSEWrapper();
        $this->requestHandler = new PMSEDirectRequestHandler();
        $this->caseWrapper = new PMSECaseWrapper();
    }

    public function registerApiRest()
    {
        return array(
            'recordListCreate' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'engine_route'),
                'pathVars' => array('module', ''),
                'jsonParams' => array('filter'),
                'method' => 'engineRoute',
                'shortHelp' => 'An API to route a case of ProcessMaker',                
                'longHelp' => 'include/api/help/module_engine_route_put.html',
                'keepSession' => true
            ),
            'engineClaim' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'engine_claim'),
                'pathVars' => array('module', ''),
                'method' => 'engineClaim',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'historyLogList' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox','historyLog','?'),
                'pathVars' => array('module','','filter'),
                'method' => 'retrieveHistoryLog',                
                'shortHelp' => 'retrieve history log',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_recordlist_delete.html',
            ),
            'noteList' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox','note_list','?'),
                'pathVars' => array('module','','cas_id'),
                'method' => 'getNotes',                
                'shortHelp' => 'retrieve history log',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_recordlist_delete.html',
            ),
            'savenoteList' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Inbox','save_notes'),
                'pathVars' => array('module',''),
                'method' => 'saveNotes',                
                'shortHelp' => 'retrieve history log',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_recordlist_delete.html',
            ),
            'deletenoteList' => array(
                'reqType' => 'DELETE',
                'path' => array('pmse_Inbox', 'delete_notes', '?'),
                'pathVars' => array('module', '', 'id'),
                'method' => 'deleteNotes',
                'shortHelp' => 'retrieve history log',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_recordlist_delete.html',
            ),
            'saveReassignRecord' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'ReassignForm'),
                'pathVars' => array('module', ''),
                'method' => 'reassignRecord',
                'shortHelp' => 'An API to claim a case of ProcessMaker',               
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'saveAdhocReassign' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'AdhocReassign'),
                'pathVars' => array('module', ''),
                'method' => 'adhocReassign',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'getReassignRecord' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'ReassignForm', '?', '?'),
                'pathVars' => array('module', '', 'data', 'flowId'),
                'method' => 'getReassign',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'getAdhocReassign' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'AdhocReassign', '?', '?'),
                'pathVars' => array('module', '', 'data', 'flowId'),
                'method' => 'getAdhoc',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'getChangeCaseUser' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'changeCaseUser', '?'),
                'pathVars' => array('module', '', 'cas_id'),
                'method' => 'changeCaseUser',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'getUserListByTeam' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'userListByTeam', '?'),
                'pathVars' => array('module', '', 'id'),
                'method' => 'userListByTeam',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'updateChangeCaseFlow' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'updateChangeCaseFlow'),
                'pathVars' => array('module', ''),
                'method' => 'updateChangeCaseFlow',
                'shortHelp' => 'An API to claim a case of ProcessMaker',
                'longHelp' => 'include/api/help/module_engine_claim_put.html',
                'keepSession' => true
            ),
            'reactivateFlows' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'reactivateFlows'),
                'pathVars' => array('module', ''),
                'jsonParams' => array('filter'),
                'method' => 'reactivateFlows',
                'shortHelp' => 'The API method used to reactivate erring cases',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
            'reassignFlows' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'reassignFlows'),
                'pathVars' => array('module', ''),
                'jsonParams' => array('filter'),
                'method' => 'reassignFlows',
                'shortHelp' => 'The API method is used to reassign a flow to a new user.',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
            'getReassignFlows' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'reassignFlows', '?'),
                'pathVars' => array('module', '', 'record'),
                'jsonParams' => array('filter'),
                'method' => 'getReassignFlows',
                'shortHelp' => 'The API method is used to reassign a flow to a new user.',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
            'getModuleCase' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'case', '?', '?'),
                'pathVars' => array('module', 'case', 'id', 'idflow'),
                'method' => 'selectCase',
                //'jsonParams' => array('filter'),
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
                'keepSession' => true
            ),
            'cancelCases' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'cancelCases'),
                'pathVars' => array('module', ''),
                'jsonParams' => array('filter'),
                'method' => 'cancelCase',
                'shortHelp' => 'The API method used to cancel active cases',
                'keepSession' => true
            ),
            'getUnattendedCases' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'unattendedCases'),
                'pathVars' => array('module', ''),
                'method' => 'getUnattendedCases',
                'shortHelp' => 'The API method is used to reassign a flow to a new user.',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
            'getSettingsEngine' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'settings'),
                'pathVars' => array('module', ''),
                'method' => 'getSettingsEngine',
                'shortHelp' => 'The API method is used to reassign a flow to a new user.',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
            'putSettingsEngine' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Inbox', 'settings'),
                'pathVars' => array('module', ''),
                'method' => 'putSettingsEngine',
                'shortHelp' => 'The API method is used to reassign a flow to a new user.',
                'keepSession' => true
                //'longHelp' => 'include/api/help/module_engine_claim_put.html'
            ),
        );
    }

    public function getNotes($api, $args)
    {
        $notesBean = BeanFactory::newBean('pmse_BpmNotes'); // new BpmNotes();

        $where = 'cas_id = ' . $args['cas_id'] . ' AND pmse_bpm_notes.deleted = 0';

        $joinTables = array(
            array('INNER', 'users', 'pmse_bpm_notes.not_user_id = users.id')
        );

        $selects = array('pmse_bpm_notes.*', 'users.first_name', 'users.last_name', 'users.picture');

        $records = $this->wrapper->getSelectRows($notesBean, 'date_entered ASC', $where, 0, -1, -1, $selects,
            $joinTables);
        $records['currentDate'] = TimeDate::getInstance()->nowDb();
        return $records;
    }

    public function saveNotes($api, $args)
    {
        global $current_user;
        //Create Notes
        $data = $args['data'];
        $notes = BeanFactory::newBean('pmse_BpmNotes');
        $notes->cas_id = $data['cas_id'];
        $notes->cas_index = $data['cas_index'];
        $notes->not_user_id = $current_user->id;
        $notes->not_user_recipient_id = null;
        $notes->not_type = (isset($data['not_type']) && !empty($data['not_type']) ? $data['not_type'] : 'GENERAL');
        $notes->not_date = '';
        $notes->not_status = 'ACTIVE';
        $notes->not_availability = '';
        $notes->not_content = $data['not_content'];
        $notes->not_recipients = '';
        $notes->save();
        return array('success' => true, 'id' => $notes->id, 'date_entered' => $notes->date_entered);
    }

    public function deleteNotes($api, $args)
    {
        $notesBean = BeanFactory::getBean('pmse_BpmNotes');
        $notesBean->mark_deleted($args['id']);
        return array('id' => $args['id']);
    }

    public function retrieveHistoryLog($api, $args)
    {
        $historyLog = new PMSEHistoryLogWrapper();
        $res = $historyLog->_get($args);
        return array('success' => true, 'result' => $res->result);
    }

    public function engineRoute($api, $args)
    {
        // The handler will call to the preprocessor in this step
        $this->retrieveRequestHandler('direct')->executeRequest($args, false, null, strtoupper($args['frm_action']));
        $taskName = $args['taskName'];
        $message = '';
        global $current_user;
        require_once 'modules/pmse_Inbox/engine/PMSELogger.php';
        $log = PMSELogger::getInstance();
        if (empty($args['full_name'])) {
            $args['full_name'] = $args['name'];
        }
        $params['tags'] = array(
            array(
                "id" => $args['beanId'],
                "name" => $args['full_name'],
                "module" => $args['moduleName']
            ),
            array(
                "id" => $current_user->id,
                "name" => $current_user->full_name,
                "module" => "Users"
            )
        );
        $params['module_name'] = 'pmse_Inbox';
        if ($args['frm_action'] == 'Approve') {
            $message = sprintf(translate('LBL_PMSE_ACTIVITY_STREAM_APPROVE', $params['module_name']), $taskName);
        } elseif ($args['frm_action'] == 'Reject') {
            $message = sprintf(translate('LBL_PMSE_ACTIVITY_STREAM_REJECT', $params['module_name']), $taskName);
        } elseif ($args['frm_action'] == 'Route') {
            $message = sprintf(translate('LBL_PMSE_ACTIVITY_STREAM_ROUTE', $params['module_name']), $taskName);
        }
        $log->activity($message, $params);
        // return the success request array
        return array('success' => true);
    }

    public function engineClaim($api, $args)
    {
        global $db;
        $cas_id = $args['cas_id'];
        $cas_index = $args['cas_index'];
        $taskName = $args['taskName'];
        $today = date('Y-m-d H:i:s');

        $query = "select cas_flow_status, cas_started, bpmn_type, bpmn_id " .
                " from pmse_bpm_flow where cas_id = $cas_id and cas_index = $cas_index ";
        $result = $db->Query($query);
        $row = $db->fetchByAssoc($result);
        $cas_flow_status = $row['cas_flow_status'];
        $cas_started = $row['cas_started'];
        $bpmn_type = $row['bpmn_type'];
        $bpmn_id = $row['bpmn_id'];

        if ($cas_started != 1) {
            //get the bpm_activity_definition record, to check if it is SELFSERVICE
            $isSelfService = '';
            if ($cas_flow_status == 'FORM' && $bpmn_type == 'bpmnActivity') {
                $queryAct = "select act_assignment_method from pmse_bpm_activity_definition where id = '$bpmn_id'";
                $resultAct = $db->Query($queryAct);
                $rowAct = $db->fetchByAssoc($resultAct);
                $assign_method = trim($rowAct['act_assignment_method']);
                if ($assign_method == 'selfservice') {
                    global $current_user;
                    $isSelfService = ", cas_user_id = '" . $current_user->id . "' ";
                }
            }

            $query = "update pmse_bpm_flow set " .
                    " cas_start_date = '$today', " .
                    " cas_started    = 1 " .
                    $isSelfService .
                    " where cas_id = $cas_id and cas_index = $cas_index ";

            global $current_user;
            require_once 'modules/pmse_Inbox/engine/PMSELogger.php';
            $log = PMSELogger::getInstance();
            if (empty($args['full_name'])) {
                $args['full_name'] = $args['name'];
            }
            if (isset($args['moduleName'])) {
                $module = $args['moduleName'];
            } else {
                $module = $args['module'];
            }
            
            $params['tags'] = array(
                array(
                    "id" => $args['beanId'],
                    "name" => $args['full_name'],
                    "module" => $module
                ),
                array(
                    "id" => $current_user->id,
                    "name" => $current_user->full_name,
                    "module" => "Users"
                )
            );
            $params['module_name'] = 'pmse_Inbox';
            $log->activity(sprintf(translate('LBL_PMSE_ACTIVITY_STREAM_CLAIM', $params['module_name']), $taskName),
                $params);


            if ($db->query($query, true, "Error updating pmse_bpm_flow record ")) {
                return array('success' => false);
            }
        }
        //$readable = $cas_id . '-' . $cas_index;
        //return PMSEEngineUtils::simpleEncode($readable);
        return array('success' => true);
    }

    public function reassignRecord($api, $args)
    {
        $case = $args['data'];

        $cas_id = $case['cas_id'];
        $cas_index = $case['cas_index'];
        $caseData['cas_id'] = $cas_id;
        $caseData['cas_index'] = $cas_index;
        $case['not_type'] = "REASSIGN";
        $case['frm_comment'] = $case['reassign_comment'];
        $case['not_user_recipient_id'] = $case['reassign_user'];
        $case['cas_index'] = $cas_index;
        $this->caseFlowHandler->saveFormAction($case);
        $this->userAssignmentHandler->reassignRecord($caseData, $case['reassign_user']);
        return $case;
    }

    public function adhocReassign($api, $args)
    {
        $case = $args['data'];
        $result = array('success' => true);
        $bean = BeanFactory::retrieveBean($case['moduleName'], $case['beanId']);
        // The handler will call to the preprocessor in this step
        $this->retrieveRequestHandler('direct')->executeRequest($case, false, $bean, 'REASSIGN');
        return $result;
    }

    public function getReassign($api, $args)
    {
        $flowBeanObject = BeanFactory::getBean('pmse_BpmFlow', $args['flowId']);
        $args['cas_id'] = $flowBeanObject->cas_id;
        $args['cas_index'] = $flowBeanObject->cas_index;
        $result = array();
        $result['success'] = false;
        if (empty($args['cas_id']) && empty($args['cas_index'])) {
            return $result;
        }
        switch ($args['data']) {
            case 'users':
                $result['result'] = $this->getUsersListReassign($args);
                $result['success'] = true;
                break;
            default:
                $result['data'] = $this->getFormDataReassign($args);
                $result['success'] = true;
                break;
        }
        return $result;
    }

    /**
     * This method gets the user list by case id and case index.
     * That user list is needed for reasign to listed user
     * @param array $args
     * @return array
     */
    public function getUsersListReassign($args)
    {
        $resultArray = array();
//        $userList = $this->engine->getReassignableUserList($args['cas_id'], $args['cas_index']);
        $userList = $this->userAssignmentHandler->getAssignableUserList($args['cas_id'], $args['cas_index']);
        foreach ($userList as $user) {
            $tmpArray = array();
            $tmpArray['value'] = $user->id;
            $tmpArray['text'] = $user->full_name;
            $resultArray[] = $tmpArray;
        }
        return $resultArray;
    }

    /**
     * This method gets the form data from BpmnActivity or BpmnEvent, for purposes of reassignment of the case
     * @param array $args
     * @return array
     */
    public function getFormDataReassign($args)
    {
        $bpmFlow = new BpmFlow();
        $orderBy = '';
        $where = "cas_id='{$args['cas_id']}' AND cas_index='{$args['cas_index']}'";
        $joinedTables = array(
            array('INNER', 'bpmn_process', 'bpmn_process.pro_id=bpm_flow.pro_id'),
        );
        $flowList = $bpmFlow->getSelectRows($orderBy, $where, 0, -1, -1, array(), $joinedTables);
        foreach ($flowList['rowList'] as $flow) {
            switch ($flow['bpmn_type']) {
                case 'bpmnActivity':
                    $objectBean = new BpmnActivity();
                    $objectBean->retrieve_by_string_fields(array('act_id' => $flow['bpmn_id']));
                    $taskName = $objectBean->act_name;
                    break;
                case 'bpmnEvent':
                    $objectBean = new BpmnEvent();
                    $objectBean->retrieve_by_string_fields(array('evn_id' => $flow['bpmn_id']));
                    $taskName = $objectBean->evn_name;
                    break;
                default:
                    break;
            }
            $processName = $flow['pro_name'];
        }
        return array("process_name" => $processName, "process_task" => $taskName);
    }

    /**
     * GET data with client object.
     * Returns an object by id, with all user list, and form data. that id is passed into an array named args.
     * return the object constructed with the success attribute set to true if
     * records are obtained,the method returns false otherwise
     * @param array $args
     * @return object
     */
    public function getAdhoc($api, $args)
    {
        $flowBeanObject = BeanFactory::getBean('pmse_BpmFlow', $args['flowId']);
        $args['cas_id'] = $flowBeanObject->cas_id;
        $args['cas_index'] = $flowBeanObject->cas_index;
        $result = array();
        $result['success'] = false;
        if (empty($args['cas_id']) && empty($args['cas_index'])) {
            return $result;
        }
        switch ($args['data']) {
            case 'users':
                $result['result'] = $this->getUsersListAdhoc($args);
                $result['success'] = true;
                break;
            default:
                $result['data'] = $this->getFormDataAdhoc($args);
                $result['success'] = true;
                break;
        }
        return $result;
    }

    /**
     * This method gets the user list by case id and case index.
     * That user list is needed for reasign to listed user
     * @param array $args
     * @return array
     */
    public function getUsersListAdhoc($args)
    {
        $resultArray = array();
        $this->userAssignmentHandler->getAdhocAssignableUserList($args['cas_id'], $args['cas_index']);
//        $userList = $this->engine->getAdhocAssignableUserList($args['cas_id'], $args['cas_index']);
        $userList = $this->userAssignmentHandler->getAdhocAssignableUserList($args['cas_id'], $args['cas_index']);
        foreach ($userList as $user) {
            $tmpArray = array();
            $tmpArray['value'] = $user->id;
            $tmpArray['text'] = $user->full_name;
            $resultArray[] = $tmpArray;
        }
        return $resultArray;
    }

    /**
     * This method gets the form data from BpmnActivity or BpmnEvent, for purposes of reassignment of the case
     * @param array $args
     * @return array
     */
    public function getFormDataAdhoc($args)
    {
        $bpmFlow = BeanFactory::getBean('pmse_BpmFlow'); //new BpmFlow();
        $orderBy = '';
        $where = "cas_id='{$args['cas_id']}' AND cas_index='{$args['cas_index']}'";
        $joinedTables = array(
            array('INNER', 'bpmn_process', 'bpmn_process.pro_id=bpm_flow.pro_id'),
        );
        $flowList = $bpmFlow->getSelectRows($orderBy, $where, 0, -1, -1, array(), $joinedTables);
        foreach ($flowList['rowList'] as $flow) {
            switch ($flow['bpmn_type']) {
                case 'bpmnActivity':
                    $objectBean = new BpmnActivity();
                    $objectBean->retrieve_by_string_fields(array('act_id' => $flow['bpmn_id']));
                    $taskName = $objectBean->act_name;
                    break;
                case 'bpmnEvent':
                    $objectBean = new BpmnEvent();
                    $objectBean->retrieve_by_string_fields(array('evn_id' => $flow['bpmn_id']));
                    $taskName = $objectBean->evn_name;
                    break;
                default:
                    break;
            }
            $processName = $flow['pro_name'];
        }
        return array("process_name" => $processName, "process_task" => $taskName);
    }

    /**
     * @param $args
     * @return object
     */
    public function changeCaseUser($api, $args)
    {
        $time_data = $GLOBALS['timedate'];
        global $current_user;
        global $db;
        $res = array(); //new stdClass();
        $res['success'] = true;
        $get_actIds = "SELECT pmse_inbox.name,pmse_inbox.cas_id,cas_user_id,cas_delegate_date, cas_due_date, act_expected_time, act_assignment_method, act_assign_team, cas_index, act_assignment_method FROM pmse_inbox
                        LEFT JOIN pmse_bpm_flow ON pmse_inbox.cas_id = pmse_bpm_flow.cas_id
                        LEFT JOIN pmse_bpmn_activity ON pmse_bpm_flow.bpmn_type = 'bpmnActivity' and pmse_bpm_flow.bpmn_id  = pmse_bpmn_activity.id
                        INNER JOIN pmse_bpm_activity_definition ON pmse_bpmn_activity.id = pmse_bpm_activity_definition.id
                        WHERE pmse_inbox.cas_id = " . $args['cas_id'] . " AND cas_flow_status = 'FORM';";
        $result = $db->query($get_actIds);
        $tmpArray = array();
//        $tmpArray[] = array ('value'=>'is_admin', 'text'=>translate('LBL_PMSE_FORM_OPTION_ADMINISTRATOR'));
        while ($row = $db->fetchByAssoc($result)) {
            $time = json_decode(base64_decode($row['act_expected_time']));
            $tmpArray[] = array(
                'act_name' => $row['act_name'],
                'cas_id' => $row['cas_id'],
                'cas_user_id' => $row['cas_user_id'],
                'cas_delegate_date' => $time_data->to_display_date_time($row['cas_delegate_date'], true, true,
                    $current_user),
                'cas_due_date' => $time_data->to_display_date_time($row['cas_due_date'], true, true, $current_user),
                'act_assignment_method' => $row['act_assignment_method'],
                'act_assign_team' => $row['act_assign_team'],
                'cas_index' => $row['cas_index'],
                'act_expected_time' => $time->time . ' ' . $time->unit,
            );
        }
        $res['result'] = $tmpArray;
        return $res;
    }

    /**
     * Method that returns the user roles as Sugar
     * @return object
     */
    public function userListByTeam($api, $args)
    {
        global $db;
        $res = array(); //new stdClass();
        $res['success'] = true;
        $teams = (isset($args['id']) && !empty($args['id'])) ? "AND teams.id ='" . $args['id'] . "'" : '';
        $get_actIds = "SELECT DISTINCT(users.id) as id,first_name,last_name  FROM teams
                        LEFT JOIN team_memberships ON team_id = teams.id
                        INNER JOIN users ON users.id = team_memberships.user_id
                        WHERE private = 0 " . $teams;
        $result = $db->query($get_actIds);
        $tmpArray = array();
//        $tmpArray[] = array ('value'=>'is_admin', 'text'=>translate('LBL_PMSE_FORM_OPTION_ADMINISTRATOR'));
        while ($row = $db->fetchByAssoc($result)) {
//            $tmpArray[] = array( 'value' => $row['id'], 'text' => $row['first_name'] . ' ' . $row['last_name'] );
            $tmpArray[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }
        $res['result'] = $tmpArray;
        return $res;
    }

    /**
     * 
     * @global type $db
     * @global type $current_user
     * @param type $api
     * @param type $args
     * @return boolean
     */
    public function updateChangeCaseFlow($api, $args)
    {
        global $db;
        $res = array(); //new stdClass();
        $res['success'] = true;
        global $current_user;
        foreach ($args['data'] as $value) {
            if (is_array($value)) {
//                $update_activity = "update bpm_flow set cas_user_id = '".$value['cas_user_id']."' where cas_id = ".$value['cas_id']." and cas_index = ".$value['cas_index'].";";
//                $resultUpdate = $db->Query($update_activity);
                $to = $value['cas_user_id'];
                $from = $value['old_cas_user_id'];
                $cas_id = $value['cas_id'];
                $cas_index = $value['cas_index'];
                //$cas_index = $value['cas_index'];

                $flowBean = BeanFactory::getBean('pmse_BpmFlow'); //new BpmFlow();
                //$flows = $flowBean->getSelectRows('', "cas_id = '$cas_id' and cas_index = '$cas_index'");
                $flows = $this->wrapper->getSelectRows($flowBean, '', "cas_id = '$cas_id' and cas_index = '$cas_index'",
                    0, -1, -1);
                //
                $update_activity = "update pmse_bpm_flow set cas_flow_status = 'CLOSED', cas_user_id = $current_user->id where cas_id = " . $value['cas_id'] . " and cas_index = " . $value['cas_index'] . ";";
                $resultUpdate = $db->Query($update_activity);

                $query = "SELECT MAX(cas_index) as cas_index FROM pmse_bpm_flow WHERE cas_id = $cas_id";
                $result = $db->Query($query);
                $row = $db->fetchByAssoc($result);
                //create a BpmFlow row
                $flow = BeanFactory::getBean('pmse_BpmFlow'); //new BpmFlow();
                $flow->cas_id = $cas_id;
                $flow->cas_index = $row['cas_index'] + 1;
                $flow->cas_previous = $flows['rowList'][0]['bpmn_id'];
                $flow->pro_id = $flows['rowList'][0]['pro_id'];
                $flow->bpmn_id = $flows['rowList'][0]['bpmn_id'];
                $flow->bpmn_type = $flows['rowList'][0]['bpmn_type'];
                $flow->cas_user_id = $to;
                $flow->cas_thread = $flows['rowList'][0]['cas_thread'];
                $flow->cas_flow_status = $flows['rowList'][0]['cas_flow_status'];
                $flow->cas_sugar_module = $flows['rowList'][0]['cas_sugar_module'];
                $flow->cas_sugar_object_id = $flows['rowList'][0]['cas_sugar_object_id'];
                $flow->cas_sugar_action = $flows['rowList'][0]['cas_sugar_action'];
                $flow->cas_delegate_date = ($to != $from) ? date('Y-m-d H:i:s') : $flows['rowList'][0]['cas_delegate_date']; //$flows['rowList'][0]['cas_delegate_date'];
                $flow->cas_start_date = $flows['rowList'][0]['cas_start_date']; //all start events are started inmediately
                $flow->cas_finish_date = $flows['rowList'][0]['cas_finish_date'];
                $ts1 = strtotime($flows['rowList'][0]['cas_delegate_date']);
                $ts2 = strtotime($flows['rowList'][0]['cas_due_date']);
                $today = strtotime(date('Y-m-d H:i:s'));
                $expectedTime = $ts2 - $ts1;
                //$expectedTime = date_diff($flows['rowList'][0]['cas_delegate_date'], $flows['rowList'][0]['cas_due_date']);
                (!empty($expectedTime)) ? $dueDate = date('Y-m-d H:i:s', $today + $expectedTime) : $dueDate = null;
                $flow->cas_due_date = ($to != $from) ? $dueDate : $flows['rowList'][0]['cas_due_date'];
                $flow->cas_queue_duration = $flows['rowList'][0]['cas_queue_duration'];
                $flow->cas_duration = $flows['rowList'][0]['cas_duration'];
                $flow->cas_delay_duration = $flows['rowList'][0]['cas_delay_duration'];
                $flow->cas_started = $flows['rowList'][0]['cas_started']; //all start events are started inmediately
                $flow->cas_finished = $flows['rowList'][0]['cas_finished'];
                $flow->cas_delayed = $flows['rowList'][0]['cas_delayed'];
                $flow->new_with_id = true;
                $flow->save();

                //$logger = new ADAMLogger();
                //$logger->log('INFO', "The admin has changed the assigned user [$from] by [$to], for case task with id [$cas_id]");
            }
        }
        return $res;
    }

    /**
     * Reactivate/Re-execute a flow list, the list is passed in the as an array
     * of id's in the $args['cas_id'] parameter.
     * @param type $api
     * @param type $args
     * @return boolean
     */
    public function reactivateFlows($api, $args)
    {
        $result = array('success' => true);
        foreach ($args['cas_id'] as $value) {
            $val['cas_id'] = $value;
            // The handler will call to the preprocessor in this step
            $this->retrieveRequestHandler('reactivate')->executeRequest($val, false, null, 'RESUME_EXECUTION');
        }
        // return the success request array
        return $result;
    }

    /**
     * Retrieve a request handler based on the request type.
     * @param type $type
     * @return \PMSEEngineRequestHandler|\PMSEDirectRequestHandler
     */
    public function retrieveRequestHandler($type)
    {
        switch ($type) {
            case 'reactivate':
                return new PMSEEngineRequestHandler();
                break;
            case 'direct':
            default:
                return new PMSEDirectRequestHandler();
                break;
        }
    }

    /**
     * Cancel a case or multiple cases based on the $args['cas_id'] parameter
     * that should be an array of cases id's the case to be cancelled it shouldn't
     * be with CANCELLED, TERMINATED or COMPLETED status.
     * @param type $api
     * @param type $args
     * @return type
     */
    public function cancelCase($api, $args)
    {
        $result = array('success' => true);
        try {
            foreach ($args['cas_id'] as $id) {
                $flowBean = BeanFactory::retrieveBean('pmse_BpmFlow');
                $inboxBean = BeanFactory::retrieveBean('pmse_Inbox');
                $inboxBean->retrieve_by_string_fields(array('cas_id' => $id));
                $flowBean->retrieve_by_string_fields(array('cas_id' => $id));
                $bean = BeanFactory::retrieveBean($flowBean->cas_sugar_module, $flowBean->cas_sugar_object_id);
                $auxCasId['cas_id'] = $id;
                if (($inboxBean->cas_status != 'CANCELLED' && $inboxBean->cas_status != 'TERMINATED' && $inboxBean->cas_status != 'COMPLETED')) {
                    $this->caseFlowHandler->terminateCase($auxCasId, $bean, 'CANCELLED');
                }

            }
        } catch (Exception $ex) {
            $result = array('success' => false, 'message' => $ex->getMessage());
        }
        return $result;
    }

    public function reassignFlows($api, $args)
    {
        $result = array('success' => true);
        try {
            foreach ($args['flow_data'] as $flow) {
                $this->userAssignmentHandler->reassignCaseToUser($flow, $flow['user_id']);
            }
        } catch (Exception $ex) {
            $result = array('success' => false, 'message' => $ex->getMessage());
        }
        return $result;
    }

    public function getReassignFlows($api, $args)
    {
        $result = array('success' => true);
        //$result['args'] = $args;
        $bpmFlow = BeanFactory::retrieveBean('pmse_BpmFlow');
        //$rows = $bpmFlow->get_full_list('','cas_id = ' . $args['record'] . ' AND cas_flow_status = \'FORM\'');
        $queryOptions = array('add_deleted' => (!isset($options['add_deleted']) || $options['add_deleted']) ? true : false);
        if ($queryOptions['add_deleted'] == false) {
            $options['select'][] = 'deleted';
        }
        $q = new SugarQuery();
        $q->from($bpmFlow, $queryOptions);
        $q->distinct(false);
        $fields = array(
            'cas_id',
            'cas_index',
            'cas_delegate_date',
            'cas_flow_status',
            'cas_user_id',
            'cas_due_date',
            'bpmn_id'
        );

        $q->where()
                ->equals('cas_flow_status', 'FORM');

        if (!empty($args['record'])) {
            $q->where()
                    ->equals('cas_id', $args['record']);
        }

        //INNER JOIN BPMN ACTIVITY DEFINITION
        $q->joinTable('pmse_bpmn_activity', array('alias' => 'activity', 'joinType' => 'INNER', 'linkingTable' => true))
                ->on()
                ->equalsField('activity.id', 'bpmn_id')
                ->equals('activity.deleted', 0);
        $fields[] = array("activity.name", 'act_name');

        //INNSER JOIN BPMN ACTIVITY DEFINTION
        $q->joinTable('pmse_bpm_activity_definition',
            array('alias' => 'activity_definition', 'joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('activity_definition.id', 'activity.id')
            ->equals('activity_definition.deleted', 0);
        $fields[] = array("activity_definition.act_assignment_method", 'act_assignment_method');

        $q->select($fields);

        //$result['sql']= $q->compileSql();

        $rows = $q->execute();
        $rows_aux = array();
        foreach ($rows as $key => $row) {
            $userList = $this->getUsersForReassign(array(
                "user_id" => $row["cas_user_id"],
                "act_assignment_method" => $row["act_assignment_method"]
            ));
            $user = BeanFactory::getBean("Users", $row["cas_user_id"]);
            $rows[$key]['assigned_user'] = $user->full_name;
//            $rows[$key]['cas_reassign_user_combo_box'] = $userList;
            if (isset($args['unattended']) && !empty($args['unattended'])) {
                if (!($user->status != 'Active' || $user->employee_status != 'Active')) {
                    unset($rows[$key]);
                }
            }
        }

        if (!empty($args['q'])) {
            foreach ($rows as $key => $row) {
                if (strstr(strtolower($row['assigned_user']), strtolower($args['q']))) {
                    $rowsLoad = $rows;
                }
            }
            $rows = $rowsLoad;
        }

        $rows = array_values($rows);
        $result['records'] = $rows;

        return $result;
    }

    private function getUsersForReassign($options)
    {
        $result = array();
        if (isset($options['act_assignment_method']) &&
            !empty($options['act_assignment_method']) &&
            $options['act_assignment_method'] == 'selfservice'
        ) {
            $teamsBean = BeanFactory::getBean('Teams', $options['user_id']);
            if ($teamsBean->fetched_row) {
                foreach ($teamsBean->get_team_members(true) as $user) {
                    if ($options['user_id'] != $user->id) {
                        $result[$user->id] = $user->full_name;
                    }
                }
            } else {
                $result = $this->getUsersForReassign(array(
                        'user_id' => $options['user_id'],
                        'act_assignment_method' => 'static'
                    ));
            }
        } else {
            $teamsBean = new Team();
            $teams = $teamsBean->get_teams_for_user($options['user_id']);
            if (!empty($teams)) {
                $arrayUsers = array();
                foreach ($teams as $key => $value) {
                    if ($value->private == 0) {
                        foreach ($value->get_team_members(true) as $user) {
                            if ($options['user_id'] != $user->id) {
                                $result[$user->id] = $user->full_name;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getUnattendedCases($api, $args)
    {
        $queryOptions = array('add_deleted' => true);

        $arrayUnattendedCases = $this->getUnattendedCasesByFlow();
        //Get Cases IN TODO
        $beanInbox = BeanFactory::getBean('pmse_Inbox');
        $fields = array(
            'id',
            'assigned_user_id',
            'date_modified',
            'date_entered',
            'name',
            'cas_id',
            'cas_title',
            'cas_status',
            'pro_title',
            'cas_init_user'
        );
        $q = new SugarQuery();
        $q->from($beanInbox, $queryOptions);
        $q->distinct(false);
        $q->where()
                ->equals('cas_status', 'IN PROGRESS');

        $q->select($fields);
        if ($args['module_list'] == 'all' && !empty($args['q'])) {
            $q->where()->queryAnd()
                ->addRaw("pmse_inbox.cas_title LIKE '%" . $args['q'] . "%' OR pmse_inbox.pro_title LIKE '%" . $args['q'] . "%' ");
        } else {
            if ($args['module_list'] == 'Cases Title') {
                $q->where()->queryAnd()
                    ->addRaw("pmse_inbox.cas_title LIKE '%" . $args['q'] . "%'");
            } elseif ($args['module_list'] == 'Process Name') {
                $q->where()->queryAnd()
                    ->addRaw("pmse_inbox.pro_title LIKE '%" . $args['q'] . "%'");
            }
        }

        $rows = $q->execute();

        $rows_aux = array();

        foreach ($rows as $key => $row) {
            if (in_array($row['cas_id'], $arrayUnattendedCases)) {
                $usersBean = BeanFactory::getBean('Users', $row['cas_init_user']);
                $row['cas_init_user'] = $usersBean->full_name;
                $rows_aux[] = $row;
            }
        }

        return array('next_offset' => '-1', 'records' => $rows_aux);
    }

    private function getUnattendedCasesByFlow()
    {
        $result = array();
        $queryOptions = array('add_deleted' => true);

        //GET CASES ID WHIT INACTIVE USERS
        $beanFlow = BeanFactory::getBean('pmse_BpmFlow');
        $q = new SugarQuery();
        $q->from($beanFlow, $queryOptions);
        $q->distinct(false);
        $fields = array('cas_id');

        //INNER JOIN USERS TABLE
        $q->joinTable('users', array('alias' => 'users', 'joinType' => 'INNER', 'linkingTable' => true))
                ->on()
                ->equalsField('users.id', 'cas_user_id')
                ->equals('users.deleted', 0);

        $q->where()
                ->queryOr()
                ->notequals('users.status', 'Active')
                ->notequals('users.employee_status', 'Active');

        $q->groupBy('cas_id');

        $q->select($fields);

        $rows = $q->execute();
        foreach ($rows as $key => $row) {
            $result[] = $row['cas_id'];
        }

        return $result;
    }

    public function selectCase($api, $args)
    {
        $returnArray = array();
        $bpmFlow = BeanFactory::retrieveBean('pmse_BpmFlow', $args['idflow']);
        $returnArray['case']['flow'] = $bpmFlow->fetched_row;

        $activity = BeanFactory::getBean('pmse_BpmActivityDefinition')->retrieve_by_string_fields(array('id' => $bpmFlow->bpmn_id));

        $reclaimCaseByUser = false;
        if (isset($bpmFlow->cas_adhoc_type) && ($bpmFlow->cas_adhoc_type === '') && ($bpmFlow->cas_start_date == '') && ($activity->act_assignment_method == 'selfservice')) {
            $reclaimCaseByUser = true;
        }
        if ($reclaimCaseByUser) {
            $listButtons = array('claim', 'cancel');
        } elseif (isset($bpmFlow->cas_adhoc_type) && ($bpmFlow->cas_adhoc_type === '') && ($activity->act_response_buttons == '' || $activity->act_response_buttons == 'APPROVE')) {
            $listButtons = array('link_cancel', 'approve', 'reject', 'edit');
        } else {
            $listButtons = array('link_cancel', 'route', 'edit');
        }
        $returnArray['case']['reclaim'] = $reclaimCaseByUser;
        $returnArray['case']['buttons'] = $this->getButtons($listButtons, $activity);

        $returnArray['case']['readonly'] = json_decode(base64_decode($activity->act_readonly_fields));
        $returnArray['case']['required'] = json_decode(base64_decode($activity->act_required_fields));

        $data_aux = new stdClass();
        $data_aux->cas_task_start_date = $returnArray['case']['flow']['cas_task_start_date'];
        $data_aux->cas_delegate_date = $returnArray['case']['flow']['cas_delegate_date'];

        $returnArray['case']['title']['time'] = $this->caseWrapper->expectedTime($activity->act_expected_time,
            $data_aux);
        $bpmnProcess = BeanFactory::retrieveBean('pmse_BpmnProcess', $bpmFlow->pro_id);
        $returnArray['case']['title']['process'] = $bpmnProcess->name;
        $bpmnActivity = BeanFactory::retrieveBean('pmse_BpmnActivity', $bpmFlow->bpmn_id);
        $returnArray['case']['title']['activity'] = $bpmnActivity->name;
        $returnArray['case']['inboxId'] = $bpmnActivity->name;
        $returnArray['case']['flowId'] = $args['idflow'];
        $returnArray['case']['inboxId'] = $args['id'];
        return $returnArray;
    }

    public function getButtons($listButtons, $activity)
    {
        $module_name = 'pmse_Inbox';
        $buttons = array(
            'link_cancel' => array(
                'type' => 'button',
                'name' => 'cancel_button',
                'label' => translate('LBL_CANCEL_BUTTON_LABEL', $module_name),
                'css_class' => 'btn-invisible btn-link',
                'showOn' => 'edit',
            ),
            'approve' => array(
                'type' => 'rowaction',
                'event' => 'case:approve',
                'name' => 'approve_button',
                'label' => translate('LBL_PMSE_LABEL_APPROVE', $module_name),
                'css_class' => 'btn btn-success',
            ),
            'reject' => array(
                'type' => 'rowaction',
                'event' => 'case:reject',
                'name' => 'reject_button',
                'label' => translate('LBL_PMSE_LABEL_REJECT', $module_name),
                'css_class' => 'btn btn-danger',
            ),
            'route' => array(
                'type' => 'rowaction',
                'event' => 'case:route',
                'name' => 'reject_button',
                'label' => translate('LBL_PMSE_LABEL_ROUTE', $module_name),
                'css_class' => 'btn btn-primary',
            ),
            'claim' => array(
                'type' => 'rowaction',
                'event' => 'case:claim',
                'name' => 'reject_button',
                'label' => translate('LBL_PMSE_LABEL_CLAIM', $module_name),
                'css_class' => 'btn btn-success',
            ),
            'cancel' => array(
                'type' => 'actiondropdown',
                'name' => 'main_dropdown',
                'primary' => true,
                'buttons' => array(
                    array(
                        'type' => 'rowaction',
                        'event' => 'case:cancel',
                        'name' => 'Cancel',
                        'label' => translate('LBL_PMSE_LABEL_CANCEL', $module_name),
                    ),
                    array(
                        'type' => 'rowaction',
                        'name' => 'history',
                        'label' => translate('LBL_PMSE_LABEL_HISTORY', $module_name),
                        'event' => 'case:history',
                    ),
                    array(
                        'type' => 'rowaction',
                        'name' => 'status',
                        'label' => translate('LBL_PMSE_LABEL_STATUS', $module_name),
                        'event' => 'case:status',
                    ),
//                    array(
//                        'type' => 'rowaction',
//                        'name' => 'add-notes',
//                        'label' => 'Add notes',
//                        'event' => 'case:add:notes',
//                    ),
                ),
            ),
            'edit' => array(
                'type' => 'actiondropdown',
                'name' => 'main_dropdown',
                'primary' => true,
                'showOn' => 'view',
                'buttons' => array(
                    array(
                        'type' => 'rowaction',
                        'event' => 'button:edit_button:click',
                        'name' => 'edit_button',
                        'label' => translate('LBL_EDIT_BUTTON_LABEL', $module_name),
                        'acl_action' => 'edit',
                    ),
                    array(
                        'type' => 'rowaction',
                        'name' => 'history',
                        'label' => translate('LBL_PMSE_LABEL_HISTORY', $module_name),
                        'event' => 'case:history',
                    ),
                    array(
                        'type' => 'rowaction',
                        'name' => 'status',
                        'label' => translate('LBL_PMSE_LABEL_STATUS', $module_name),
                        'event' => 'case:status',
                    ),
//                    array(
//                        'type' => 'rowaction',
//                        'name' => 'add-notes',
//                        'label' => 'Add notes',
//                        'event' => 'case:add:notes',
//                    ),
                ),
            ),
        );

        if (($activity->act_reassign || $activity->act_adhoc) && !in_array("claim", $listButtons)) {
            $buttons['edit']['buttons'][] = $buttons['cancel']['buttons'][] = array(
                'type' => 'divider',
            );
        }

        if ($activity->act_reassign && !in_array("claim", $listButtons)) {
            $buttons['edit']['buttons'][] = $buttons['cancel']['buttons'][] = array(
                'type' => 'rowaction',
                //'event' => '',
                'name' => 'find_duplicates_button',
                'label' => translate('LBL_PMSE_LABEL_CHANGE_OWNER', $module_name),
                'event' => 'case:change:owner',
            );
        }

        if ($activity->act_adhoc && !in_array("claim", $listButtons)) {
            $buttons['edit']['buttons'][] = $buttons['cancel']['buttons'][] = array(
                'type' => 'rowaction',
                //'event' => '',
                'name' => 'duplicate_button',
                'label' => translate('LBL_PMSE_LABEL_REASSIGN', $module_name),
                'event' => 'case:reassign',
            );
        }

        $arrayReturn = array();
        foreach ($listButtons as $button) {
            $arrayReturn[] = $buttons[$button];
        }
        return $arrayReturn;
    }

    public function getSettingsEngine($api, $args)
    {
        require_once 'modules/pmse_Inbox/engine/PMSESettings.php';
        $settings = PMSESettings::getInstance();
        list($settings, $settingsHtml) = $settings->getSettingsDB();
        return $settings;
    }

    public function putSettingsEngine($api, $args)
    {
        require_once 'modules/pmse_Inbox/engine/PMSESettings.php';
        $settings = PMSESettings::getInstance();
        if (isset($args['data']) && !empty($args['data'])) {
            $settings->putSettings($args['data']);
        }
        return array('success' => true, 'data' => $args['data'], 'data_1' => $settings->getSettings());
    }
}
