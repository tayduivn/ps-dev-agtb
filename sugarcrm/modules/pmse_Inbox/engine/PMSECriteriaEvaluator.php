<?php

/**
 * Description of PMSECriteriaEvaluator
 * 
 */
class PMSECriteriaEvaluator
{
    protected $expressionEvaluator;

    public function __construct()
    {
        $this->expressionEvaluator = new PMSEExpressionEvaluator();
    }
    
    public function isCriteriaToken($token)
    {
        $result = false;
        $criteriaTypes = array (
            'MODULE',
            'CONTROL',
            'BUSINESS_RULES',
            'USER_ROLE',
            'USER_ADMIN',
            'USER_IDENTITY'
        );

        if (in_array($token->expType, $criteriaTypes)) {
            $result = true;
        }
        
        return $result;
    }
    
    public function evaluateCriteriaToken($criteriaToken)
    {
        $resultToken = new stdClass();
        $resultToken->expType = 'CONSTANT';
        $operationGroup = 'relation';
        //$resultToken->expSubtype = $this->retrieveCriteriaSubtype($criteriaToken->expSubtype);
        $resultToken->expValue = $this->expressionEvaluator->routeFunctionOperator(
            $operationGroup,
            $criteriaToken->currentValue,
            $criteriaToken->expOperator,
            $criteriaToken->expValue,
            $criteriaToken->expSubtype
        );
        $this->expressionEvaluator->processTokenAttributes($resultToken);
        return $resultToken;
    }
    
    public function evaluateCriteriaTokenList($tokenArray)
    {
        foreach ($tokenArray as $key => $token) {
            if ($this->isCriteriaToken($token)) {
                $tokenArray[$key] = $this->evaluateCriteriaToken($token);
            }
        }
        return $tokenArray;
    }
    
//    public function retrieveCriteriaSubtype($subType)
//    {
//        $type = 'string';
//        switch (strtolower($subtype)) {
//            case 'name':
//            case 'textfield':
//            case 'varchar':
//            case 'dropdown':
//            case 'enum':
//            case 'textarea':
//            case 'text':
//            case 'html':
//            case 'url':
//            case 'radio':
//            case 'radioenum':
//                $type = 'string';
//            break;
//            case 'checkbox':
//            case 'bool':
//                $type = 'boolean';
//            break;
//            case 'date':
//            case 'datetime':
//            case 'datetimecombo':
//                $type = 'date';
//            break;            
//            case 'currency':
//            case 'float':
//            case 'integer':
//            case 'int':
//            case 'decimal':
//                $type = 'number';
//            break;
//            default :
//                $type = $subType;
//            break;
//        }
//        return $type;
//    }
//    
//    public function retrieveCaster($subtype)
//    {
//        switch ($subtype) {
//            case 'string':
//            case 'date':
//            case 'boolean':
//                $caster = $subtype;
//                break;
//            case 'number':
//                $caster = '';
//            default:
//                break;
//        }
//    }
}
