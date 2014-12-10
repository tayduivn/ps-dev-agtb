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

require_once 'PMSEEngineUtils.php';
require_once 'PMSEImporter.php';
require_once 'modules/pmse_Project/clients/base/api/wrappers/PMSERelatedDependencyWrapper.php';

/**
 * Description of the ProjectImporter class
 * This class is in charge of the import of bpm files into Processes.
 */
class PMSEProjectImporter extends PMSEImporter
{

    /**
     * The import result
     * @var \stdClass
     */
    private $importResult;

    /**
     * The array of saved elements
     * @var array
     */
    private $savedElements = array();

    /**
     * The array of changed uid elements
     * @var array
     */
    private $changedUidElements = array();

    /**
     * The list of default flows
     * @var array
     */
    private $defaultFlowList = array();

    /**
     *
     * @var type
     */
    protected $dependenciesWrapper;

    /**
     * The class constructor
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Project'); //new BpmEmailTemplate();
        $this->dependenciesWrapper = new PMSERelatedDependencyWrapper();
        $this->name = 'name';
        $this->id = 'prj_id';
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getSavedElements()
    {
        return $this->savedElements;
    }

    /**
     *
     * @param type $savedElements
     * @codeCoverageIgnore
     */
    public function setSavedElements($savedElements)
    {
        $this->savedElements = $savedElements;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getDependenciesWrapper()
    {
        return $this->dependenciesWrapper;
    }

    /**
     *
     * @param type $dependenciesWrapper
     * @codeCoverageIgnore
     */
    public function setDependenciesWrapper($dependenciesWrapper)
    {
        $this->dependenciesWrapper = $dependenciesWrapper;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getDefaultFlowList()
    {
        return $this->defaultFlowList;
    }

    /**
     *
     * @param type $defaultFlowList
     * @codeCoverageIgnore
     */
    public function setDefaultFlowList($defaultFlowList)
    {
        $this->defaultFlowList = $defaultFlowList;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getChangedUidElements()
    {
        return $this->changedUidElements;
    }

    /**
     *
     * @param type $changedUidElements
     * @codeCoverageIgnore
     */
    public function setChangedUidElements($changedUidElements)
    {
        $this->changedUidElements = $changedUidElements;
    }


    /**
     * Save the project data into the bpm project, and process beans, validates the uniqueness of
     * ids and also saves the rest.
     * @param $projectData
     * @return bool|void
     */
    public function saveProjectData($projectData)
    {
        global $current_user;
        $projectObject = $this->getBean(); //new BpmnProject();
        $keysArray = array();
        unset($projectData[$this->id]);
        //Unset common fields
        $this->unsetCommonFields($projectData);
        //unset($projectData['assigned_user_id']);
        if (!isset($projectData['assigned_user_id'])) {
            $projectData['assigned_user_id'] = $current_user->id;
        }

        if (isset($projectData['prj_name']) && !empty($projectData['prj_name'])) {
            $name = $this->getNameWithSuffix($projectData['prj_name']);
        } else {
            $name = $this->getNameWithSuffix($projectData[$this->name]);
        }

        $projectData['name'] = $name;
        $projectData['process']['name'] = $name;
        $projectData['diagram'][0]['name'] = $name;

        foreach ($projectData as $processFieldKey => $processFieldContent) {
            if ($processFieldKey != 'diagram') {
                $projectObject->$processFieldKey = $processFieldContent;
            }
        }
        // PMSEEngineUtils::validateUniqueUid($projectObject, 'prj_uid');
        $projectObject->prj_uid = PMSEEngineUtils::generateUniqueID();
        $keysArray['prj_id'] = $projectObject->save();

        $diagramBean = BeanFactory::newBean('pmse_BpmnDiagram'); //new BpmnDiagram();
        unset($projectData['diagram'][0]['dia_id']);
        foreach ($projectData['diagram'][0] as $diaFieldKey => $diaFieldContent) {
            $diagramBean->$diaFieldKey = $diaFieldContent;
        }
        $diagramBean->prj_id = $keysArray['prj_id'];
        //$diagramBean->new_with_id = true;
        //PMSEEngineUtils::validateUniqueUid($diagramBean, 'dia_uid'); //$diagramBean->validateUniqueUid();
        $diagramBean->dia_uid = PMSEEngineUtils::generateUniqueID(); //$diagramBean->validateUniqueUid();
        $keysArray['dia_id'] = $diagramBean->save();

        $processBean = BeanFactory::newBean('pmse_BpmnProcess'); //new BpmnProcess();
        $processBean->prj_id = $keysArray['prj_id'];
        $processBean->dia_id = $keysArray['dia_id'];
        $processBean->pro_name = $projectObject->prj_name;
        unset($projectData['process']['pro_id']);
        foreach ($projectData['process'] as $key => $value) {
            $processBean->$key = $value;
        }
        $processBean->pro_uid = PMSEEngineUtils::generateUniqueID(); //$processBean->validateUniqueUid();
        $projectData['diagram'][0]['documentation'] = isset($projectData['diagram'][0]['documentation']) ? $projectData['diagram'][0]['documentation'] : array();

        $keysArray['pro_id'] = $processBean->save();

        //$definitionTmpArray = array();
        //$definitionTmpArray[] = $projectData['definition'];
        //$this->saveProjectElementsData($definitionTmpArray, $keysArray, 'pmse_BpmProcessDefinition', false, true);
        $processDefinitionBean = BeanFactory::newBean('pmse_BpmProcessDefinition');
        $processDefinitionBean->prj_id = $keysArray['prj_id'];
        unset($projectData['definition']['pro_id']);
        foreach ($projectData['definition'] as $key => $value) {
            $processDefinitionBean->$key = $value;
        }
        $processDefinitionBean->id = $keysArray['pro_id'];
        $processDefinitionBean->new_with_id = true;
        $processDefinitionBean->save();

//        $this->saveProjectElementsData($projectData['rulesets'], $keysArray, 'BpmRuleSet');
        $this->saveProjectActivitiesData($projectData['diagram'][0]['activities'], $keysArray);
        $this->saveProjectEventsData($projectData['diagram'][0]['events'], $keysArray);
        $this->saveProjectGatewaysData($projectData['diagram'][0]['gateways'], $keysArray);
        $this->saveProjectElementsData($projectData['diagram'][0]['documentation'], $keysArray,
            'pmse_BpmnDocumentation', false, true);
        $this->saveProjectElementsData($projectData['diagram'][0]['extension'], $keysArray, 'pmse_BpmnExtension', false,
            true);
        $this->saveProjectElementsData($projectData['diagram'][0]['pools'], $keysArray, 'pmse_BpmnLaneset', true);
        $this->saveProjectElementsData($projectData['diagram'][0]['lanes'], $keysArray, 'pmse_BpmnLane', true);
        $this->saveProjectElementsData($projectData['diagram'][0]['participants'], $keysArray, 'pmse_BpmnParticipant');
        $this->saveProjectElementsData($projectData['diagram'][0]['artifacts'], $keysArray, 'pmse_BpmnArtifact', true);
        $this->saveProjectElementsData($projectData['diagram'][0]['data'], $keysArray, 'pmse_BpmnData', false, true);
        $this->saveProjectElementsData($projectData['dynaforms'], $keysArray, 'pmse_BpmDynaForm');
        $this->saveProjectFlowsData($projectData['diagram'][0]['flows'], $keysArray);
        $this->processDefaultFlows();
        return $keysArray['prj_id'];
    }

    /**
     * @codeCoverageIgnore
     * @deprecated since version 1.612
     */
    public function getFileProjectData($filePath)
    {
        return false;
    }

    /**
     * Save the project activities data.
     * @param array $activitiesData
     * @param array $keysArray
     */
    public function saveProjectActivitiesData($activitiesData, $keysArray)
    {
        foreach ($activitiesData as $element) {
            $activityBean = BeanFactory::getBean('pmse_BpmnActivity'); //new BpmnActivity();
            $boundBean = BeanFactory::getBean('pmse_BpmnBound'); //new BpmnBound();
            $definitionBean = BeanFactory::getBean('pmse_BpmActivityDefinition'); //new BpmActivityDefinition();
            $element['prj_id'] = $keysArray['prj_id'];
            $element['pro_id'] = $keysArray['pro_id'];
            foreach ($element as $key => $value) {
                switch ($key) {
                    case 'act_name':
                        $activityBean->name = $value;
                        break;
                    case 'act_type':
                        $activityBean->$key = 'TASK';
                        break;
                    default:
                        $activityBean->$key = $value;
                        break;
                }
                $boundBean->$key = $value;
                $definitionBean->$key = $value;
            }

            if (isset($activityBean->act_default_flow) && !empty($activityBean->act_default_flow)) {
                $this->defaultFlowList[$element['act_default_flow']] = array(
                    'bean' => 'BpmnActivity',
                    'search_field' => 'act_uid',
                    'search_field_value' => $activityBean->act_uid,
                    'default_flow' => $element['act_default_flow'],
                    'default_flow_field' => 'act_default_flow'
                );
            }
            $previousUid = $activityBean->act_uid;
            $activityBean->act_uid = PMSEEngineUtils::generateUniqueID();
            $this->changedUidElements[$previousUid] = array('new_uid' => $activityBean->act_uid);

            $currentID = $activityBean->save();
            if (!isset($this->savedElements['bpmnActivity'])) {
                $this->savedElements['bpmnActivity'] = array();
                $this->savedElements['bpmnActivity'][$activityBean->act_uid] = $currentID;
            } else {
                $this->savedElements['bpmnActivity'][$activityBean->act_uid] = $currentID;
            }

            $boundBean->bou_uid = PMSEEngineUtils::generateUniqueID();
            $boundBean->dia_id = $keysArray['dia_id'];
            $boundBean->element_id = $keysArray['dia_id'];
            $boundBean->bou_element_type = 'bpmnActivity';
            $boundBean->bou_element = $currentID;
            //PMSEEngineUtils::validateUniqueUid($boundBean, 'bou_uid'); //we already generate an unique id above.
            $boundBean->save();
            $definitionBean->id = $currentID;
            $definitionBean->pro_id = $keysArray['pro_id'];
            $definitionBean->dia_id = $keysArray['dia_id'];
            if ($element['act_task_type'] == 'SCRIPTTASK' && $element['act_script_type'] == 'BUSINESS_RULE') {
                $definitionBean->act_fields = $this->savedElements['BpmRuleSet'][$element['act_fields']];
            }
            $definitionBean->new_with_id = true;
            $defID = $definitionBean->save();
        }
    }

    /**
     * Save the project events data.
     * @param array $eventsData
     * @param array $keysArray
     */
    public function saveProjectEventsData($eventsData, $keysArray)
    {
        foreach ($eventsData as $element) {
            $eventBean = BeanFactory::getBean('pmse_BpmnEvent'); //new BpmnEvent();
            $boundBean = BeanFactory::getBean('pmse_BpmnBound'); //new BpmnBound();
            $definitionBean = BeanFactory::getBean('pmse_BpmEventDefinition'); //new BpmEventDefinition();
            $element['prj_id'] = $keysArray['prj_id'];
            $element['pro_id'] = $keysArray['pro_id'];
            foreach ($element as $key => $value) {
                switch ($key) {
                    case 'evn_name':
                        $eventBean->name = $value;
                        break;
                    case 'evn_message':
                        $eventBean->$key = $this->changeEventMessage($value);
                        break;
                    default:
                        $eventBean->$key = $value;
                        break;
                }
                $boundBean->$key = $value;
                $definitionBean->$key = $value;
            }
            $previousUid = $eventBean->evn_uid;
            $eventBean->evn_uid = PMSEEngineUtils::generateUniqueID();
            $this->changedUidElements[$previousUid] = array('new_uid' => $eventBean->evn_uid);
            $currentID = $eventBean->save();
            if (!isset($this->savedElements['bpmnEvent'])) {
                $this->savedElements['bpmnEvent'] = array();
                $this->savedElements['bpmnEvent'][$eventBean->evn_uid] = $currentID;
            } else {
                $this->savedElements['bpmnEvent'][$eventBean->evn_uid] = $currentID;
            }

            $boundBean->bou_uid = PMSEEngineUtils::generateUniqueID();
            $boundBean->dia_id = $keysArray['dia_id'];
            $boundBean->element_id = $keysArray['dia_id'];
            $boundBean->bou_element_type = 'bpmnEvent';
            $boundBean->bou_element = $currentID;
            //$boundBean->validateUniqueUid();
            $boundBean->save();
            $definitionBean->id = $currentID;
            $definitionBean->pro_id = $keysArray['pro_id'];
            $definitionBean->dia_id = $keysArray['dia_id'];
            $definitionBean->new_with_id = true;
            $definitionBean->save();
            if (!empty($currentID)) {
                $definitionBean->evn_id = $currentID;
                $this->dependenciesWrapper->processRelatedDependencies($eventBean->toArray() + $definitionBean->toArray());
            }
        }
    }

    /**
     * Save the project gateways data.
     * @param array $gatewaysData
     * @param array $keysArray
     */
    public function saveProjectGatewaysData($gatewaysData, $keysArray)
    {
        foreach ($gatewaysData as $element) {
            $gatewayBean = BeanFactory::getBean('pmse_BpmnGateway'); //new BpmnGateway();
            $boundBean = BeanFactory::getBean('pmse_BpmnBound'); //new BpmnBound();
            $element['prj_id'] = $keysArray['prj_id'];
            $element['pro_id'] = $keysArray['pro_id'];
            foreach ($element as $key => $value) {
                switch ($key) {
                    case 'gat_name':
                        $gatewayBean->name = $value;
                        break;
                    default:
                        $gatewayBean->$key = $value;
                        break;
                }
                $boundBean->$key = $value;
            }
            if (isset($gatewayBean->gat_default_flow) && !empty($gatewayBean->gat_default_flow)) {
                $this->defaultFlowList[$element['gat_default_flow']] = array(
                    'bean' => 'BpmnGateway',
                    'search_field' => 'gat_uid',
                    'search_field_value' => $gatewayBean->gat_uid,
                    'default_flow' => $element['gat_default_flow'],
                    'default_flow_field' => 'gat_default_flow'
                );
            }
            $previousUid = $gatewayBean->gat_uid;
            $gatewayBean->gat_uid = PMSEEngineUtils::generateUniqueID();
            $this->changedUidElements[$previousUid] = array('new_uid' => $gatewayBean->gat_uid);
            $currentID = $gatewayBean->save();
            if (!isset($this->savedElements['bpmnGateway'])) {
                $this->savedElements['bpmnGateway'] = array();
                $this->savedElements['bpmnGateway'][$gatewayBean->gat_uid] = $currentID;
            } else {
                $this->savedElements['bpmnGateway'][$gatewayBean->gat_uid] = $currentID;
            }

            $boundBean->bou_uid = PMSEEngineUtils::generateUniqueID();
            $boundBean->dia_id = $keysArray['dia_id'];
            $boundBean->element_id = $keysArray['dia_id'];
            $boundBean->bou_element_type = 'bpmnGateway';
            $boundBean->bou_element = $currentID;
            //$boundBean->validateUniqueUid();
            $boundBean->save();
        }
    }

    /**
     * Save the project flows data.
     * @param array $flowsData
     * @param array $keysArray
     */
    public function saveProjectFlowsData($flowsData, $keysArray)
    {
        foreach ($flowsData as $element) {
            $flowBean = BeanFactory::getBean('pmse_BpmnFlow'); //new BpmnFlow();
            $element['prj_id'] = $keysArray['prj_id'];
            $element['pro_id'] = $keysArray['pro_id'];
            $element['dia_id'] = $keysArray['dia_id'];
            foreach ($element as $key => $value) {
                if ($key == 'flo_state') {
                    $flowBean->$key = json_encode($value);
                } else {
                    if ($key == 'flo_element_origin') {
                        if (isset($this->changedUidElements[$value])) {
                            $flowBean->$key = $this->savedElements[$element['flo_element_origin_type']][$this->changedUidElements[$value]['new_uid']];
                        } else {
                            $flowBean->$key = $this->savedElements[$element['flo_element_origin_type']][$value];
                        }
                    } elseif ($key == 'flo_element_dest') {
                        if (isset($this->changedUidElements[$value])) {
                            $flowBean->$key = $this->savedElements[$element['flo_element_dest_type']][$this->changedUidElements[$value]['new_uid']];
                        } else {
                            $flowBean->$key = $this->savedElements[$element['flo_element_dest_type']][$value];
                        }
                    } elseif ($key == 'flo_condition') {
                        $condition = $this->processBusinessRulesData($element['flo_condition']);
                        $flowBean->$key = (!empty($condition)) ? json_encode($condition) : null;
                    } elseif ($key == 'flo_is_inmediate') {
                        $flowBean->$key = (!empty($value)) ? $value : null;
                    } else {
                        $flowBean->$key = $value;
                    }
                }
            }

            $previousUid = $flowBean->flo_uid;
            $flowBean->flo_uid = PMSEEngineUtils::generateUniqueID();
            //if ($flowBean->validateUniqueUid() && isset($this->defaultFlowList[$previousUid])) {
            if (isset($this->defaultFlowList[$previousUid])
            ) {
                $this->defaultFlowList[$previousUid]['default_flow'] = $flowBean->flo_uid;
                $this->defaultFlowList[$flowBean->flo_uid] = $this->defaultFlowList[$previousUid];
                unset($this->defaultFlowList[$previousUid]);
            }
            $flowBean->save();
        }
    }

    /**
     * Save the project elements data.
     * @param $elementsData
     * @param $keysArray
     * @param $beanType
     * @param bool $generateBound
     * @param bool $generateWithId
     * @param string $field_uid
     */
    public function saveProjectElementsData(
        $elementsData,
        $keysArray,
        $beanType,
        $generateBound = false,
        $generateWithId = false,
        $field_uid = ''
    ) {
        //$beanFactory = new ADAMBeanFactory();
        foreach ($elementsData as $element) {
            $boundBean = BeanFactory::getBean('pmse_BpmnBound'); //new BpmnBound();
//            $elementBean = new $beanType();
            $elementBean = BeanFactory::getBean($beanType); //$beanFactory->getBean($beanType);

            $element['prj_id'] = $keysArray['prj_id'];
            $element['pro_id'] = $keysArray['pro_id'];
            $element['dia_id'] = $keysArray['dia_id'];
            foreach ($element as $key => $value) {
                if (strpos($key, '_name') !== false) {
                    $elementBean->name = $value;
                } else {
                    $elementBean->$key = $value;
                }
                if ($generateBound) {
                    $boundBean->$key = $value;
                }
                if (strpos($key, '_uid') !== false) {
                    $uid = $key;
                }
            }
            //$elementBean->new_with_id = $generateWithId;
            $savedId = $elementBean->save();
            //$elementUid = $savedId; //$elementBean->getPrimaryFieldUID();
            if (!empty($savedId)) {
                $this->savedElements[$beanType][$elementBean->$uid] = $savedId;
            }
            if (!empty($field_uid)) {
                $elementBean->$field_uid = PMSEEngineUtils::generateUniqueID(); //$elementBean->validateUniqueUid();
            }
            if ($generateBound) {
                $boundBean->save();
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * Displays the import result response as a JSON string
     */
    public function displayResponse()
    {
        echo json_encode($this->importResult);
    }

    /**
     * Additional processing to the default flows
     */
    public function processDefaultFlows()
    {
        foreach ($this->defaultFlowList as $defaultFlow) {
            $elementBean = BeanFactory::getBean('pmse_' . $defaultFlow['bean']); //new $defaultFlow['bean']();
            $elementBean->retrieve_by_string_fields(array($defaultFlow['search_field'] => $defaultFlow['search_field_value']));
            $flowBean = BeanFactory::getBean('pmse_BpmnFlow'); //new BpmnFlow();
            $flowBean->retrieve_by_string_fields(array('flo_uid' => $defaultFlow['default_flow']));
            $elementBean->$defaultFlow['default_flow_field'] = $flowBean->flo_id;
            $elementBean->save();
        }
    }

    /**
     * Additional processing the Business rules imported data.
     * @param array $conditionArray
     * @return array
     * @deprecated since version pmse2
     * @codeCoverageIgnore
     */
    public function processBusinessRulesData($conditionArray = array())
    {
        if (is_array($conditionArray)) {
            foreach ($conditionArray as $key => $value) {
                if (isset($value->expType) && $value->expType == 'BUSINESS_RULES') {
                    $activityBeam = BeanFactory::getBean('pmse_BpmnActivity');
                    $activityBeam->retrieve_by_string_fields(array('act_uid' => $value->expField));
                    $conditionArray[$key]->expField = $activityBeam->act_id;
                }
            }
        }
        return $conditionArray;
    }

    /**
     * Change name of modules to new version
     * @codeCoverageIgnore
     * @param $message
     * @return mixed
     */
    private function changeEventMessage($message)
    {
        $arr = array(
            'LEAD' => 'Leads',
            'OPPORTUNITIES' => 'Opportunities',
            'DOCUMENTS' => 'Documents'
        );
        if (key_exists($message, $arr)) {
            return $arr[$message];
        } else {
            return $message;
        }
    }

}
