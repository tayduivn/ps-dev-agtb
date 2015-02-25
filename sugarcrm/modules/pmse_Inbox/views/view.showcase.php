<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('include/EditView/EditView2.php');
require_once('modules/pmse_Project/clients/base/api/wrappers/PMSEWrapper.php');
require_once('modules/pmse_Inbox/engine/wrappers/PMSECaseWrapper.php');
require_once('modules/pmse_Inbox/engine/PMSE.php');

class pmse_InboxViewShowCase extends SugarView
{
    public $type = 'list';
    public $activityRow = array();
    public $dyn_uid = '';
    public $workFlowType = '';
    public $renderedTemplate = '';
    public $view = '';
    public $showVCRControl = '';
    public $fieldDefs;
    public $offset;
    public $sectionPanels;
    public $returnModule;
    public $returnAction;
    public $returnId;
    public $isDuplicate;
    public $showDetailData;
    public $showSectionPanelsTitles;
    public $viewObject;
    public $populateBean;
    public $pmse;
    private $wrapper;

    public function pmse_InboxViewShowCase()
    {
        $this->pmse = PMSE::getInstance();
        $this->wrapper = new PMSEWrapper();
        parent::SugarView();
    }

    /**
     * This method assembles and renders the display of the custom template
     * for the edit and detail actions for the ProcessMaker Module.
     * @param type $module The name of the module to be rendered
     * @param type $id The id of the record to be rendered
     * @param type $viewMode This parameter can be 'bpm' 'detail' or 'edit'
     *                        in order to render the adequate template and
     *                        view definition
     */
    public function displayDataForm($module = '', $id = '', $viewMode = 'bpm', $readonly = false)
    {
        if (!empty($module) && !empty($id)) {

            $this->bean = BeanFactory::getBean($module, $id);
            $altViewMode = array();
            if (is_array($viewMode)) {
                $altViewMode = $viewMode;
                $viewMode = $viewMode['displayMode'];
            } else {
                $this->type = 'detail';
                $viewMode = 'detail';
            }

            $this->module = $module;

            $metadataFile = $this->getMetaDataFile();

            $viewdefs = '';

            if (isset($GLOBALS['sugar_config']['disable_vcr'])) {
                $this->showVCRControl = !$GLOBALS['sugar_config']['disable_vcr'];
            }

            if (empty($altViewMode)) {
                $mfile = get_custom_file_if_exists($metadataFile);
                if (isset($mfile)) {
                    require_once $metadataFile;
                }
            } else {
                $dynaformBean = BeanFactory::getBean('pmse_BpmDynaForm');//new BpmDynaForm();
                $dynaformBean->retrieve_by_string_fields(array('dyn_uid' => $altViewMode['dyn_uid']));
                $this->dyn_uid = $altViewMode['dyn_uid'];
                $viewdefs = unserialize(base64_decode($dynaformBean->dyn_view_defs));//unserialize(base64_decode('YToxOntzOjc6IkJwbVZpZXciO2E6Mjp7czoxMjoidGVtcGxhdGVNZXRhIjthOjU6e3M6NDoiZm9ybSI7YToyOntzOjY6ImhpZGRlbiI7YTo0OntpOjA7czoxNDg6IjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InByb3NwZWN0X2lkIiB2YWx1ZT0ie2lmIGlzc2V0KCRzbWFydHkucmVxdWVzdC5wcm9zcGVjdF9pZCl9eyRzbWFydHkucmVxdWVzdC5wcm9zcGVjdF9pZH17ZWxzZX17JGJlYW4tPnByb3NwZWN0X2lkfXsvaWZ9Ij4iO2k6MTtzOjE0NDoiPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iYWNjb3VudF9pZCIgdmFsdWU9IntpZiBpc3NldCgkc21hcnR5LnJlcXVlc3QuYWNjb3VudF9pZCl9eyRzbWFydHkucmVxdWVzdC5hY2NvdW50X2lkfXtlbHNlfXskYmVhbi0+YWNjb3VudF9pZH17L2lmfSI+IjtpOjI7czoxNDQ6IjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNvbnRhY3RfaWQiIHZhbHVlPSJ7aWYgaXNzZXQoJHNtYXJ0eS5yZXF1ZXN0LmNvbnRhY3RfaWQpfXskc21hcnR5LnJlcXVlc3QuY29udGFjdF9pZH17ZWxzZX17JGJlYW4tPmNvbnRhY3RfaWR9ey9pZn0iPiI7aTozO3M6MTYwOiI8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJvcHBvcnR1bml0eV9pZCIgdmFsdWU9IntpZiBpc3NldCgkc21hcnR5LnJlcXVlc3Qub3Bwb3J0dW5pdHlfaWQpfXskc21hcnR5LnJlcXVlc3Qub3Bwb3J0dW5pdHlfaWR9e2Vsc2V9eyRiZWFuLT5vcHBvcnR1bml0eV9pZH17L2lmfSI+Ijt9czo3OiJidXR0b25zIjthOjI6e2k6MDtzOjQ6IlNBVkUiO2k6MTtzOjY6IkNBTkNFTCI7fX1zOjEwOiJtYXhDb2x1bW5zIjtzOjE6IjIiO3M6NzoidXNlVGFicyI7YjoxO3M6Njoid2lkdGhzIjthOjI6e2k6MDthOjI6e3M6NToibGFiZWwiO3M6MjoiMTAiO3M6NToiZmllbGQiO3M6MjoiMzAiO31pOjE7YToyOntzOjU6ImxhYmVsIjtzOjI6IjEwIjtzOjU6ImZpZWxkIjtzOjI6IjMwIjt9fXM6MTA6ImphdmFzY3JpcHQiO3M6ODU1OiI8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgbGFuZ3VhZ2U9IkphdmFzY3JpcHQiPmZ1bmN0aW9uIGNvcHlBZGRyZXNzUmlnaHQoZm9ybSkgIHtsZGVsaW19IGZvcm0uYWx0X2FkZHJlc3Nfc3RyZWV0LnZhbHVlID0gZm9ybS5wcmltYXJ5X2FkZHJlc3Nfc3RyZWV0LnZhbHVlO2Zvcm0uYWx0X2FkZHJlc3NfY2l0eS52YWx1ZSA9IGZvcm0ucHJpbWFyeV9hZGRyZXNzX2NpdHkudmFsdWU7Zm9ybS5hbHRfYWRkcmVzc19zdGF0ZS52YWx1ZSA9IGZvcm0ucHJpbWFyeV9hZGRyZXNzX3N0YXRlLnZhbHVlO2Zvcm0uYWx0X2FkZHJlc3NfcG9zdGFsY29kZS52YWx1ZSA9IGZvcm0ucHJpbWFyeV9hZGRyZXNzX3Bvc3RhbGNvZGUudmFsdWU7Zm9ybS5hbHRfYWRkcmVzc19jb3VudHJ5LnZhbHVlID0gZm9ybS5wcmltYXJ5X2FkZHJlc3NfY291bnRyeS52YWx1ZTtyZXR1cm4gdHJ1ZTsge3JkZWxpbX0gZnVuY3Rpb24gY29weUFkZHJlc3NMZWZ0KGZvcm0pICB7bGRlbGltfSBmb3JtLnByaW1hcnlfYWRkcmVzc19zdHJlZXQudmFsdWUgPWZvcm0uYWx0X2FkZHJlc3Nfc3RyZWV0LnZhbHVlO2Zvcm0ucHJpbWFyeV9hZGRyZXNzX2NpdHkudmFsdWUgPSBmb3JtLmFsdF9hZGRyZXNzX2NpdHkudmFsdWU7Zm9ybS5wcmltYXJ5X2FkZHJlc3Nfc3RhdGUudmFsdWUgPSBmb3JtLmFsdF9hZGRyZXNzX3N0YXRlLnZhbHVlO2Zvcm0ucHJpbWFyeV9hZGRyZXNzX3Bvc3RhbGNvZGUudmFsdWUgPWZvcm0uYWx0X2FkZHJlc3NfcG9zdGFsY29kZS52YWx1ZTtmb3JtLnByaW1hcnlfYWRkcmVzc19jb3VudHJ5LnZhbHVlID0gZm9ybS5hbHRfYWRkcmVzc19jb3VudHJ5LnZhbHVlO3JldHVybiB0cnVlOyB7cmRlbGltfSA8L3NjcmlwdD4iO31zOjY6InBhbmVscyI7YTozOntzOjIzOiJMQkxfQ09OVEFDVF9JTkZPUk1BVElPTiI7YTo4OntpOjA7YToxOntpOjA7YToyOntzOjQ6Im5hbWUiO3M6MTA6ImZpcnN0X25hbWUiO3M6MTA6ImN1c3RvbUNvZGUiO3M6MjM3OiJ7aHRtbF9vcHRpb25zIG5hbWU9InNhbHV0YXRpb24iIGlkPSJzYWx1dGF0aW9uIiBvcHRpb25zPSRmaWVsZHMuc2FsdXRhdGlvbi5vcHRpb25zIHNlbGVjdGVkPSRmaWVsZHMuc2FsdXRhdGlvbi52YWx1ZX0mbmJzcDs8aW5wdXQgbmFtZT0iZmlyc3RfbmFtZSIgIGlkPSJmaXJzdF9uYW1lIiBzaXplPSIyNSIgbWF4bGVuZ3RoPSIyNSIgdHlwZT0idGV4dCIgdmFsdWU9InskZmllbGRzLmZpcnN0X25hbWUudmFsdWV9Ij4iO319aToxO2E6Mjp7aTowO3M6OToibGFzdF9uYW1lIjtpOjE7czoxMDoicGhvbmVfd29yayI7fWk6MjthOjI6e2k6MDtzOjU6InRpdGxlIjtpOjE7czoxMjoicGhvbmVfbW9iaWxlIjt9aTozO2E6Mjp7aTowO3M6MTA6ImRlcGFydG1lbnQiO2k6MTtzOjk6InBob25lX2ZheCI7fWk6NDthOjI6e2k6MDthOjQ6e3M6NDoibmFtZSI7czoxMjoiYWNjb3VudF9uYW1lIjtzOjQ6InR5cGUiO3M6NzoidmFyY2hhciI7czoxODoidmFsaWRhdGVEZXBlbmRlbmN5IjtiOjA7czoxMDoiY3VzdG9tQ29kZSI7czoxODU6IjxpbnB1dCBuYW1lPSJhY2NvdW50X25hbWUiIGlkPSJFZGl0Vmlld19hY2NvdW50X25hbWUiIHtpZiAoJGZpZWxkcy5jb252ZXJ0ZWQudmFsdWUgPT0gMSl9ZGlzYWJsZWQ9InRydWUiey9pZn0gc2l6ZT0iMzAiIG1heGxlbmd0aD0iMjU1IiB0eXBlPSJ0ZXh0IiB2YWx1ZT0ieyRmaWVsZHMuYWNjb3VudF9uYW1lLnZhbHVlfSI+Ijt9aToxO3M6Nzoid2Vic2l0ZSI7fWk6NTthOjI6e2k6MDthOjQ6e3M6NDoibmFtZSI7czoyMjoicHJpbWFyeV9hZGRyZXNzX3N0cmVldCI7czo5OiJoaWRlTGFiZWwiO2I6MTtzOjQ6InR5cGUiO3M6NzoiYWRkcmVzcyI7czoxMzoiZGlzcGxheVBhcmFtcyI7YTo0OntzOjM6ImtleSI7czo3OiJwcmltYXJ5IjtzOjQ6InJvd3MiO2k6MjtzOjQ6ImNvbHMiO2k6MzA7czo5OiJtYXhsZW5ndGgiO2k6MTUwO319aToxO2E6NDp7czo0OiJuYW1lIjtzOjE4OiJhbHRfYWRkcmVzc19zdHJlZXQiO3M6OToiaGlkZUxhYmVsIjtiOjE7czo0OiJ0eXBlIjtzOjc6ImFkZHJlc3MiO3M6MTM6ImRpc3BsYXlQYXJhbXMiO2E6NTp7czozOiJrZXkiO3M6MzoiYWx0IjtzOjQ6ImNvcHkiO3M6NzoicHJpbWFyeSI7czo0OiJyb3dzIjtpOjI7czo0OiJjb2xzIjtpOjMwO3M6OToibWF4bGVuZ3RoIjtpOjE1MDt9fX1pOjY7YToxOntpOjA7czo2OiJlbWFpbDEiO31pOjc7YToxOntpOjA7czoxMToiZGVzY3JpcHRpb24iO319czoxODoiTEJMX1BBTkVMX0FEVkFOQ0VEIjthOjQ6e2k6MDthOjI6e2k6MDtzOjY6InN0YXR1cyI7aToxO3M6MTE6ImxlYWRfc291cmNlIjt9aToxO2E6Mjp7aTowO2E6MTp7czo0OiJuYW1lIjtzOjE4OiJzdGF0dXNfZGVzY3JpcHRpb24iO31pOjE7YToxOntzOjQ6Im5hbWUiO3M6MjM6ImxlYWRfc291cmNlX2Rlc2NyaXB0aW9uIjt9fWk6MjthOjI6e2k6MDtzOjE4OiJvcHBvcnR1bml0eV9hbW91bnQiO2k6MTtzOjEwOiJyZWZlcmVkX2J5Ijt9aTozO2E6Mjp7aTowO3M6MTM6ImNhbXBhaWduX25hbWUiO2k6MTtzOjExOiJkb19ub3RfY2FsbCI7fX1zOjIwOiJMQkxfUEFORUxfQVNTSUdOTUVOVCI7YToxOntpOjA7YToyOntpOjA7YToyOntzOjQ6Im5hbWUiO3M6MTg6ImFzc2lnbmVkX3VzZXJfbmFtZSI7czo1OiJsYWJlbCI7czoxNToiTEJMX0FTU0lHTkVEX1RPIjt9aToxO2E6Mjp7czo0OiJuYW1lIjtzOjk6InRlYW1fbmFtZSI7czoxMzoiZGlzcGxheVBhcmFtcyI7YToxOntzOjc6ImRpc3BsYXkiO2I6MTt9fX19fX19'));
                $tmpArray = array();
                $tmpArray[$this->bean->module_name] = $viewdefs;
                $viewdefs = $tmpArray;
            }
            $this->view = ucfirst($viewMode) . 'View';
            if (isset($viewdefs[$this->bean->module_name][$this->view])) {
                $this->defs = $viewdefs[$this->bean->module_name][$this->view];
            } else {
                $this->defs = $viewdefs[$this->bean->module_name]['EditView'];
            }

            $this->focus = $this->bean;
            $tpl = get_custom_file_if_exists('modules/pmse_Inbox/tpls/' . $this->view . '.tpl');
            $this->th = new TemplateHandler();
            $this->th->ss = &$this->ss;
            $this->tpl = $tpl;

            if ($this->th->checkTemplate($this->bean->module_name, $this->view)) {
                $this->th->deleteTemplate($this->bean->module_name, $this->view);
            }

            $this->ev = new EditView();
            $this->ev->ss =& $this->ss;
            $this->ev->module = $module;
            $this->ev->th = $this->th;
            $this->ev->focus = $this->bean;
            $this->ev->defs = $this->defs;
            $this->ev->view = $this->view;
            $this->ev->process();
            $this->fieldDefs = $this->ev->fieldDefs;
            $this->sectionPanels = $this->ev->sectionPanels;
            $this->offset = $this->ev->offset;
            $this->returnModule = $this->ev->returnModule;
            $this->returnAction = $this->ev->returnAction;
            $this->returnId = $this->ev->returnId;
            //$this->returnRelationship = $this->ev->returnRelationship;
            //$this->returnName = $this->ev->returnName;

            return $this->setupAll(false, false, $this->bean->module_name, $readonly);
        }
    }

