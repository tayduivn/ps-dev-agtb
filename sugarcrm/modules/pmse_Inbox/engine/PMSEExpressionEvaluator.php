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

/**
 * Description of PMSEExpressionEvaluator
 *
 */
class PMSEExpressionEvaluator
{
    /**
     * Store the existing groups and subgroups
     * @var array
     */
    public $arrayGroups;

    /**
     * The types of operators that supports the evaluation according to the
     * following order ot operations
     * 'unary', 'exponent', 'multiplication/division', 'addition/substraction', 'relations', 'logic'
     * @var array
     */
    public $operationList = array (
        'unary' => array('NOT', '!'), //unary operators
        'exponent' => array('^'), //function for evalAritmetic
        'multiply_divide' => array('x', '/'), //function for Multiplication Division
        'add_substract' => array('+', '-'), //function for Add and Substraction
        'relation' => array('<', '<=', '>', '>=', '==', '!='), //function for evalRelations
        'logic' => array('AND', 'OR') //function for evalLogic
    );

    /**
     * history assessments by token
     * @var string
     */
    public $condition;

    public function getCondition()
    {
        return $this->condition;
    }

    public function evaluateExpression($expression)
    {
        $accArray = array();
        foreach ($this->operationList as $groupKey => $groupOperator) {
            while ($token = array_shift($expression)) {
                if (in_array($token->expValue, $groupOperator)
                        && $token->expType!=='VARIABLE'
                        && $token->expType!=='CONSTANT') {

                    if ($groupKey != 'unary') {
                        $firstOperand = array_pop($accArray);
                    } else {
                        $firstOperand = $this->getDefaultToken();
                    }
                    $accArray[] = $this->processOperation(
                        $groupKey,
                        $firstOperand,
                        $token,
                        array_shift($expression)
                    );
                } else {
                    $accArray[] = $token;
                }
            }
            $expression = $accArray;
            $accArray = array();
        }
        return $expression;
    }

    public function getDefaultToken()
    {
        $token = new stdClass();
        $token->expType = 'CONSTANT';
        $token->expSubtype = 'string';
        $token->expValue = '';
        $token->expLabel = '';
        return $token;
    }

    public function processOperation($groupKey, $firstToken, $token, $secondToken)
    {
        $resultToken = new stdClass();
        $resultToken->expType = 'CONSTANT';

        $operationGroup = $this->checkDateEvaluation(
            $groupKey,
            $firstToken,
            $token,
            $secondToken
        );
        $resultToken->expValue = $this->routeFunctionOperator(
            $operationGroup,
            $firstToken->expValue,
            $token->expValue,
            $secondToken->expValue
        );
        $this->processTokenAttributes($resultToken);
        return $resultToken;
    }

    public function checkDateEvaluation($key, $firstToken, $operator, $secondToken)
    {
        if ((strtolower($firstToken->expSubtype)=='date' || strtolower($firstToken->expSubtype)=='datetime') && strtolower($secondToken->expSubtype)=='timespan') {
            switch ($operator->expValue) {
                case '+':
                    $key = 'dateAdd';
                    break;
                case '-':
                    $key = 'dateSubstract';
                    break;
            }
        }
        return $key;
    }

    public function processExpression($expression = array())
    {
        $resultGroup = array();
        while ($token = array_shift($expression)) {
            switch (true) {
                case $token->expValue === '(':
                    $expression = $this->processExpression($expression);
                    break;
                case $token->expValue === ')':
                    $resultGroup = array_merge($this->evaluateExpression($resultGroup), $expression);
                    return $resultGroup;
                    break;
                default:
                    $resultGroup[] = $token;
                    break;
            }

        }
        return $this->evaluateExpression($resultGroup);
    }


