<?php
/**
 * Class that an analysis of a business rule and evaluates
 * classes used PMSEBusinessRuleConversor where it parses a business rule and
 * PMSEExpressionEvaluator performs an evaluation of the conditions have a business rule
 *
 */

require_once 'PMSEBusinessRuleConversor.php';
require_once 'PMSEExpressionEvaluator.php';

class PMSEBusinessRuleReader
{
    /**
     * Global evaluation extencion
     * @var string 
     */
    public $extensionGlobal = 'G@';
    
    /**
     * additional variables necessary
     * @var array 
     */
    public $appDataVar = array();
    
    /**
     * global variables
     * @var array 
     */
    public $globalVar = array();
    
    /**
     * Object of class PMSEExpressionEvaluator
     * @var object 
     */
    public $expressionEvaluator;
    
    /**
     * Object of class PMSEBusinessRuleConversor
     * @var object 
     */
    public $businessRuleConversor;

    /**
     * Constructor
     * @param type $appData
     * @param type $global
     */
    public function __construct($appData = array(), $global = array())
    {
        $this->appDataVar = $appData;
        $this->globalVar = $global;
        $this->businessRuleConversor = new PMSEBusinessRuleConversor();
        $this->expressionEvaluator = new PMSEExpressionEvaluator();
    }
    
    /**
     * get object variable to analyze the business rule
     * @return object
     */
    public function getBusinessRuleParser()
    {
        return $this->businessRuleConversor;
    }

    /**
     * set object variable to analyze the business rule
     * @param object $businessRuleParser
     */
    public function setBusinessRuleParser($businessRuleParser)
    {
        $this->businessRuleConversor = $businessRuleParser;
    }

    /**
     * get variable object for evaluation
     * @return object
     */
    public function getExpressionEvaluator()
    {
        return $this->expressionEvaluator;
    }

