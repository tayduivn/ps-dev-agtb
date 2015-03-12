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
 * Class that analyzes the data type of a bean
 * getting the value of this field according to the data type
 * if there is a date data type used the classes TimeDate()
 *
 */
class PMSEFieldParser implements PMSEDataParserInterface
{
    /**
     * Object Bean
     * @var object
     */
    private $evaluatedBean;

    /**
     * Lists modules Bean
     * @var array
     */
    private $beanList;
    private $currentUser;

    /**
     * gets the bean list
     * @return array
     * @codeCoverageIgnore
     */
    public function getBeanList()
    {
        return $this->beanList;
    }

    /**
     * sets the bean list
     * @param array $beanList
     */
    public function setBeanList($beanList)
    {
        $this->beanList = $beanList;
    }

    /**
     * gets the bean
     * @return object
     * @codeCoverageIgnore
     */
    public function getEvaluatedBean()
    {
        return $this->evaluatedBean;
    }

    /**
     * sets the bean
     * @param object $evaluatedBean
     */
    public function setEvaluatedBean($evaluatedBean)
    {
        $this->evaluatedBean = $evaluatedBean;
    }

    /**
     * sets the current user
     * @param object $currentUser
     * @codeCoverageIgnore
     */
    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * get the class TimeDate()
     * @return object
     * @codeCoverageIgnore
     */
    public function getTimeDate()
    {
        if (!isset($this->timeDate) || empty($this->timeDate)) {
            $this->timeDate = new TimeDate();
        }
        return $this->timeDate;
    }

    /**
     * set the class TimeDate()
     * @param object $timeDate
     * @codeCoverageIgnore
     */
    public function setTimeDate($timeDate)
    {
        $this->timeDate = $timeDate;
    }

    /**
     * Parser token incorporando el tipo de dato, en el caso de tipo de dato date, datetime se usa la clase TimeDate
     * @global object $current_user cuurrent user
     * @param object $criteriaToken token to be parsed
     * @param array $params
     * @return object
     */
    public function parseCriteriaToken($criteriaToken, $params = array())
    {
        if ($criteriaToken->expType === 'VARIABLE') {
            $criteriaToken = $this->parseVariable($criteriaToken, $params);
        } else {
            $criteriaToken = $this->parseCriteria($criteriaToken, $params);
        }
        return $criteriaToken;
    }
    
    /**
     * parse the token ussing the old function
     * @global object $current_user
     * @param type $criteriaToken
     * @param type $params
     * @return type
     */
    public function parseCriteria($criteriaToken, $params = array())
    {
        switch ($criteriaToken->expOperator) {
            case 'equals':
                $delimiter = '==';
                break;
            case 'not_equals':
                $delimiter = '!=';
                break;
            case 'major_equals_than':
                $delimiter = '>=';
                break;
            case 'minor_equals_than':
                $delimiter = '<=';
                break;
            case 'minor_than':
                $delimiter = '<';
                break;
            case 'major_than':
                $delimiter = '>';
                break;
            case 'within':
                $delimiter = 'within';
                break;
            case 'not_within':
                $delimiter = 'not within';
                break;
            default:
                $delimiter = '==';
                break;
        }

        //$tokenValueArray = explode($delimiter, $criteriaToken->expLabel);
        $tokenDelimiter = '::';        
        $newTokenArray = array('{', 'future', $criteriaToken->expModule, $criteriaToken->expField, '}');
        $assembledTokenString = implode($tokenDelimiter, $newTokenArray);
        $tokenValue = $this->parseTokenValue($assembledTokenString);
        $criteriaToken->expToken = $assembledTokenString;
        $criteriaToken->currentValue = $tokenValue;
        if ($this->evaluatedBean->field_name_map[$criteriaToken->expField]['type']=='date') {
            $criteriaToken->expSubtype = 'date';
        } elseif ($this->evaluatedBean->field_name_map[$criteriaToken->expField]['type']=='datetime'
                || $this->evaluatedBean->field_name_map[$criteriaToken->expField]['type']=='datetimecombo') {
            $criteriaToken->expSubtype = 'date';
            global $current_user;
            // Instantiate the TimeDate Class
            $timeDate = $this->getTimeDate();
            ////new TimeDate();
            // Call the function
            $localDate = $timeDate->to_display_date_time($tokenValue, true, true, $current_user);
            $criteriaToken->currentValue = $localDate;
        }
        return $criteriaToken;
    }