    /**
     * Check to see if there groups and get their positions
     * @param array $array Array in which groups will verify if there
     * @param array $this->arrayGroups the number of groups is stored in this attribute
     * @deprecated since version pmse2
     */
//    public function verifyGroups($array)
//    {
//        $arrayGroups = array(array("(", ")"), array("[", "]"), array("{", "}"));
//        $arrayGroupsExist = array();
//        foreach ($arrayGroups as $group) {
//            $arrGroup = array();
//            foreach ($group as $sig) {
//                $arrSubGroup = array();
//                $i = 0;
//                while ($i < count($array)) {
//                    if ("$array[$i]" == $sig) {
//                        $arrSubGroup[] = $i;
//                    }
//                    $i++;
//                }
//                if (!empty($arrSubGroup))
//                    $arrGroup[$sig] = $arrSubGroup;
//            }
//            if (!empty($arrGroup))
//                $arrayGroupsExist[] = $arrGroup;
//        }
//        $this->arrayGroups = $arrayGroupsExist;
//    }

    /**
     * We check parity groups if there is a (,), [,], {,} that is not closed
     * @return Boolean if there is the same amount associators that open and close one we return false
     */
//    public function verifyEqualsGroups()
//    {
//        $arrayGroups = $this->arrayGroups;
//        $value = true;
//        foreach ($arrayGroups as $group) {
//            $count = 0;
//            foreach ($group as $arr) {
//                $count = count($arr) - $count;
//            }
//            if ($count != 0)
//                $value = false;
//        }
//        return $value;
//    }

    /**
     * Evaluation without existecia partnerships
     * @param array $array Array which evaluates only operators having
     * @return int return values ​​of zero or one depending on the evaluation
     * @deprecated since version pmse2
     */
//    public function executeOperation($array)
//    {
//        foreach ($this->operationList as $funOpe => $operators) {
//            foreach ($operators as $sig) {
//                $j = 0;
//                if(count($array) <= 1){
//                    break;
//                }
//                while ((count($array) - 1) >= $j) {
//                    $ele = isset($array[$j]) ? $array[$j] : '';
//                    if ("$ele" == $sig && "$ele" != 'NOT') {
//                        $array[$j - 1] = $this->routeFunctionOperator($funOpe,
//                        $array[$j - 1], $array[$j], $array[$j + 1]);
//                        unset($array[$j + 1]);
//                        unset($array[$j]);
//                        $j = 0;
//                        $array = array_values($array);
//                    } elseif ("$ele" == $sig && "$ele" == 'NOT') {
//                        $array[$j] = $this->routeFunctionOperator($funOpe, $array[$j + 1], $array[$j]);
//                        unset($array[$j + 1]);
//                        $j = 0;
//                        $array = array_values($array);
//                    } else {
//                        $j++;
//                    }
//                }
//            }
//        }
//        return array_shift($array);
//    }

    /**
     * Method to address the function to conduct the operation
     * @param string $operation Name of the method to redirect
     * @param string $firstOperand assess value
     * @param string $operator type of operator that evaluates
     * @param string $secondOperand assess value
     * @return type
     */
    public function routeFunctionOperator(
        $operation,
        $firstOperand,
        $operator,
        $secondOperand = null,
        $tokenType = null
    ) {
        switch ($operation) {
            case 'unary':
                $result = $this->executeUnaryOp($operator, $secondOperand);
                break;
            case 'exponent':
                $result = $this->executeExponentOp($firstOperand, $operator, $secondOperand);
                break;
            case 'multiply_divide':
                $result = $this->executeMultiplyDivideOp($firstOperand, $operator, $secondOperand);
                break;
            case 'add_substract':
                $result = $this->executeAddSubstractOp($firstOperand, $operator, $secondOperand);
                break;
            case 'dateAdd':
                $result = $this->executeDateOp($firstOperand, $operator, $secondOperand);
                break;
            case 'dateSubstract':
                $result = $this->executeDateOp($firstOperand, $operator, $secondOperand);
                break;
            case 'relation':
                $result = $this->executeRelationsOp(
                    $firstOperand,
                    $operator,
                    $secondOperand,
                    $tokenType
                );
                break;
            case 'logic':
                $result = $this->executeLogicOp($firstOperand, $operator, $secondOperand);
                break;
            default:
                $result = 0;
                break;
        }
        return $result;
    }

