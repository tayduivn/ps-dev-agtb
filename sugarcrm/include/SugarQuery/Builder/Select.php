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

class SugarQuery_Builder_Select
{

    /**
     * Array of Select fields/statements
     * @var array
     */
    public $select = array();

    protected $query;

    protected $countQuery = false;

    /**
     * Create Select Object
     * @param $columns
     */
    public function __construct(SugarQuery $query, $columns)
    {
        if (!is_array($columns)) {
            $columns = array_slice(func_get_args(), 1);
        }
        $this->query = $query;
        $this->field($columns);
    }

    /**
     * Select method
     * Add select elements
     * @param string $columns
     * @return object this
     */
    public function field($columns)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        foreach ($columns as $column) {
            $field = new SugarQuery_Builder_Field_Select($column, $this->query);
            $key = empty($field->alias) ? $field->field : $field->alias;
            if(!$field->isNonDb()) {
                $this->select[$key] = $field;
            }
        }
        return $this;
    }


    public function addField($column, $options = array())
    {
        $this->field($column);
    }

    /**
     * SelectReset method
     * clear out the objects select array
     * @return object this
     */
    public function selectReset()
    {
        $this->select = array();
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public function setCountQuery()
    {
        $this->countQuery = true;
        return $this;
    }

    public function getCountQuery()
    {
        return $this->countQuery;
    }
}