    public function getButtonArray($buttonList = array(), $casId = '', $casIndex = '', $teamId = '', $title='', $idInbox='')
    {
        $buttons = array(
            'claim' => array(
                'id' => 'claimBtn',
                'name' => 'Type',
                'value' => 'Claim',
                'type' => 'button',
                'onclick' => 'javascript:claim_case(\'' . $casId . '\', \'' . $casIndex . '\', \'' . $title . '\', \'' . $idInbox . '\');'
            ),
            'approve' => array('id' => 'ApproveBtn', 'name' => 'Type', 'value' => 'Approve', 'type' => 'submit'),
            'reject' => array('id' => 'RejectBtn', 'name' => 'Type', 'value' => 'Reject', 'type' => 'submit'),
            'reassign' => array(
                'id' => 'ReassignBtn',
                'name' => 'Type',
                'value' => 'Reassign',
                'type' => 'button',
                'onclick' => 'reassignForm(\'' . $casId . '\', \'' . $casIndex . '\', \'' . $teamId . '\');'
            ),
            'adhoc' => array(
                'id' => 'AdhocBtn',
                'name' => 'Type',
                'value' => 'Ad-Hoc User',
                'type' => 'button',
                'onclick' => 'adhocForm(\'' . $casId . '\', \'' . $casIndex . '\');'
            ),
            'route' => array('id' => 'RouteBtn', 'name' => 'Type', 'value' => 'Route Task', 'type' => 'submit'),
            'cancel' => array(
                'name' => 'Cancel',
                'value' => 'Cancel',
                'type' => 'button',
                'onclick' => 'history.back(1);'
            )
        );
        $customButtons = array();

        foreach ($buttonList as $buttonKey => $buttonValue) {
            if ($buttonValue == 'true') {
                switch ($buttonKey) {
                    case 'approve':
                        $customButtons[] = $buttons['approve'];
                        $customButtons[] = $buttons['reject'];
                        break;
                    case 'reassign':
                        $customButtons[] = $buttons['reassign'];
                        break;
                    case 'route':
                        $customButtons[] = $buttons['route'];
                        break;
                    case 'adhoc':
                        $customButtons[] = $buttons['adhoc'];
                        break;
                    case 'claim':
                        $customButtons[] = $buttons['claim'];
                        break;
                }
            }
        }
        $customButtons[] = $buttons['cancel'];
        return $customButtons;
    }