    public function processTokenAttributes($token)
    {
        switch (true) {
            case is_integer($token->expValue):
            case is_double($token->expValue):
            case is_float($token->expValue):
                $token->expSubtype = 'number';
                $token->expLabel = (string)$token->expValue;
                break;
            case is_string($token->expValue)://if is string
                $token->expSubtype = 'string';
                $token->expLabel = $token->expValue;
                break;
            case is_bool($token->expValue):
                $token->expSubtype = 'boolean';
                $boolarray = array(false => 'false', true => 'true');
                $token->expLabel = $boolarray[$token->expValue];
                break;
            case is_a($token->expValue, 'DateTime'):
                $dateTimeObject = $token->expValue;
                $token->expValue = $dateTimeObject->format('c');
                $token->expLabel = $dateTimeObject->format('Y-m-d H:i:s');
                $token->expSubtype = 'date';
                break;
        }
        return $token;
    }

    public function executeExponentOp($value1, $operator, $value2)
    {
        return pow($value1, $value2);
    }

    public function executeUnaryOp($operator, $value)
    {
        $result = 0;
        switch ($operator) {
            case 'NOT':
                $result = !(bool)($value);
                break;
            case '!':
                $result = !(bool)($value);
                break;
        }
        return $result;
    }

    public function executeMultiplyDivideOp($value1, $operator, $value2)
    {
        $result = 0;
        switch ($operator) {
            case '/':
                $result = $value1 / $value2;
                break;
            case 'x':
                $result = $value1 * $value2;
                break;
        }
        return $result;
    }

    public function executeAddSubstractOp($value1, $operator, $value2)
    {
        $result = 0;
        switch ($operator) {
            case '+':
                $result = $value1 + $value2;
                break;
            case '-':
                $result = $value1 - $value2;
                break;
        }
        return $result;
    }

    public function executeDateOp($value1, $operator, $value2)
    {
        $dateString = $value1. " ".$operator. $this->processDateInterval($value2);
        $dateObject = new DateTime();
        $dateObject->setTimestamp(strtotime($dateString));
        return $dateObject;
    }

    public function processDateInterval($interval)
    {
        $pattern = "/(\d*)(y|min|m|w|d|h)/";
        preg_match($pattern, $interval, $matches);
        $dateUnit = $this->processDateUnit($matches[2]);
        $result = $matches[1]." ".$dateUnit;
        return $result;
    }

    public function processDateUnit($unit)
    {
        switch ($unit) {
            case 'y':
                return 'days';
            break;
            case 'm':
                return 'months';
            break;
            case 'w':
                return 'weeks';
            break;
            case 'd':
                return 'days';
            break;
            case 'h':
                return 'hours';
            break;
            case 'min':
                return 'minutes';
            break;
        }
    }


    /**
     * Method that evaluates the arithmetic part
     * @param int $value1 value
     * @param string $operator arithmetic operator
     * @param int $value2 value
     * @return int returned the result to be evaluated
     * @deprecated since version pmse2
     */
//    public function executeAritmeticOperation($value1, $operator, $value2)
//    {
//        switch ($operator) {
//            case '+':
//                $ret = $value1 + $value2;
//                break;
//            case '-':
//                $ret = $value1 - $value2;
//                break;
//            case '/':
//                if ($value2 > 0)
//                    $ret = $value1 / $value2;
//                else
//                    $ret = 0;
//                break;
//            case 'x':
//                $ret = $value1 * $value2;
//                break;
//            default:
//                $ret = 0;
//                break;
//        }
//        return $ret;
//    }

