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
 * SugarQuery_Builder_Field_Select
 * @api
 */

class SugarQuery_Builder_Field_Select extends SugarQuery_Builder_Field
{
    public function __construct($field, SugarQuery $query)
    {
        parent::__construct($field, $query);
    }

    public function cleanField()
    {
        $this->alias = empty($this->alias) ? $this->def['name'] : $this->alias;
        if ($this->field == '*') {
            return;
        }
        if (!isset($this->def['source']) || $this->def['source'] == 'db') {
            return;
        }
        if (!empty($this->def['fields'])) {
            // this is a compound field
            foreach ($this->def['fields'] as $field) {
                $this->addToSelect("{$this->table}.{$field}");
            }
        }
        if ($this->def['type'] == 'parent') {
            $this->query->hasParent($this->field);
            $this->addToSelect('parent_type');
            $this->addToSelect('parent_id');
        }
        if (isset($this->def['custom_type']) && $this->def['custom_type'] == 'teamset') {
            $this->addToSelect('team_set_id');
        }
        // Exists only checks
        if (!empty($this->def['rname_exists'])) {
            $this->addToSelect("IF({$this->jta}.{$this->def['rname']} IS NOT NULL,1,0)");
        }

        if (!empty($this->def['rname']) && !empty($this->jta)) {
            $this->addToSelect(array("{$this->jta}.{$this->def['rname']}", $this->def['name']));
        }
        if (!empty($this->def['rname_link']) && !empty($this->jta)) {
            $this->field = $this->def['rname_link'];
            $this->alias = $this->def['name'];
        }
    }

    public function addToSelect($field)
    {
        if (!is_object($this->query->select)) {
            $this->query->select($field);
            return true;
        }
        $this->query->select->addField($field);
        return true;
    }

}
