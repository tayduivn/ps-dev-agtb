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

require_once('PMSEExporter.php');
require_once('modules/pmse_Inbox/engine/PMSEEngineUtils.php');

/**
 * Exports a record of Project
 *
 * This class extends the class PMSEExporter to export a record
 * from the tables related with a pmse_Project to transport it from one instance to another.
 * @package PMSE
 * @codeCoverageIgnore
 */
class PMSEProjectExporter extends PMSEExporter
{

    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Project'); //new BpmEmailTemplate();
        $this->uid = 'id';
        $this->name = 'name';
        $this->extension = 'bpm';
    }

    /**
     * Method to retrieve a record of the database to export.
     * @param array $args
     * @return array
     */
    public function getProject(array $args)
    {
        $this->bean->retrieve($args['id']);

        if ($this->bean->fetched_row != false) {
            $this->projectId = $this->bean->id;
            $this->bean->fetched_row = PMSEEngineUtils::unsetCommonFields($this->bean->fetched_row,
                array('name', 'description'));
            $this->bean->fetched_row['process'] = $this->getProjectProcess();
            $this->bean->fetched_row['diagram'] = $this->getProjectDiagram($this->bean->id);
            $this->bean->fetched_row['definition'] = $this->getProcessDefinition();
            $this->bean->fetched_row['dynaforms'] = $this->getProjectDynaforms();

            return array("metadata" => $this->getMetadata(), "project" => $this->bean->fetched_row);
        } else {
            return array("error" => true);
        }
    }

    /**
     * Get the project process data
     * @return array
     */
    public function getProjectProcess()
    {
        $processBean = BeanFactory::getBean('pmse_BpmnProcess'); //new BpmnProcess();
        $processData = array();
        $processBean->retrieve_by_string_fields(array("prj_id" => $this->projectId));
        if (!empty($processBean->fetched_row)) {
            $processData = PMSEEngineUtils::unsetCommonFields($processBean->fetched_row, array('name', 'description'));
            $processData = PMSEEngineUtils::sanitizeKeyFields($processData);
        }
        return $processData;
    }

    /**
     * Get the project Diagram data with a determined Project Id
     * @param string $prjID
     * @return array
     */
    public function getProjectDiagram($prjID)
    {
        $diagramBean = BeanFactory::getBean('pmse_BpmnDiagram'); //new BpmnDiagram();
        $diagramData = array();
        $activityBean = BeanFactory::getBean('pmse_BpmnActivity'); //new BpmnActivity();
        $activityData = array();
        $artifactBean = BeanFactory::getBean('pmse_BpmnArtifact'); //new BpmnArtifact();
        $artifactData = array();
        $gatewayBean = BeanFactory::getBean('pmse_BpmnGateway'); //new BpmnGateway();
        $gatewayData = array();
        $eventBean = BeanFactory::getBean('pmse_BpmnEvent'); //new BpmnEvent();
        $eventData = array();
        $flowBean = BeanFactory::getBean('pmse_BpmnFlow'); //new BpmnFlow();
        $flowData = array();
        $rulesetBean = BeanFactory::getBean('pmse_Business_Rules'); //new BpmRuleSet();
        $rulesetData = array();
        $lanesetBean = BeanFactory::getBean('pmse_BpmnLaneset'); //new BpmnLaneset();
        $lanesetData = array();
        $laneBean = BeanFactory::getBean('pmse_BpmnLane'); //new BpmnLane();
        $laneData = array();
//        $participantBean = BeanFactory::getBean('pmse_BpmnParticipant'); //new BpmnParticipant();
//        $participantData = array();
        $processBean = BeanFactory::getBean('pmse_BpmnProcess'); //new BpmnProcess();
        $processData = array();
        $retrievedDataBean = BeanFactory::getBean('pmse_BpmnData'); //new BpmnData();
        $retrievedData = array();
        $documentationBean = BeanFactory::getBean('pmse_BpmnDocumentation'); //new BpmnDocumentation();
        $documentationData = array();
        $extensionBean = BeanFactory::getBean('pmse_BpmnExtension'); //new BpmnExtension();
        $extensionData = array();
        $conditions = array("prj_id" => $prjID);
        if ($diagramBean->retrieve_by_string_fields($conditions)) {
            $diagramBean->fetched_row = PMSEEngineUtils::unsetCommonFields($diagramBean->fetched_row);
            // list of activities based in the project id
            //$data = $activityBean->getSelectRows("", "bpmn_activity.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnActivity'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_activity.act_id=bpmn_bound.bou_element'), array('LEFT', 'bpm_activity_definition', 'bpmn_activity.act_id=bpm_activity_definition.act_id')));
            $q = new SugarQuery();
            $q->from($activityBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $q->joinRaw("LEFT JOIN pmse_bpm_activity_definition c ON (a.id=c.id)", array('alias' => 'c'));
            $fields = $this->getFields('pmse_BpmnActivity', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnActivity'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }
            $fields_ad = $this->getFields('pmse_BpmActivityDefinition', array(), 'c');
            foreach ($fields_ad as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $tmpObject = $flowBean->retrieve_by_string_fields(array("id" => $row['act_default_flow']));
                    $row['act_default_flow'] = isset($tmpObject->flo_uid) ? $tmpObject->flo_uid : '';
                    if ($row['act_task_type'] == "SCRIPTTASK" && $row['act_script_type'] == "BUSINESS_RULE") {
                        $row['act_fields'] = isset($row['act_fields']) ? $row['act_fields'] : '';
                        $ruleset = $rulesetBean->retrieve_by_string_fields(array('id' => $row['act_fields']));
                        if ($ruleset) {
                            $row['act_fields'] = $ruleset->rst_uid;
                            $this->rulesetList[] = $ruleset->fetched_row;
                        }
                    }
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $activityData[] = $row;
                }
            }
            $diagramBean->fetched_row['activities'] = $activityData;

            // list of events based in the project id
            //$data = $eventBean->getSelectRows("", "bpmn_event.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnEvent'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_event.evn_id=bpmn_bound.bou_element'), array('LEFT', 'bpm_event_definition', 'bpmn_event.evn_id=bpm_event_definition.evn_id')));
            $q = new SugarQuery();
            $q->from($eventBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $q->joinRaw("LEFT JOIN pmse_bpm_event_definition c ON (a.id=c.id)", array('alias' => 'c'));
            $fields = $this->getFields('pmse_BpmnEvent', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnEvent'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }
            $fields_ad = $this->getFields('pmse_BpmEventDefinition', array(), 'c');
            foreach ($fields_ad as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $tmpActBean = $activityBean->retrieve_by_string_fields(array("id" => $row['evn_attached_to']));
                    $row['evn_attached_to'] = isset($tmpActBean->act_uid) ? $tmpActBean->act_uid : '';
                    $tmpActBean = $activityBean->retrieve_by_string_fields(array("id" => $row['evn_cancel_activity']));
                    $row['evn_cancel_activity'] = isset($tmpActBean->act_uid) ? $tmpActBean->act_uid : '';
                    $tmpActBean = $activityBean->retrieve_by_string_fields(array("id" => $row['evn_activity_ref']));
                    $row['evn_activity_ref'] = isset($tmpActBean->act_uid) ? $tmpActBean->act_uid : '';
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $eventData[] = $row;
                }
            }
            $diagramBean->fetched_row['events'] = $eventData;

            // list of gateways based in the project id
            //$data = $gatewayBean->getSelectRows("", "bpmn_gateway.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnGateway'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_gateway.gat_id=bpmn_bound.bou_element')));
            $q = new SugarQuery();
            $q->from($gatewayBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $fields = $this->getFields('pmse_BpmnGateway', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnGateway'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $flowObject = $flowBean->retrieve_by_string_fields(array("id" => $row['gat_default_flow']));
                    $row['gat_default_flow'] = isset($flowObject->flo_uid) ? $flowObject->flo_uid : '';
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $gatewayData[] = $row;
                }
            }
            $diagramBean->fetched_row['gateways'] = $gatewayData;

            // list of artifacts based in the project id
            //$data = $artifactBean->getSelectRows("", "bpmn_artifact.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnArtifact'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_artifact.art_id=bpmn_bound.bou_element')));
            $q = new SugarQuery();
            $q->from($artifactBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $fields = $this->getFields('pmse_BpmnArtifact', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnArtifact'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $artifactData[] = $row;
                }
            }
            $diagramBean->fetched_row['artifacts'] = $artifactData;

            // list of flows based in the project id
            //$data = $flowBean->getSelectRows("", "bpmn_flow.prj_id=" . $prjID, 0, -1, -1, array());
            $rows = $flowBean->get_full_list('', "prj_id='" . $prjID . "'");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row = $row->fetched_row;
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeKeyFields($row);
                    $row['prj_id'] = $prjID;
                    $row['flo_element_origin'] = PMSEEngineUtils::getElementUid($row['flo_element_origin'],
                        $row['flo_element_origin_type'],
                        PMSEEngineUtils::getEntityUid($row['flo_element_origin_type']));
                    $row['flo_element_dest'] = PMSEEngineUtils::getElementUid($row['flo_element_dest'],
                        $row['flo_element_dest_type'], PMSEEngineUtils::getEntityUid($row['flo_element_dest_type']));
                    $row['flo_state'] = json_decode($row['flo_state']);
                    $row['flo_condition'] = json_decode($row['flo_condition']);
                    $row['flo_condition'] = !empty($row['flo_condition']) ? $this->processBusinessRulesData($row['flo_condition']) : '';
                    $flowData[] = $row;
                }
            }
            $diagramBean->fetched_row['flows'] = $flowData;

            // list of pools based in the project id
            //$data = $lanesetBean->getSelectRows("", "bpmn_laneset.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnLaneset'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_laneset.lns_id=bpmn_bound.bou_element')));
            $q = new SugarQuery();
            $q->from($lanesetBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $fields = $this->getFields('pmse_BpmnLaneset', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnLaneset'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $lanesetData[] = $row;
                }
            }
            $diagramBean->fetched_row['pools'] = $lanesetData;

            // list of lanes based in the project id
            //$data = $laneBean->getSelectRows("", "bpmn_lane.prj_id=" . $prjID . " AND bpmn_bound.bou_element_type='bpmnLane'", 0, -1, -1, array(), array(array('INNER', 'bpmn_bound', 'bpmn_lane.lan_id=bpmn_bound.bou_element')));
            $q = new SugarQuery();
            $q->from($laneBean, array('alias' => 'a'));
            $q->joinRaw("INNER JOIN pmse_bpmn_bound b ON (a.id=b.bou_element)", array('alias' => 'b'));
            $fields = $this->getFields('pmse_BpmnLane', array('name'), 'a');
            $q->select($fields);
            $q->where()->queryAnd()
                ->addRaw("a.prj_id='" . $prjID . "' AND b.bou_element_type='bpmnLane'");
            $fields_bound = $this->getFields('pmse_BpmnBound', array(), 'b');
            foreach ($fields_bound as $key => $value) {
                $q->select->fieldRaw($value);
            }

            $rows = $q->execute();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row['bou_element'] = $row['bou_uid'];
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $row = PMSEEngineUtils::sanitizeFields($row);
                    $laneData[] = $row;
                }
            }
            $diagramBean->fetched_row['lanes'] = $laneData;

            // list of participants based in the project id
            //$data = $participantBean->getSelectRows("", "bpmn_participant.prj_id=" . $prjID, 0, -1, -1, array(), array());