    /**
     * Method that evaluates the relational part
     * @param string $value1 value
     * @param string $relational This value can be null or
     * must not contain the "==", "=", ">", "> =", "<>", "! =", "<", "<="
     * @param string $value2 value
     * @return int
     */
    public function executeRelationsOp($value1, $relational, $value2, $typeDate = 'typeDefault')
    {
        $arrayRelationsSig = array("==", ">", ">=", "!=", "<", "<=");
        $arrayRelationsLit = array(
            "equals",
            "major_than",
            "major_equals_than",
            "not_equals",
            "minor_than",
            "minor_equals_than",
            "within",
            "not_within"
        );
        $pos = false;
        $relLit = $relational;
        if (in_array($relational, $arrayRelationsLit)) {
            $pos = true;
        }
        if (in_array($relational, $arrayRelationsSig)) {//transform sign the literal
            $clave = array_search($relational, $arrayRelationsSig);
            $relLit = $arrayRelationsLit[$clave];
            $pos = true;
        }
        if ($pos === false) {
            return 0;
        }
        $value1 = $this->typeData($value1, $typeDate);
        $value2 = $this->typeData($value2, $typeDate);
        $this->condition .= ':(' . $value1 . '):'; // . $relSig . ' ' . $value2 . '::';
        switch ($relLit) {
            case 'equals':
                if ($value1 == $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'major_than':
                if ($value1 > $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'major_equals_than':
                if ($value1 >= $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'not_equals':
                if ($value1 != $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'minor_than':
                if ($value1 < $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'minor_equals_than':
                if ($value1 <= $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
//            case 'within'://revisar
//                break;
//            case 'not_within'://revisar
//                break;
        }
        return (bool)$ret;
    }

    /**
     * Method that evaluates the logic part
     * @param boolean $value1 takes the values ​​1 or 0
     * @param string $logical if evaluate AND, OR, NOT
     * @param boolean $value2 takes the values ​​1 or 0 in the case of NOT is null
     * @return int
     */
    public function executeLogicOp($value1, $logical, $value2 = null)
    {
        switch ($logical) {
            case 'AND':
                if ($value1 && $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            case 'OR':
                if ($value1 || $value2) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
                break;
            default:
                $ret = 0;
                break;
        }
        return (bool)$ret;
    }

    /**
     * Method that takes cast depending on the type of value
     * (int), (integer) - integer
     * (bool), (boolean) - boolean
     * (float), (double), (real) - float
     * (string) - string
     * (array) - array
     * (object) - object
     * (unset) - NULL (PHP 5)
     * @param type $value
     * @param type $tipeDate
     * @return type
     */
    public function typeData($value, $typeDate)
    {
        switch (strtolower($typeDate)) {
            case 'address'://varchar
            case 'relate'://varchar
            case 'text':
            case 'url'://varchar
            case 'textfield'://varchar
            case 'name'://varchar
            case 'varchar'://varchar
            case 'radioenum': //varchar
            case 'parent_type'://varchar
                $newValue = (string) $value;
                break;
            case 'bool'://bool
            case 'boolean':
            case 'checkbox':
                if (!empty($value) && $value==='false') {
                    $newValue = false;
                } else {
                    $newValue = (boolean)$value;
                }
                break;
            case 'date'://date
            case 'datetime'://datetime
            case 'datetimecombo'://datetime
                $newValue = strtotime($value);
                break;
            case 'enum'://int
            case 'int':
                $newValue = (int) $value;
                break;
            case 'float':
                $newValue = (float) $value;
                break;
            case 'integer':
                $newValue = (int) $value;
                break;
            case 'decimal': //decimal
                $newValue = (float)$value;
                break;
            case 'currency': //double
                $newValue = (double)$value;
                break;
            case 'encrypt':
            case 'html':
            case 'iframe':
            case 'image':
            case 'multienum':
            case 'phone':
            default:
                $newValue = $value;
                break;
        }
        return $newValue;
    }
}