    public function display()
    {
        $id_flow = $_REQUEST['id'];
        $time_data = $GLOBALS['timedate'];
        $expected_time = 0;
        $expected_time_warning = false;
        $expected_time_message = '';

        global $current_user;
        //extract cas_id and cas_index
        $beanFlow = BeanFactory::getBean('pmse_BpmFlow', $id_flow);
        $cas_id = $beanFlow->cas_id;
        $cas_index = $beanFlow->cas_index;

        $caseBean = BeanFactory::newBean('pmse_Inbox');
        $joinTables = array(
            array('LEFT', 'pmse_bpm_flow', 'pmse_inbox.cas_id = pmse_bpm_flow.cas_id')
        );
        $records = $this->wrapper->getSelectRows($caseBean, 'cas_id desc',
            "pmse_bpm_flow.cas_id = $cas_id and cas_index = $cas_index ", 0, -1, -1, array('*'), $joinTables);
        $totalRecords = $records['totalRows'];
        $caseData = $records['rowList'][0];

        $totalNotes = 0;

        $smarty = new Sugar_Smarty();

        $smarty->assign('caseData', $caseData);
        $simpleRouting = false;

        switch ($caseData['cas_flow_status']) {
            case 'FORM':
                //TODO: if form still having two differents forms, depending of act_task_type
                global $sugar_config;

                //FORM TEMPLATE SECTION
                global $db;
                $sql = "SELECT *  FROM pmse_bpmn_activity
                        INNER JOIN pmse_bpm_activity_definition ON pmse_bpm_activity_definition.id = pmse_bpmn_activity.id
                        WHERE pmse_bpmn_activity.id='" . $caseData['bpmn_id'] . "'";
                $resultActi = $db->Query($sql);


                $this->activityRow = $db->fetchByAssoc($resultActi);
                $activityName = $this->activityRow['name'];
                $taskName = $this->activityRow['name'];
                $smarty->assign('nameTask', $taskName);
                $smarty->assign('flowId', $id_flow);

                //DUE DATE SECCION
                $data_aux = new stdClass();
                $data_aux->cas_task_start_date = $caseData['cas_task_start_date'];
                $data_aux->cas_delegate_date = $caseData['cas_delegate_date'];
                $expTime = PMSECaseWrapper::expectedTime($this->activityRow['act_expected_time'], $data_aux);
                $expected_time = $expTime['expected_time'];
                $expected_time_warning = $expTime['expected_time_warning'];
                if ($expected_time_warning == true) {
                    $expected_time_message = "Overdue";
                } else {
                    $expected_time_message = "Due Date";
                }

                $displayMode = array('displayMode' => 'bpm', 'dyn_uid' => $this->activityRow['act_type']);
                //INIT CLAIM CASE AND DEFINE DISPLAY MODE
                $reclaimCaseByUser = false;
                if (isset($caseData['cas_adhoc_type']) && ($caseData['cas_adhoc_type'] === '') && ($caseData['cas_start_date'] == '') && ($this->activityRow['act_assignment_method'] == 'selfservice')) {
                    $reclaimCaseByUser = true;
//                    $displayMode = 'detail';

                }
                //
                $beanTemplate = $this->displayDataForm($caseData['cas_sugar_module'], $caseData['cas_sugar_object_id'],
                    $displayMode, $reclaimCaseByUser);
                if (isset($caseData['cas_adhoc_type']) && ($caseData['cas_adhoc_type'] === '') && ($caseData['cas_start_date'] == '') && ($this->activityRow['act_assignment_method'] == 'selfservice')) {
                    $displayMode = 'detail';

                }
                //BUTTON SECTIONS
                $defaultButtons = $this->getButtonArray(array('approve' => true, 'reject' => true));
                if ($reclaimCaseByUser) {
                    $this->defs['BPM']['buttons']['claim'] = true;
                } elseif (isset($caseData['cas_adhoc_type']) && ($caseData['cas_adhoc_type'] === '')) {
                    $this->defs['BPM']['buttons']['approve'] = (strtoupper($this->activityRow['act_response_buttons']) == 'APPROVE') ? true : false;
                    $this->defs['BPM']['buttons']['route'] = (strtoupper($this->activityRow['act_response_buttons']) == 'ROUTE') ? true : false;
                } else {
                    $this->defs['BPM']['buttons']['route'] = true;
                }

                //ASSIGN SECTION
                $smarty->assign('cas_id', $cas_id);
                $smarty->assign('cas_index', $cas_index);
                $smarty->assign('cas_current_user_id', $current_user->id);
                $smarty->assign('act_name', $activityName);
                $smarty->assign('act_adhoc_behavior', $this->activityRow['act_adhoc_behavior']);
                $smarty->assign('act_adhoc', $this->activityRow['act_adhoc'] == 1 ? true : false);
                $smarty->assign('act_reassign', $this->activityRow['act_reassign'] == 1 ? true : false);
                $smarty->assign('act_note', true);
                $smarty->assign('expected_time_warning', $expected_time_warning);
                $smarty->assign('expected_time_message', $expected_time_message);
                $smarty->assign('expected_time', $expected_time);
                $smarty->assign('reclaimCaseByUser', $reclaimCaseByUser);
                $smarty->assign('totalNotes', $totalNotes);
                $smarty->assign('SUGAR_URL', $sugar_config['site_url']);
                $smarty->assign('SUGAR_AJAX_URL',
                    $sugar_config['site_url'] . "/index.php?module=pmse_Inbox&action=ajaxapi");
                $apiSupported = 'false';
                $smarty->assign('SUGAR_REST', $apiSupported);

                //verify if is a claim case form if not add validate fields
                if (!$reclaimCaseByUser) {
                    $valid = $this->validationsRequiredFields();
                    $smarty->assign('validations', $valid);
                } else {
                    $smarty->assign('validations', array());
                }
                $idInbox = isset($caseData['idInbox']) ? $caseData['idInbox'] : null;
                $customButtons = $this->getButtonArray($this->defs['BPM']['buttons'], $cas_id, $cas_index,
                    $this->focus->team_id, $caseData['cas_title'], $idInbox);
                if (count($customButtons) > 1) {
                    $smarty->assign('customButtons', $customButtons);
                } else {
                    $smarty->assign('customButtons', $defaultButtons);
                }

                //TPL SECTION

                $openHeaderTemplate = 'modules/pmse_Inbox/tpls/showCaseRoute.tpl';

                $closeHeaderTemplate = 'modules/pmse_Inbox/tpls/showCaseCloseHeader.tpl';
                $openFooterTemplate = 'modules/pmse_Inbox/tpls/showCaseOpenFooter.tpl';
                $closeFooterTemplate = 'modules/pmse_Inbox/tpls/showCaseRouteFooter.tpl';

                //DISPLAY SECTION
                $smarty->display($openHeaderTemplate);
                if ($displayMode == 'detail') {
                    $smarty->display($closeHeaderTemplate);
                }


                // Displaying the Bean Form filled with data
                echo $beanTemplate;

                if ($displayMode == 'detail') {
                    $smarty->display($openFooterTemplate);
                }

                $smarty->display($closeFooterTemplate);

                break;
            default:
                global $sugar_config;
                $smarty->assign('siteUrl', $sugar_config['site_url']);
                $smarty->display('modules/pmse_Inbox/tpls/showCaseDefault.tpl');
                break;
        }
    }

