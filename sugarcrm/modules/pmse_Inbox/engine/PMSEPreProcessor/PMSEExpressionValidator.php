<?php

require_once 'PMSEValidate.php';
require_once 'modules/pmse_Inbox/engine/PMSEExpressionEvaluator.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

/**
 * Description of PMSERecordValidator
 *
 */
class PMSEExpressionValidator implements PMSEValidate
{

    /**
     *
     * @var Integer 
     */
    protected $level;

    /**
     *
     * @var PMSELogger
     */
    protected $logger;

    /**
     *
     * @var type 
     */
    protected $expressionEvaluator;

    /**
     * 
     * @param type $level
     * @codeCoverageIgnore
     */
    public function __construct($level)
    {
        $this->level = $level;
        $this->logger = PMSELogger::getInstance();
        $this->expressionEvaluator = new PMSEExpressionEvaluator();
    }

    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * 
     * @return PMSELogger
     * @codeCoverageIgnore
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getExpressionEvaluator()
    {
        return $this->expressionEvaluator;
    }

    /**
     * 
     * @param type $expressionEvaluator
     * @codeCoverageIgnore
     */
    public function setExpressionEvaluator($expressionEvaluator)
    {
        $this->expressionEvaluator = $expressionEvaluator;
    }

    /**
     * 
     * @param PMSELogger $logger
     * @codeCoverageIgnore
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * 
     * @param type $level
     * @codeCoverageIgnore
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * 
     * @param PMSERequest $request
     * @return \PMSERequest     
     */
    public function validateRequest(PMSERequest $request)
    {
        $this->logger->info("Validate Request " . get_class($this));
        $this->logger->debug("Request data" . print_r($request, true));

        $flowData = $request->getFlowData();
        $bean = $request->getBean();
        if ($flowData['evn_id'] != 'TERMINATE') {
            $paramsRelated = $this->validateParamsRelated($bean, $flowData, $request);
            if ($request->isValid()) {
                $this->validateExpression($bean, $flowData, $request, $paramsRelated);
            }
        }
        return $request;
    }

    /**
     * 
     * @param type $bean
     * @param type $flowData
     * @param type $request
     * @param type $paramsRelated
     * @return type
     */
    public function validateExpression($bean, $flowData, $request, $paramsRelated = array())
    {
        if ($flowData['evn_criteria'] == '' || $flowData['evn_criteria'] == '[]' || $this->expressionEvaluator->evaluateExpression(trim($flowData['evn_criteria']), $bean, $paramsRelated)) {
            $request->validate();
        } else {
            $request->invalidate();
        }

        $condition = $this->expressionEvaluator->condition();
        $this->logger->debug("Eval: $condition returned " . ($request->isValid()));
        return $request;
    }

    /**
     * 
     * @param type $bean
     * @param type $flowData
     * @param type $request
     * @return array
     */
    public function validateParamsRelated($bean, $flowData, $request)
    {
        $paramsRelated = array();

        if ($request->getExternalAction() == 'EVALUATE_RELATED_MODULE') {
            if ($bean->parent_type == $flowData['rel_process_module'] && $bean->parent_id == $flowData['cas_sugar_object_id']
            ) {
                $paramsRelated = array(
                    'replace_fields' => array(
                        $flowData['rel_element_relationship'] => $flowData['rel_element_module']
                    )
                );
            } else {
                $request->invalidate();
            }
        }
        
        if ($request->getExternalAction() == 'EVALUATE_MAIN_MODULE') {
            if (
                $bean->module_name != $flowData['cas_sugar_module'] 
                || $bean->id != $flowData['cas_sugar_object_id']
            ){
                $request->invalidate();
            }
        }
        
        $this->logger->debug("Parameters related returned :" . print_r($paramsRelated, true));
        return $paramsRelated;
    }

}
