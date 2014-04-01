<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

class SugarQuery_Builder_Condition
{
    /**
     * @var string
     */
    public $operator;
    /**
     * @var string
     */
    public $field;
    /**
     * @var array
     */
    public $values = array();
    /**
     * @var bool|SugarBean
     */
    public $bean = false;
    /**
     * @var bool
     */
    public $isNull = false;
    /**
     * @var bool
     */
    public $notNull = false;

    /**
     * @var SugarQuery
     */
    public $query;

    public function __construct(SugarQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @param string $operator
     * @return SugarQuery_Builder_Condition
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @param array $values
     * @return SugarQuery_Builder_Condition
     */
    public function setValues($values)
    {
        $this->values = $values;
        $this->field->verifyCondition($values, $this->query);
        return $this;
    }

    /**
     * @param string $field
     * @return SugarQuery_Builder_Condition
     */
    public function setField($field)
    {
        $this->field = new SugarQuery_Builder_Field_Condition($field, $this->query);
        return $this;
    }

    /**
     * @param SugarBean $bean
     */
    public function setBean(SugarBean $bean)
    {
        $this->bean = $bean;
    }

    /**
     * @return SugarQuery_Builder_Condition
     */
    public function isNull()
    {
        $this->isNull = true;
        return $this;
    }

    /**
     * @return SugarQuery_Builder_Condition
     */
    public function notNull()
    {
        $this->notNull = true;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

}