    protected function addAdditionalModStrings () {
        global $mod_strings;
        if (!isset($this->bean) || !isset($this->th->ss)) {
            return false;
        }
        switch ($this->bean->module_name) {
            case 'Quotes':
                $this->bean->load_relationship('product_bundles');
                $product_bundle_list = $this->bean->product_bundles->getBeans();

                if (is_array($product_bundle_list)) {
                    usort($product_bundle_list, array('ProductBundle', 'compareProductBundlesByIndex'));
                }

                $this->th->ss->assign('ordered_bundle_list', $product_bundle_list);
                $this->th->ss->assign('exclude_default_footer_buttons', "1");
                $mod_strings = array_merge($mod_strings, return_module_language($GLOBALS['current_language'], $this->bean->module_name));

                require_once('modules/Quotes/Layouts.php');
                require_once('include/EditView/EditView2.php');


                global $beanFiles;
                require_once($beanFiles['Quote']);
                require_once($beanFiles['TaxRate']);
                require_once($beanFiles['Shipper']);

                global $mod_strings;
                global $app_strings;
                global $app_list_strings;
                global $current_user;
                global $timedate;
                global $locale;

                $original_quote = BeanFactory::getBean('Quotes');
                if($this->ev->isDuplicate)
                {
                    $this->bean->id = "";
                    $this->bean->quote_num = "";
                    $original_quote->retrieve($_REQUEST['record']);
                }

                //needed when creating a new quote only with a default account value passed in
                if((empty($this->bean->id))  && !$this->ev->isDuplicate)
                {
                    $this->bean->quote_num= '';
                    $this->bean->total= '0.00';
                    $this->bean->shipping= '0.00';
                    $this->bean->tax= '0.00';
                    $this->bean->subtotal= '0.00';
                    if (isset($_REQUEST['opportunity_name'])) {
                        $this->bean->opportunity_name = $_REQUEST['opportunity_name'];
                    }
                    if (isset($_REQUEST['opportunity_id'])) {
                        $this->bean->opportunity_id = $_REQUEST['opportunity_id'];
                    }
                    if (isset($_REQUEST['account_name'])) {
                        $this->bean->billing_account_name = $_REQUEST['account_name'];
                        $this->bean->shipping_account_name = $_REQUEST['account_name'];
                    }
                    if (isset($_REQUEST['account_id'])) {
                        $this->bean->billing_account_id = $_REQUEST['account_id'];
                        $this->bean->shipping_account_id = $_REQUEST['account_id'];
                        require_once($beanFiles['Account']);
                        $account = BeanFactory::getBean('Accounts', $this->bean->shipping_account_id);
                        $this->bean->shipping_address_street    = $account->shipping_address_street;
                        $this->bean->shipping_address_city      = $account->shipping_address_city;
                        $this->bean->shipping_address_state         = $account->shipping_address_state;
                        $this->bean->shipping_address_country   = $account->shipping_address_country;
                        $this->bean->shipping_address_postalcode = $account->shipping_address_postalcode;
                        $this->bean->billing_address_street         = $account->billing_address_street;
                        $this->bean->billing_address_city       = $account->billing_address_city;
                        $this->bean->billing_address_state      = $account->billing_address_state;
                        $this->bean->billing_address_country    = $account->billing_address_country;
                        $this->bean->billing_address_postalcode     = $account->billing_address_postalcode;
                    }
                    if (isset($_REQUEST['contact_id'])) {
                        $this->bean->contact_id = $_REQUEST['contact_id'];
                        require_once($beanFiles['Contact']);
                        $contact = BeanFactory::getBean('Contacts', $this->bean->contact_id);
                        $this->bean->billing_contact_name       = $locale->formatName($contact);
                        $this->bean->billing_contact_id             = $contact->id;
                        $this->bean->shipping_contact_name      = $locale->formatName($contact);
                        $this->bean->shipping_contact_id        = $contact->id;
                        $this->bean->shipping_address_street    = $contact->primary_address_street;
                        $this->bean->shipping_address_city      = $contact->primary_address_city;
                        $this->bean->shipping_address_state         = $contact->primary_address_state;
                        $this->bean->shipping_address_country   = $contact->primary_address_country;
                        $this->bean->shipping_address_postalcode = $contact->primary_address_postalcode;
                    }

                    if (isset($_REQUEST['date_quote_expected_closed'])) {
                        $this->bean->date_quote_expected_closed = $_REQUEST['date_quote_expected_closed'];
                    }
                    if (isset($_REQUEST['currency_id'])) {
                        $this->bean->currency_id = $_REQUEST['currency_id'];
                    }
                }


                $currency = BeanFactory::getBean('Currencies', $this->bean->currency_id);

                // Set the number grouping and decimal separators
                $seps = get_number_seperators();
                $dec_sep = $seps[1];
                $num_grp_sep = $seps[0];
                $this->th->ss->assign('NUM_GRP_SEP', $num_grp_sep);
                $this->th->ss->assign('DEC_SEP', $dec_sep);

                $significantDigits = $locale->getPrecedentPreference('default_currency_significant_digits', $current_user);
                $this->th->ss->assign('PRECISION', $significantDigits);


                if((is_admin($current_user) || is_admin_for_module($GLOBALS['current_user'],'Quotes')) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
                    $record = '';
                    if(!empty($_REQUEST['record'])){
                        $record = $_REQUEST['record'];
                    }
                    $this->th->ss->assign('ADMIN_EDIT',"<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$record. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>");

                }
                $this->th->ss->assign('QUOTE_STAGE_OPTIONS', get_select_options_with_id($app_list_strings['quote_stage_dom'], $this->bean->quote_stage));
                $this->th->ss->assign('DEFAULT_PRODUCT_STATUS', $app_list_strings['product_status_quote_key']);
                if (isset($this->bean->subtotal)) $this->th->ss->assign('SUBTOTAL', $this->bean->subtotal);
                else $this->th->ss->assign('SUBTOTAL', "0.00");
                if (isset($this->bean->tax)) $this->th->ss->assign('TAX', $this->bean->tax);
                else $this->th->ss->assign('TAX', "0.00");
                if (isset($this->bean->shipping)) $this->th->ss->assign("SHIPPING", $this->bean->shipping);
                else $this->th->ss->assign('SHIPPING', "0.00");
                if (isset($this->bean->deal_tot)) $this->th->ss->assign('DEAL_TOT', $this->bean->deal_tot);
                else $this->th->ss->assign('DEAL_TOT', "0.00");
                if (isset($this->bean->new_sub)) $this->th->ss->assign('NEW_SUB', $this->bean->new_sub);
                else $this->th->ss->assign('NEW_SUB', "0.00");
                if (isset($this->bean->total)) $this->th->ss->assign('TOTAL', $this->bean->total);
                else $this->th->ss->assign('TOTAL', "0.00");
                if (isset($this->bean->subtotal_usdollar)) $this->th->ss->assign('SUBTOTAL_USDOLLAR', $this->bean->subtotal_usdollar);
                else $this->th->ss->assign('SUBTOTAL_USDOLLAR', "0.00");
                if (isset($this->bean->tax_usdollar)) $this->th->ss->assign('TAX_USDOLLAR', $this->bean->tax_usdollar);
                else $this->th->ss->assign('TAX_USDOLLAR', "0.00");
                if (isset($this->bean->shipping_usdollar)) $this->th->ss->assign('SHIPPING_USDOLLAR', $this->bean->shipping_usdollar);
                else $this->th->ss->assign('SHIPPING_USDOLLAR', "0.00");
                if (isset($this->bean->total_usdollar)) $this->th->ss->assign('TOTAL_USDOLLAR', $this->bean->total_usdollar);
                else $this->th->ss->assign('TOTAL_USDOLLAR', "0.00");


                $this->th->ss->assign('USER_DATEFORMAT', '('. $timedate->get_user_date_format().')');
                $this->th->ss->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
                $taxrate = BeanFactory::getBean('TaxRates');
                $this->th->ss->assign('TAXRATE_OPTIONS', get_select_options_with_id($taxrate->get_taxrates(false), $this->bean->taxrate_id));
                if (empty($this->bean->taxrate_value)) { $this->th->ss->assign('TAXRATE_VALUE', $taxrate->get_default_taxrate_value() / 100); }
                else { $this->th->ss->assign('TAXRATE_VALUE', $this->bean->taxrate_value / 100); }

                $shipper = BeanFactory::getBean('Shippers');
                $this->th->ss->assign('SHIPPER_OPTIONS', get_select_options_with_id($shipper->get_shippers(true), $this->bean->shipper_id));

                if (empty($this->bean->assigned_user_id) && empty($this->bean->id))  $this->bean->assigned_user_id = $current_user->id;
                if (empty($this->bean->assigned_name) && empty($this->bean->id))  $this->bean->assigned_user_name = $current_user->user_name;
                $this->th->ss->assign('ASSIGNED_USER_OPTIONS', get_select_options_with_id(get_user_array(TRUE, 'Active', $this->bean->assigned_user_id), $this->bean->assigned_user_id));
                $this->th->ss->assign('ASSIGNED_USER_NAME', $this->bean->assigned_user_name);
                $this->th->ss->assign('ASSIGNED_USER_ID', $this->bean->assigned_user_id );

                if(!empty($this->bean->calc_grand_total) && $this->bean->calc_grand_total == 1){
                    $this->th->ss->assign('CALC_GRAND_TOTAL_CHECKED', 'checked');
                }

                if(!empty($this->bean->show_line_nums) && $this->bean->show_line_nums == 1){
                    $this->th->ss->assign('SHOW_LINE_NUMS_CHECKED', 'checked');
                }

                // Set Currency values and currency javascript
                require_once('modules/Currencies/ListCurrency.php');
                $currency = new ListCurrency();
                $base_rate = '1.00';
                if ( isset($this->bean->currency_id) && !empty($this->bean->currency_id) ) {
                    $curid = $this->bean->currency_id;
                } elseif ( isset($_REQUEST['currency_id']) && !empty($_REQUEST['currency_id']) ) {
                    $curid = $_REQUEST['currency_id'];
                } elseif ( empty($this->bean->id) ) {
                    $curid = $current_user->getPreference('currency');
                    if ( empty($curid) ) {
                        $curid = -99;
                    }
                } else {
                    $curid = -99;
                }

                if ($this->bean->isClosed()) {
                    $base_rate = $this->bean->base_rate;
                } else {
                    $base_rate = null;
                }

                $selectCurrency = $currency->getSelectOptions($curid, $base_rate);
                $this->th->ss->assign("CURRENCY", $selectCurrency);
                $this->th->ss->assign('CURRENCY_JAVASCRIPT', $currency->getJavascript());

                if($this->bean->fetched_row['date_quote_expected_closed'] == '1970-01-01' ||
                    $this->bean->fetched_row['date_quote_expected_closed'] == '0001-01-01') {
                    $this->bean->date_quote_expected_closed = '';
                }

                $add_row = array();
                if (!empty($this->bean->id))
                {
                    $this->bean->load_relationship('product_bundles');
                    $product_bundle_list = $this->bean->product_bundles->getBeans();
                    usort($product_bundle_list, array('ProductBundle', 'compareProductBundlesByIndex'));

                    $quote_currency_id = $this->bean->currency_id;
                    $quote_base_rate = $this->bean->base_rate;
                    $convert_format = function($value, $prod_currency, $prod_base_rate) use ($quote_currency_id, $quote_base_rate) {
                        if ($prod_currency !== $quote_currency_id) {
                            $value = SugarCurrency::convertWithRate($value, $prod_base_rate, $quote_base_rate);
                        }
                        return SugarCurrency::formatAmountUserLocale($value, $quote_currency_id, false);
                    };

                    if(is_array($product_bundle_list)){

                        foreach ($product_bundle_list as $product_bundle) {
                            $bundle_list = $product_bundle->get_product_bundle_line_items();
                            $add_row[] = "quotesManager.addTable('$product_bundle->id','$product_bundle->bundle_stage', '$product_bundle->name', '".format_money($product_bundle->shipping,FALSE)."' );\n";

                            if (is_array($bundle_list)) {
                                while (list($key, $line_item) = each ($bundle_list)) {
                                    if ($line_item->object_name == "Product") {
                                        /* @var $line_item Product */
                                        $tax_class_name = isset($line_item->tax_class) ? $line_item->tax_class : "";

                                        $encoded_name = js_escape(br2nl($line_item->name));

                                        $add_row[] = "quotesManager.addRow('$line_item->id','" . format_number($line_item->quantity, $significantDigits, $significantDigits) . "','$line_item->product_template_id','$encoded_name'"
                                            . ", '".$convert_format($line_item->cost_price, $line_item->currency_id, $line_item->base_rate) . "'"
                                            . ", '".$convert_format($line_item->list_price, $line_item->currency_id, $line_item->base_rate) ."'"
                                            . ", '".$convert_format($line_item->discount_price, $line_item->currency_id, $line_item->base_rate) . "'"
                                            . ", '', '', '$line_item->pricing_factor', '$line_item->tax_class', '$tax_class_name', '$line_item->mft_part_num', '$product_bundle->id', '$product_bundle->bundle_stage', '$product_bundle->name', '"
                                            . format_number($product_bundle->shipping)."', '".js_escape(br2nl($line_item->description))."', '". $line_item->type_id."'"
                                            . ", '".format_number($line_item->discount_amount, $significantDigits, $significantDigits)."'"
                                            . ", ".($line_item->discount_select?1:0)
                                            . ", ".($line_item->deal_calc?1:0)
                                            . ", '".$line_item->status."');\n";
                                    }
                                    else if ($line_item->object_name == "ProductBundleNote") {
                                        /* @var $line_item ProductBundleNote */
                                        $encoded_description = js_escape(br2nl($line_item->description));
                                        //$encoded_description = html_entity_decode($encoded_description);
                                        $add_row[] = "quotesManager.addCommentRow('$line_item->id', '$product_bundle->id', '$encoded_description');\n";
                                    }
                                } //while
                            } //if
                        } //foreach
                    }
                } else {
                    // this else part is to create a new product_bundle for the duplicate quote
                    $original_quote->load_relationship('product_bundles');
                    $product_bundle_list = $original_quote->product_bundles->getBeans();
                    usort($product_bundle_list, array('ProductBundle', 'compareProductBundlesByIndex'));

                    if(is_array($product_bundle_list)){

                        foreach ($product_bundle_list as $product_bundle) {

                            $product_list = $product_bundle->get_products();
                            if (is_array($product_list)) {
                                foreach ($product_list as $line_item) {
                                    $tax_class_name = isset($line_item->tax_class) ? $line_item->tax_class : "";

                                    $add_row[] = "quotesManager.addRow('','$line_item->quantity','$line_item->product_template_id','$line_item->name'"
                                        . ", '".format_number($line_item->cost_usdollar, $significantDigits, $significantDigits, array('convert' => true, 'currency_id' => $curid)) . "'"
                                        . ", '".format_number($line_item->list_usdollar, $significantDigits, $significantDigits, array('convert' => true, 'currency_id' => $curid)) ."'"
                                        . ", '".format_number($line_item->discount_usdollar, $significantDigits, $significantDigits, array('convert' => true, 'currency_id' => $curid)) . "'"
                                        .", '', '', '$line_item->pricing_factor', '$line_item->tax_class', '$tax_class_name',
                                '$line_item->mft_part_num', 'group_$product_bundle->id', '$product_bundle->bundle_stage', '$product_bundle->name', '".format_money($product_bundle->shipping,FALSE)
                                        ."', '".js_escape(br2nl($line_item->description))."', '"
                                        . $line_item->type_id ."','"
                                        . format_number($line_item->discount_amount_usdollar, $significantDigits, $significantDigits, array('convert' => !$line_item->discount_select, 'currency_id' => $curid))."',"
                                        .($line_item->discount_select?1:0)
                                        . ",0, '". $line_item->status."');\n";

                                } //foreach
                                if(empty($product_list)){
                                    $add_row[] = "quotesManager.addTable('group_$product_bundle->id','$product_bundle->bundle_stage', '$product_bundle->name' , ' ".format_money($product_bundle->shipping,FALSE)."');\n";
                                } //if
                                //bug 39573 - Comments are not duplicated in quotes
                                $bundle_list = $product_bundle->get_product_bundle_line_items();
                                if (is_array($bundle_list)){
                                    while (list($key, $line_item) = each ($bundle_list)){
                                        if ($line_item->object_name == "ProductBundleNote"){
                                            $encoded_description = js_escape(br2nl($line_item->description));
                                            $add_row[] = "quotesManager.addCommentRow('$line_item->id', 'group_$product_bundle->id', '$encoded_description');\n";

                                        }
                                    }
                                } //end bug 39573
                            } //if
                        }
                    }
                } //if-else for !empty($this->bean->id)

                //Bug#53607: Create the javascript code to store the rendering function in a queue
                $add_row_js = 'var add_row_stack = [];';
                foreach($add_row as $script_command) {
                    $add_row_js .= "add_row_stack.push(function(){
                $script_command
            });";
                }