    /**
     * set variable object for evaluation
     * @param object $expressionEvaluator
     */
    public function setExpressionEvaluator($expressionEvaluator)
    {
        $this->expressionEvaluator = $expressionEvaluator;
    }
    /*
    public function parseRuleset($sugarModule, $ruleset, $type = 'single')
    {
        $result = '';
        $res = '';
        $log = '';
        $appData = $this->appDataVar;
        $global = $this->globalVar;

        //initialize some variables
        $ruleInfo = array();
        $rawRule = '';
        $ruleName = '';
        $rulesetName = '';
        $success = false;
        $successRule = "";
        $successAction = "";
        $successReturn = '';
        $isMultiHit = false;

        $lines = explode("\n", $ruleset);
        $lineId = 0;
        while ($lineId < count($lines) && $success == false) {
            $line = trim($lines[$lineId++]);

            if (preg_match('/^ruleset "{0,1}([^"]*)"{0,1} (.*)/', $line, $matches)) {
                $rulesetName = trim($matches[1]);
                $testHit = strtolower(trim($matches[2]));
                $isMultiHit = ($testHit == 'multi-hit' || $testHit == 'multiple') ? true : false;
                $log .= "\tRuleset $rulesetName is " . ($isMultiHit ? 'multi-hit' : 'single-hit') . " \n";
                $returnValue = '';
                continue;
            }

            if (preg_match('/^rule "{1,1}([^"]*)"{0,1} {0,1}(.*)/', $line, $matches)) {
                $ruleName = trim($matches[1]);
                //$log .= "start of rule : $ruleName " . ($isMultiHit ? 'multi-hit': 'single-hit') ." \n";
                if (!$isMultiHit) {
                    //some rules shouldn't have a return value, but in single hit, they always return
                    //rule's return value, if not defined for the rule then returns empty.
                    $returnValue = '';
                }
                continue;
            }

            if (preg_match('/^ {0,8}if {0,8}/', $line, $matches)) {
                $line = $lines[$lineId++];
                $condition = '';
                $reachThen = (preg_match('/^ {0,8}then {0,8}/', $line, $matches));
                while ($lineId < count($lines) && !$reachThen) {
                    $condition .= trim($line) . ' ';
                    $line = $lines[$lineId++];
                    $reachThen = (preg_match('/^ {0,8}then {0,8}/', $line, $matches));
                }

                $line = trim($lines[$lineId++]);
                $actions = '';
                $reachEnd = (preg_match('/^ {0,8}end {0,8}/', $line, $matches));
                //while (!feof($fh) && !$reachEnd) {
                while ($lineId < count($lines) && !$reachEnd) {
                    if (strtoupper(trim($line)) != 'AND') {
                        if (preg_match('/^return "{0,1}([^"]*)"{0,1}/i', $line, $matches)) {
                            $line = $matches[1];
                            preg_match("/^'{0,1}([^']*)'{0,1}/", $line, $matches);
                            $returnValue = $matches[1];
                        } else {
                            $actions .= trim($line);
                        }
                    }
                    $line = trim($lines[$lineId++]);
                    $reachEnd = (preg_match('/^ {0,8}end {0,8}/', $line, $matches));
                }
                $actions = trim($actions);

                $toEval = $condition;
                $log .= "\tExpresion to be evaluated: $toEval\n";
                foreach ($appData as $key => $value) {
                    $sugarField = '{::' . $sugarModule . '::' . $key . '::}';
                    $moduleBean = BeanFactory::getBean($sugarModule, $appData['id']);
                    $value = bpminbox_get_display_text($moduleBean, $key, $value);
                    //this is a trick, we are adding quotes to the variable
                    //todo, check if the field is numeric, if yes we need to replace without quotes
                    $toEvalNew = str_replace("'" . $sugarField . "'", "'" . $value . "'", $toEval);
                    if (is_numeric($value)) {
                        $toEval = str_replace($sugarField, 1.0 * $value, $toEvalNew);
                    } else {
                        $toEval = str_replace($sugarField, "'" . $value . "'", $toEvalNew);
                    }
                    if ($toEvalNew != $toEval) {
                        $bean = BeanFactory::getBean($sugarModule);
                        $type = get_bean_field_type($key, $bean);
                        $log .= "\t\t$sugarField is '" . $type['type'] . "' type and '" . $type['db_type'] . "' BD_type\n";
                        $toEvalNew = $toEval;
                    }
                }

                foreach ($global as $key => $value) {
                    $toEval = str_replace($this->extensionGlobal . $key, $value, $toEval);
                }

                $toEval = str_ireplace(' and ', ' && ', $toEval);
                $toEval = str_ireplace(' or ', ' || ', $toEval);

                //be sure we are using double = in the conditions,
                //$toEval = str_ireplace('==', '=', $toEval);
                $toEval = str_ireplace(' = ', '==', $toEval);
                $result = 0;

                eval("\$result = $toEval;");
                //$log .= "$condition \n";  //this is the original condition rule
                //$log .= "$toEval \n";     //this is the rule with replaced values
                $log .= "$ruleName is " . ($result ? 'true' : 'false') . "\n";

                if ($result) {
                    $success = true;
                    $successRule .= ' ' . $ruleName;
                    $successAction .= ' ' . $actions;
                    $successReturn = $returnValue; //always store the last return value

                    $log .= "Valid condition: '$toEval'\n" . str_replace(';', "\n", $actions) . "\n";

                    //execute each change part not only the return should be returned by single-hit
                    $changes = trim($actions);
                    $commands = explode(';', $changes);
                    foreach ($commands as $command) {
                        $parts = explode('=', trim($command));
                        if (!is_array($parts) || !isset($parts[1])) {
                            continue;
                        }

                        $targetVar = explode('::', trim($parts[0]));
                        $targetModule = $targetVar[1];
                        $targetField = $targetVar[2];

                        $expresion = trim($parts[1]);
                        $log .= "command: $targetField  = $expresion\n";

                        //start code duplicated, todo: improve this!! using numeric type fields
                        $toEval = $expresion;
                        foreach ($appData as $key => $value) {
                            $sugarField = '{::' . $sugarModule . '::' . $key . '::}';
                            if (is_numeric($value)) {
                                $toEval = str_replace($sugarField, trim($value), $toEval);
                            } else {
                                $toEval = str_replace($sugarField, "'" . $value . "'", $toEval);
                            }
                        }
                        $log .= "Rule to be evaluated  $toEval\n";

                        foreach ($global as $key => $value) {
                            $toEval = str_replace($this->extensionGlobal . $key, $value, $toEval);
                        }
                        //end code duplicated, todo: improve this!!

                        $result = '';
                        eval("\$result = $toEval;");
                        if ($targetModule == $sugarModule) {
                            $appData[$targetField] = $result;
                        }
                        $log .= "evaluated: $targetField  = $result\n";
                    }
                }

                if ($isMultiHit) {
                    $success = false;
                }
                continue;
            }

            if (preg_match('/^ {0,8}then {0,8}/', $line, $matches)) {
                //print "THEN : '" . $matches[1] . "'";
                //print_r($matches);
                continue;
            }

            if (trim($line) != '') {
                $log .= 'error in line: ' . $line . "\n";
            }
        }

        if (!$success && !$isMultiHit) {
            $log .= "\nall rules fired, but all rules returned false!\n";
        }
        if ($success || $isMultiHit) {
            $log .= "return value: $successReturn \n";
            $successAction = trim($successAction);
            //print "rule : $successRule\naction:\n$successAction\n";
            $res .= $successAction;
        }

        $newAppData = array();
        foreach ($this->appDataVar as $key => $value) {
            if ($value != $appData[$key]) {
                $newAppData[$key] = $appData[$key];
            }
        }
        return array('log' => $log, 'return' => $successReturn, 'result' => $res, 'newAppData' => $newAppData);
    }*/
    