//            $rows = $participantBean->get_full_list("", "prj_id='" . $prjID . "'");
//            if (!empty($rows)) {
//                foreach ($rows as $row) {
//                    $row = $row->fetched_row;
//                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
//                    $row['bou_element'] = $row['bou_uid'];
//                    $participantData[] = $row;
//                }
//            }
//            $diagramBean->fetched_row['participants'] = $participantData;
            $diagramBean->fetched_row['participants'] = array();

            // data list based in the project id
            //$data = $retrievedDataBean->getSelectRows("", "prj_id=" . $prjID, 0, -1, -1, array(), array());
            $rows = $retrievedDataBean->get_full_list("", "prj_id='" . $prjID . "'");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $retrievedData[] = $row->fetched_row;
                }
            }
            $diagramBean->fetched_row['data'] = $retrievedData;

            // documentation list based in the project id
            //$data = $documentationBean->getSelectRows("", "prj_id=" . $prjID, 0, -1, -1, array(), array());
            $rows = $documentationBean->get_full_list("", "prj_id='" . $prjID . "'");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row = $row->fetched_row;
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $documentationData[] = $row;
                }
            }
            $diagramBean->fetched_row['documentation'] = $documentationData;

            // data list based in the project id
            //$data = $extensionBean->getSelectRows("", "prj_id=" . $prjID, 0, -1, -1, array(), array());
            $rows = $extensionBean->get_full_list("", "prj_id='" . $prjID . "'");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $row = $row->fetched_row;
                    $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                    $extensionData[] = $row;
                }
            }
            $diagramBean->fetched_row['extension'] = $extensionData;

            array_push($diagramData, $diagramBean->fetched_row);
        }
        return $diagramData;
    }

    /**
     * Get the Process Definition data
     * @return array
     */
    public function getProcessDefinition()
    {
        $definitionBean = BeanFactory::getBean('pmse_BpmProcessDefinition'); //new BpmProcessDefinition();
        $definitionData = array();
        $definitionBean->retrieve_by_string_fields(array("prj_id" => $this->projectId));
        if (!empty($definitionBean->fetched_row)) {
            $definitionData = PMSEEngineUtils::unsetCommonFields($definitionBean->fetched_row,
                array('name', 'description'));
            $definitionData = PMSEEngineUtils::sanitizeKeyFields($definitionBean->fetched_row);
        }
        return $definitionData;
    }

    /**
     * Get the object list of dyanform records
     * @return array
     */
    public function getProjectDynaforms()
    {
        $dynaformsBean = BeanFactory::getBean('pmse_BpmDynaForm'); //new BpmDynaForm();
        $dynaformData = array();
        //$data = $dynaformsBean->getSelectRows("", "bpm_dynamic_forms.prj_id=" . $this->projectId, 0, -1, -1, array(), array());
        $rows = $dynaformsBean->get_full_list('', "prj_id='" . $this->projectId . "'");
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $row = $row->fetched_row;
                $row = PMSEEngineUtils::unsetCommonFields($row, array('name', 'description'));
                $row = PMSEEngineUtils::sanitizeKeyFields($row);
                $row['prj_id'] = $this->projectId;
                $dynaformData[] = $row;
            }
        }
        return $dynaformData;
    }

    /**
     * Additional processing to the Business Rules Data.
     * @param array $conditionArray
     * @return array
     */
    public function processBusinessRulesData($conditionArray = array())
    {
        if (is_array($conditionArray)) {
            foreach ($conditionArray as $key => $value) {
                if (isset($value->expType) && $value->expType == 'BUSINESS_RULES') {
                    $activityBeam = BeanFactory::getBean('pmse_BpmnActivity');
                    $activityBeam->retrieve_by_string_fields(array('id' => $value->expField));
                    $conditionArray[$key]->expField = $activityBeam->act_uid;
                }
            }
        }
        return $conditionArray;
    }

    private function getFields($module, $except = array(), $alias = '')
    {
        $result = array();
        $rows = array_flip(PMSEEngineUtils::getAllFieldsBean($module));
        $rows = PMSEEngineUtils::unsetCommonFields($rows, $except);
        foreach ($rows as $key => $value) {
            if (!empty($alias)) {
                $result[] = $alias . '.' . $key;
            } else {
                $result[] = $key;
            }
        }
        return $result;
    }
}