                //Bug#53607: Rather than executing all rendering row script once, it will keep in a queue.
                //           And it will render the specified number of rows every interval.
                $add_row_js .= "function add_rows_on_load() {
            if(typeof add_row_stack != 'undefined' && add_row_stack.length > 0) {
                //interval is in msec,
                //size is the number of rows rendering every time
                asyncLoading = true; // to indicate that the content is still loading
                var _interval = 100,
                    _size = 3,
                    _exec = add_row_stack.splice(0, _size),
                    _header_button = document.getElementById('SAVE_HEADER'),
                    _footer_button = document.getElementById('SAVE_FOOTER');
                if(_header_button) {
                    _header_button.disabled = true;
                }
                if(_footer_button) {
                    _footer_button.disabled = true;
                }
                for(idx in _exec) {
                    _exec[idx]();
                }
                window.setTimeout(add_rows_on_load, _interval);
            } else {
                asyncLoading = false; // content is loaded
                var _header_button = document.getElementById('SAVE_HEADER'),
                    _footer_button = document.getElementById('SAVE_FOOTER');
                if(_header_button) {
                    _header_button.disabled = false;
                }
                if(_footer_button) {
                    _footer_button.disabled = false;
                }
            }
        }";
                $this->th->ss->assign("ADD_ROWS", $add_row_js);

                $setup_script = '';
                $taxclass = translate('tax_class_dom');
                foreach($taxclass as $value=>$name){
                    $setup_script .= "quotesManager.add_tax_class('$name', '$value');\n";
                }
                $this->th->ss->assign("SETUP_SCRIPT", $setup_script);

                $this->th->ss->assign('TAXRATE_JAVASCRIPT', $taxrate->get_taxrate_js());
                $this->th->ss->assign('CALCULATE_FUNCTION', '<script type="text/javascript">YAHOO.util.Event.onDOMReady(function(){quotesManager.calculate(document);});</script>');

                $this->th->ss->assign('NO_MATCH_VARIABLE', '<script type="text/javascript">sqs_no_match_text = "' . $app_strings['ERR_SQS_NO_MATCH'] . '";</script>');

                $str = "<script language=\"javascript\">
        YAHOO.util.Event.onAvailable('add_tables', add_rows_on_load);
        </script>";
                $this->th->ss->assign('SAVED_SEARCH_SELECTS', $str);

                break;
        }
        return true;
    }

    public function setupAll($showTitle = false, $ajaxSave = false, $moduleName = '', $readonly = false)
    {
        global $mod_strings, $sugar_config, $app_strings, $app_list_strings, $theme, $current_user;

        if (isset($this->defs['templateMeta']['javascript'])) {
            if (is_array($this->defs['templateMeta']['javascript'])) {
                $this->th->ss->assign('externalJSFile', $this->defs['templateMeta']['javascript']);
            } else {
                $this->th->ss->assign('scriptBlocks', $this->defs['templateMeta']['javascript']);
            }
        }

         $this->addAdditionalModStrings();

        $this->th->ss->assign('id', $this->fieldDefs['id']['value']);
        $this->th->ss->assign('offset', $this->offset + 1);
        $this->th->ss->assign('APP', $app_strings);
        $this->th->ss->assign('MOD', $mod_strings);
        $this->th->ss->assign('footerTpl', isset($this->defs['templateMeta']['form']['footerTpl']) ? $this->defs['templateMeta']['form']['footerTpl'] : null);
        $this->fieldDefs = $this->setDefaultAllFields($this->fieldDefs); // default editview
        if ($readonly) {
            $this->fieldDefs = $this->setReadOnlyAllFields($this->fieldDefs);
        } else {
            $this->fieldDefs = $this->processReadOnlyFields($this->fieldDefs);
            $this->fieldDefs = $this->processRequiredFields($this->fieldDefs);
        }
        $this->th->ss->assign('fields', $this->fieldDefs);
        $this->sectionPanels = $this->processSectionPanels($this->sectionPanels);
        $this->th->ss->assign('sectionPanels', $this->sectionPanels);
        $this->th->ss->assign('config', $sugar_config);
        $this->th->ss->assign('returnModule', $this->returnModule);
        $this->th->ss->assign('returnAction', $this->returnAction);
        $this->th->ss->assign('returnId', $this->returnId);
        $this->th->ss->assign('isDuplicate', $this->isDuplicate);
        $this->th->ss->assign('def', $this->defs);
        $this->th->ss->assign('useTabs',
            isset($this->defs['templateMeta']['useTabs']) && isset($this->defs['templateMeta']['tabDefs']) ? $this->defs['templateMeta']['useTabs'] : false);
        $this->th->ss->assign('maxColumns',
            isset($this->defs['templateMeta']['maxColumns']) ? $this->defs['templateMeta']['maxColumns'] : 2);
        $this->th->ss->assign('module', $moduleName);
        $this->th->ss->assign('current_user', $current_user);
        $this->th->ss->assign('bean', $this->focus);
        $this->th->ss->assign('gridline', $current_user->getPreference('gridline') == 'on' ? '1' : '0');
        $this->th->ss->assign('tabDefs',
            isset($this->defs['templateMeta']['tabDefs']) ? $this->defs['templateMeta']['tabDefs'] : false);
        $this->th->ss->assign('VERSION_MARK', getVersionedPath(''));

        global $js_custom_version;
        global $sugar_version;

        $this->th->ss->assign('SUGAR_VERSION', $sugar_version);
        $this->th->ss->assign('JS_CUSTOM_VERSION', $js_custom_version);

        //this is used for multiple forms on one page
        if (!empty($this->formName)) {
            $form_id = $this->formName;
            $form_name = $this->formName;
        } else {
            $form_id = $this->view;
            $form_name = $this->view;
        }

        if ($ajaxSave && empty($this->formName)) {
            $form_id = 'form_' . $this->view . '_' . $moduleName;
            $form_name = $form_id;
            $this->view = $form_name;
       }

        $form_name = $form_name == 'QuickCreate' ? "QuickCreate_{$moduleName}" : $form_name;
        $form_id = $form_id == 'QuickCreate' ? "QuickCreate_{$moduleName}" : $form_id;

        if (isset($this->defs['templateMeta']['preForm'])) {
            $this->th->ss->assign('preForm', $this->defs['templateMeta']['preForm']);
        }

        if (isset($this->defs['templateMeta']['form']['closeFormBeforeCustomButtons'])) {
            $this->th->ss->assign('closeFormBeforeCustomButtons',
                $this->defs['templateMeta']['form']['closeFormBeforeCustomButtons']);
        }

        if (isset($this->defs['templateMeta']['form']['enctype'])) {
            $this->th->ss->assign('enctype', 'enctype="' . $this->defs['templateMeta']['form']['enctype'] . '"');
        }

        //for SugarFieldImage, we must set form enctype to "multipart/form-data"
        foreach ($this->fieldDefs as $field) {
            if (isset($field['type']) && $field['type'] == 'image') {
                $this->th->ss->assign('enctype', 'enctype="multipart/form-data"');
                break;
            }
        }

        $this->th->ss->assign('showDetailData', $this->showDetailData);
        $this->th->ss->assign('showSectionPanelsTitles', $this->showSectionPanelsTitles);
        $this->th->ss->assign('form_id', $form_id);
        $this->th->ss->assign('form_name', $form_name);//$form_name change by id form showCaseForm
        $this->th->ss->assign('set_focus_block', get_set_focus_js());

        $this->th->ss->assign('form',
            isset($this->defs['templateMeta']['form']) ? $this->defs['templateMeta']['form'] : null);
        $this->th->ss->assign('includes',
            isset($this->defs['templateMeta']['includes']) ? $this->defs['templateMeta']['includes'] : null);
        $this->th->ss->assign('view', $this->view);


        $admin = new Administration();
        $admin->retrieveSettings();
        if (isset($admin->settings['portal_on']) && $admin->settings['portal_on']) {
            $this->th->ss->assign("PORTAL_ENABLED", true);
        } else {
            $this->th->ss->assign("PORTAL_ENABLED", false);
        }

        //Calculate time & date formatting (may need to calculate this depending on a setting)
        global $timedate;

        $this->th->ss->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
        $this->th->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        $time_format = $timedate->get_user_time_format();
        $this->th->ss->assign('TIME_FORMAT', $time_format);

        $date_format = $timedate->get_cal_date_format();
        $time_separator = ':';
        if (preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
            $time_separator = $match[1];
        }

        // Create Smarty variables for the Calendar picker widget
        $t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
        if (!isset($match[2]) || $match[2] == '') {
            $this->th->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . '%M');
        } else {
            $pm = $match[2] == 'pm' ? '%P' : '%p';
            $this->th->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . '%M' . $pm);
        }

        $this->th->ss->assign('CALENDAR_FDOW', $current_user->get_first_day_of_week());
        $this->th->ss->assign('TIME_SEPARATOR', $time_separator);

        $seps = get_number_seperators();
        $this->th->ss->assign('NUM_GRP_SEP', $seps[0]);
        $this->th->ss->assign('DEC_SEP', $seps[1]);

        if ($this->view == 'EditView' || $this->view == 'BpmView') {
            $height = $current_user->getPreference('text_editor_height');
            $width = $current_user->getPreference('text_editor_width');

            $height = isset($height) ? $height : '300px';
            $width = isset($width) ? $width : '95%';

            $this->th->ss->assign('RICH_TEXT_EDITOR_HEIGHT', $height);
            $this->th->ss->assign('RICH_TEXT_EDITOR_WIDTH', $width);
        } else {
            $this->th->ss->assign('RICH_TEXT_EDITOR_HEIGHT', '100px');
            $this->th->ss->assign('RICH_TEXT_EDITOR_WIDTH', '95%');
        }

        $this->th->ss->assign('SHOW_VCR_CONTROL', $this->showVCRControl);

        $str = $this->showTitle($showTitle);
        $ajaxSave = false;
        //Use the output filter to trim the whitespace
        $this->th->ss->load_filter('output', 'trimwhitespace');
        $form_name = $this->view;
        if ($this->th->checkTemplate($this->bean->module_name, $this->view) && !empty($this->dyn_uid)) {
            $nameTemplateTmp = $this->dyn_uid;
        } else {
            $nameTemplateTmp = 'PMSEDetailView';
        }
        $this->th->buildTemplate($this->bean->module_name, $nameTemplateTmp, $this->tpl, $ajaxSave, $this->defs);
        $this->th->deleteTemplate($this->bean->module_name, $form_name);
        $newTplFile = $this->th->cacheDir . $this->th->templateDir . $this->bean->module_name . '/' . $nameTemplateTmp . '.tpl';
        $str .= $this->th->ss->fetch($newTplFile);
        return $str;
    }

    public function showTitle($showTitle = false)
    {
        global $mod_strings, $app_strings;

        if (is_null($this->viewObject)) {
            $this->viewObject = (!empty($GLOBALS['current_view'])) ? $GLOBALS['current_view'] : new SugarView();
        }

        if ($showTitle) {
            return $this->viewObject->getModuleTitle();
        }

        return '';
    }

    protected function getPanelWithFillers($panel)
    {
        $addFiller = true;
        foreach ($panel as $row) {
            if (count($row) == $this->defs['templateMeta']['maxColumns'] || 1 == count($panel)) {
                $addFiller = false;
                break;
            }
        }

        if ($addFiller) {
            $rowCount = count($panel);
            $filler = count($panel[$rowCount - 1]);
            while ($filler < $this->defs['templateMeta']['maxColumns']) {
                $panel[$rowCount - 1][$filler++] = array('field' => array('name' => ''));
            }
        }
        return $panel;
    }

    public function processReadOnlyFields($fieldDefs)
    {
        if (!empty($fieldDefs) && !empty($this->activityRow)) {

            $readOnlyFields = array();
            if (isset($this->activityRow['act_readonly_fields'])) {
                $readOnlyFields = json_decode(base64_decode($this->activityRow['act_readonly_fields']));
            }

            foreach ($fieldDefs as $fieldKey => $field) {
                if (!empty($readOnlyFields) && in_array($fieldKey, $readOnlyFields)) {
                    $fieldDefs[$fieldKey]['viewType'] = 'DetailView';
                } else {
                    $fieldDefs[$fieldKey]['viewType'] = 'EditView';
                }
            }
            return $fieldDefs;
        } else {
            return $fieldDefs;
        }
    }

    public function setDefaultAllFields($fieldDefs)
    {
        foreach ($fieldDefs as $fieldKey => $field) {
            $fieldDefs[$fieldKey]['viewType'] = 'EditView';
        }
        return $fieldDefs;
    }


    public function setReadOnlyAllFields($fieldDefs)
    {
        foreach ($fieldDefs as $fieldKey => $field) {
            $fieldDefs[$fieldKey]['viewType'] = 'DetailView';
        }
        return $fieldDefs;
    }

    public function processRequiredFields($fieldDefs)
    {
        if (!empty($fieldDefs) && !empty($this->activityRow)) {

            $requiredFields = array();
            if (isset($this->activityRow['act_required_fields'])) {
                $requiredFields = json_decode(base64_decode($this->activityRow['act_required_fields']));
            }

            foreach ($fieldDefs as $fieldKey => $field) {
                if (!empty($requiredFields) && in_array($fieldKey, $requiredFields)) {
                    $fieldDefs[$fieldKey]['required'] = true;
                    $fieldDefs[$fieldKey]['importable'] = 'required';
                }
            }
            return $fieldDefs;
        } else {
            return $fieldDefs;
        }
    }

    public function validationsRequiredFields()
    {
        $requiredFields = '';
        foreach ($this->fieldDefs as $fieldKey => $field) {
            if (isset($this->fieldDefs[$fieldKey]['required'])
                && $this->fieldDefs[$fieldKey]['required']
                && $field['viewType'] != 'DetailView') {
                $requiredFields .= '"' . $this->fieldDefs[$fieldKey]['name'] . '",';
            }
        }
        if (!empty($requiredFields)) {
            $requiredFields = '[' . substr($requiredFields, 0, -1) . ']';
        }
        return $requiredFields;
    }

    public function processSectionPanels($panels)
    {
        $readOnlyFields = array();
        if (isset($this->activityRow['act_readonly_fields'])) {
            $readOnlyFields = json_decode(base64_decode($this->activityRow['act_readonly_fields']));
        }
        foreach ($panels as $panelKey => $panel) {
            foreach ($panel as $rowKey => $row) {
                foreach ($row as $fieldKey => $field) {
                    if (!empty($readOnlyFields) && in_array($field['field']['name'], $readOnlyFields)) {
                        $panels[$panelKey][$rowKey][$fieldKey]['field']['hideLabel'] = 0;
                        if (!empty($panels[$panelKey][$rowKey][$fieldKey]['colspan'])) {
                            $panels[$panelKey][$rowKey][$fieldKey]['colspan'] = $panels[$panelKey][$rowKey][$fieldKey]['colspan'] - 1;
                        }
                    }
                }
            }
        }
        return $panels;
    }


    protected function _displaySubPanels()
    {
        if (!empty($this->bean->id) &&
            (SugarAutoLoader::existingCustom('modules/' . $this->module . '/metadata/subpaneldefs.php') ||
                SugarAutoLoader::loadExtension("layoutdefs", $this->module))
        ) {
            $GLOBALS['focus'] = $this->bean;
            require_once('include/SubPanel/SubPanelTiles.php');
            $subpanel = new SubPanelTiles($this->bean, $this->module);
            echo $subpanel->display();
        }
    }


    public function getAjaxRelationships($relationships)
    {
        $ajaxrels = array();
        $relationshipList = $relationships->getRelationshipList();
        foreach ($relationshipList as $relationshipName) {
            $rel = $relationships->get($relationshipName)->getDefinition();
            $rel ['lhs_module'] = translate($rel['lhs_module']);
            $rel ['rhs_module'] = translate($rel['rhs_module']);

            //#28668  , translate the relationship type before render it .
            switch ($rel['relationship_type']) {
                case 'one-to-one':
                    $rel['relationship_type_render'] = translate('LBL_ONETOONE');
                    break;
                case 'one-to-many':
                    $rel['relationship_type_render'] = translate('LBL_ONETOMANY');
                    break;
                case 'many-to-one':
                    $rel['relationship_type_render'] = translate('LBL_MANYTOONE');
                    break;
                case 'many-to-many':
                    $rel['relationship_type_render'] = translate('LBL_MANYTOMANY');
                    break;
                default:
                    $rel['relationship_type_render'] = '';
            }
            $rel ['name'] = $relationshipName;
            if ($rel ['is_custom'] && isset($rel ['from_studio']) && $rel ['from_studio']) {
                $rel ['name'] = $relationshipName . "*";
            }
            $ajaxrels [] = $rel;
        }
        return $ajaxrels;
    }
}