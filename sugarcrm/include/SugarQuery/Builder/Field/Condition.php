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


/**
 * SugarQuery_Builder_Field_Condition
 * @api
 */

class SugarQuery_Builder_Field_Condition extends SugarQuery_Builder_Field
{

    public function __construct($field, SugarQuery $query)
    {
        parent::__construct($field, $query);
    }


    public function cleanField()
    {
        if(!isset($this->def['source']) || $this->def['source'] == 'db') {
            return;
        }
        if(!empty($this->def['rname']) && !empty($this->def['link'])) {
            $this->table = $this->query->getJoinAlias($this->def['link']);
            $this->field = $this->def['rname'];
        } elseif (!empty($this->def['rname']) && !empty($this->def['table'])) {
            $this->table = $this->query->getJoinAlias($this->def['table']);
            $this->field = $this->def['rname'];
        }  elseif(!empty($this->def['rname_link']) && !empty($this->def['link'])) {
            $this->field = $this->def['rname_link'];
        }
    }

    /**
     * @param $field
     * @param $value
     * @param bool $bean
     * @param bool $operator
     *
     * @return string
     */
    public function quoteValue($value, $operator = false)
    {
        global $db;
        if ($value instanceof SugarQuery_Builder_Literal) {
            return (string)$value;
        }
        if (isset($this->def)) {
            $dbtype = $db->getFieldType($this->def);

            if (empty($value)) {
                return $db->emptyValue($dbtype);
            }

            switch ($dbtype) {
                case 'date':
                case 'datetime':
                case 'time':
                    if ($value == 'NOW()') {
                        return $db->now();
                    }
                    if ($operator == 'STARTS') {
                        $value = $value . '%';
                    }
            }

            if ($db->getTypeClass($dbtype) == 'string') {
                if ($operator == 'STARTS') {
                    $value = $value . '%';
                }
                if ($operator == 'CONTAINS' || $operator == 'DOES NOT CONTAIN') {
                    $value = '%' . $value . '%';
                }
                if ($operator == 'ENDS') {
                    $value = '%' . $value;
                }
            }
            return $db->quoteType($dbtype, $value);
        }
        return $db->quoted($value);
    }
}