    /**
     * Method that converts a standard business rule conditions and makes the evaluation of the condition
     * @param string $sugarModule the module case
     * @param json $ruleSetJSON the expression
     * @param string $type
     * @return array
     */
    public function parseRuleSetJSON($sugarModule, $ruleSetJSON, $type = 'single')
    {
        $global = $this->globalVar;
        $res = '';
        $evaluatedBean = BeanFactory::getBean($sugarModule, $this->appDataVar['id']);
        $ruleSet = json_decode($ruleSetJSON);
        $resultArray = array();
        $appData = array();
        $newAppData = array();
        $successReturn = "";
        $evaluationResult = true;
        $this->businessRuleConversor->setBaseModule($ruleSet->base_module);
        foreach ($ruleSet->ruleset as $key => $rule) {
            $this->businessRuleConversor->setEvaluatedBean($evaluatedBean);
            $transformedCondition = $this->businessRuleConversor->transformConditions($rule->conditions);
            $transformedCondition = json_encode($transformedCondition);
            $evaluationResult = $this->expressionEvaluator->evaluateExpression($transformedCondition, $evaluatedBean);
            if ($evaluationResult) {
                $successReturn = $this->businessRuleConversor->getReturnValue($rule->conclusions);
//                $newAppData = $this->businessRuleConversor->processAppData($rule->conclusions, $appData);
                $newAppData = array_merge($newAppData, $this->businessRuleConversor->processAppData($rule->conclusions, $appData));
                $res .= $this->businessRuleConversor->processConditionResult($rule->conclusions, $appData);
            }
            if ($type == 'single' && $evaluationResult) {
                break;
            }
        }
        /*
        foreach ($this->appDataVar as $key => $value) {
            if ($value != $appData[$key])) {
                $newAppData[$key] = $appData[$key];
            }
        }
        */
        //$successReturn = "ANOTHER_ZONE";
        
        //$newAppData = array(
        //    "description" => "POTENTIAL SALE",
        //    "probability" => 0.16
        //);
        
        //$res = "{::Opportunities::description::} = 'POTENTIAL CONTACT';{::Opportunities::probability::} = 0.06;";
        $log = "The following condition: \n".$transformedCondition." has returned: \n".  json_encode($successReturn);
        $resultArray = array('log' => $log, 'return' => $successReturn, 'result' => $res, 'newAppData' => $newAppData);
        return $resultArray;
    }
}
