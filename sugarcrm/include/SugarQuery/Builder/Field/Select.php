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

    public function expandField()
    {
        $this->checkCustomField();

        if (isset($this->def['type']) && $this->def['type'] == 'function') {
            $this->markNonDb();
            return;
        }

        if (empty($this->alias) && !empty($this->def['name'])) {
            $this->alias = $this->def['name'];
        }
        
        if (!empty($this->alias)) {
            $newAlias = $GLOBALS['db']->getValidDBName($this->alias, false, 'alias');
            if (strtolower($this->alias) != $newAlias) {
                throw new SugarQueryException("Alias is more than the max allowed length for an alias");
            }
        }

        if ($this->field == '*') {
            // remove *
            $this->moduleName = empty($this->moduleName) ? $this->query->getFromBean()->module_name : $this->moduleName;
            $bean = BeanFactory::getBean($this->moduleName);
            foreach ($bean->field_defs AS $field => $def) {
                if (!isset($def['source']) || $def['source'] == 'db' || ($def['source'] == 'custom_fields' && $def['type'] != 'relate')) {
                    $this->addToSelect("{$this->table}.{$field}");
                }
            }
            $this->markNonDb();
            return;
        }

        if ($this->def['type'] == 'fullname') {
            $nameFields = Localization::getObject()->getNameFormatFields($this->moduleName);
            foreach ($nameFields as $partOfName) {
                $alias = !empty($this->alias) ? "{$this->alias}__{$partOfName}" : "{$this->def['name']}__{$partOfName}";
                $this->addToSelect(array(array("{$this->table}.{$partOfName}", $alias)));
            }
            $this->markNonDb();
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
            $this->markNonDb();
        }
        if (isset($this->def['custom_type']) && $this->def['custom_type'] == 'teamset') {
            $this->addToSelect('team_set_id');
        }

        // Exists only checks
        if (!empty($this->def['rname_exists'])) {
            $this->markNonDb();
            $this->addToSelectRaw("IF({$this->jta}.{$this->def['rname']} IS NOT NULL,1,0)",$this->field);
            return;
        }

        if (!empty($this->def['rname']) && !empty($this->jta)) {
            $field = array("{$this->jta}.{$this->def['rname']}", $this->def['name']);
            $this->addToSelect(array($field));
            $this->markNonDb();
        }
        if (!empty($this->def['rname_link']) && !empty($this->jta)) {
            $this->field = $this->def['rname_link'];
            $this->alias = $this->def['name'];
        }
        if (!empty($this->def['source']) && $this->def['source'] == 'custom_fields') {
            $this->table = strstr($this->table, '_cstm') ? $this->table : $this->table . '_cstm';
        }
    }

    public function addToSelect($field)
    {
        if (!is_object($this->query->select)) {
            $this->query->select($field);
        } else {
            $this->query->select->field($field);
        }
        return true;
    }

    public function addToSelectRaw($field, $alias = '')
    {
        $this->query->select->fieldRaw($field, $alias);
    }
}