    /**
     * Parse the token using a new function to parse variable tokens
     * @global object $current_user
     * @param type $criteriaToken
     * @param type $params
     * @return type
     */
    public function parseVariable($criteriaToken, $params = array())
    {
        switch ($criteriaToken->expOperator) {
            case 'equals':
                $delimiter = '==';
                break;
            case 'not_equals':
                $delimiter = '!=';
                break;
            case 'major_equals_than':
                $delimiter = '>=';
                break;
            case 'minor_equals_than':
                $delimiter = '<=';
                break;
            case 'minor_than':
                $delimiter = '<';
                break;
            case 'major_than':
                $delimiter = '>';
                break;
            case 'within':
                $delimiter = 'within';
                break;
            case 'not_within':
                $delimiter = 'not within';
                break;
            default:
                $delimiter = '==';
                break;
        }

        //$tokenValueArray = explode($delimiter, $criteriaToken->expLabel);
        $tokenDelimiter = '::';

        $newTokenArray = array('{', 'future', $criteriaToken->expModule, $criteriaToken->expValue, '}');
        $assembledTokenString = implode($tokenDelimiter, $newTokenArray);
        $tokenValue = $this->parseTokenValue($assembledTokenString);
        $criteriaToken->expToken = $assembledTokenString;
        $criteriaToken->currentValue = $tokenValue;
        if ($this->evaluatedBean->field_name_map[$criteriaToken->expValue]['type']=='date') {
            $criteriaToken->expSubtype = 'date';
        } elseif ($this->evaluatedBean->field_name_map[$criteriaToken->expValue]['type']=='datetime'
                || $this->evaluatedBean->field_name_map[$criteriaToken->expValue]['type']=='datetimecombo') {
            $criteriaToken->expSubtype = 'date';
        }
        $criteriaToken->expValue = $criteriaToken->currentValue;
        return $criteriaToken;
    }

    /**
     * parser a token for a field element, is this: bool or custom fields
     * @param string $token field contains a parser
     * @return string field value
     */
    public function parseTokenValue($token)
    {
        global $timedate, $current_user;
        $tokenArray = $this->decomposeToken($token);
        $all = array();

        if ($this->evaluatedBean->parent_type == $tokenArray[1]) {
            $bean = BeanFactory::retrieveBean($this->evaluatedBean->parent_type, $this->evaluatedBean->parent_id);
            $all[] = $this->evaluatedBean;
        } else {
            $bean = $this->evaluatedBean;
        }

        $value = '';
        $isAValidBean = true;
        if (!empty($tokenArray)) {
            $status = $tokenArray[0];
            $module = isset($this->beanList[$tokenArray[1]]) ? $tokenArray[1] : '';
            if ($module == '') {// @codeCoverageIgnoreStart
                $relationships = new DeployedRelationships($bean->module_name);
                $rel_module = $relationships->get($tokenArray[1])->getDefinition();
                $conditionModule = strtolower($rel_module['rhs_module']);
                $join_key_b = strtolower($rel_module['join_key_rhs']);

                if (!$rel_module['is_custom']) {
                    //Normal Related
                    $bean->load_relationship($conditionModule);
                    $relatedField = $rel_module['rhs_table'];
                    $relationship = $bean->$relatedField;
                    reset($relationship->rows);
                    $id = key($relationship->rows);
                    if (isset($id) && !empty($id)) {
                        $all = array(BeanFactory::retrieveBean($rel_module['rhs_module'], $id));
                    } else {
                        $all = array();
                    }
                } else {
                    //Custom related
                    global $db;
                    $join_key_a = strtolower($rel_module['join_key_lhs']);
                    $query = "select * from "
                            . "$tokenArray[1]_c where $join_key_a = '" .
                            $bean->id . "' AND deleted=0 ORDER BY date_modified DESC";
                    $result = $db->Query($query);
                    $row = $db->fetchByAssoc($result);
                    $moduleBean = BeanFactory::getBean($rel_module['rhs_module'], $row[$join_key_b]);
                    $all = array($moduleBean);
                }
            }// @codeCoverageIgnoreEnd
            $field = $tokenArray[2];
        }
        $isAValidBean = (trim($module) == trim($bean->module_name));
        $isBoolean = ('bool' == $bean->field_name_map[$field]['type']);
        if ($isAValidBean) {
            $def = $bean->field_defs[$field];
            if ($def['type'] == 'datetime'){
                date_default_timezone_set('UTC');
                $datetime = new Datetime($bean->$field);
                $value = $timedate->asIso($datetime, $current_user);
            } else {
                $value = $bean->$field;
            }
            if ($isBoolean) {
                $value = ($value==1)? true : false;
            }
        } else {
            $value = !empty($all)?array_pop($all)->$tokenArray[2]:null;
        }
        return $value;
    }

    /**
     * converts a string {:: future :: Users :: id ::} to an array ('future','Users','id')
     * @param string $token @example {:: future :: Users :: id ::}
     * @return array
     */
    public function decomposeToken($token)
    {
        $response = array();
        $tokenArray = explode('::', $token);
        foreach ($tokenArray as $key => $value) {
            if ($value != '{' && $value != '}' && !empty($value)) {
                $response[] = $value;
            }
        }
        return $response;
    }
}
