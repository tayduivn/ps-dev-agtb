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
 * SugarQuery_Builder_Field_Orderby
 * @api
 */

class SugarQuery_Builder_Field_Orderby extends SugarQuery_Builder_Field
{

    public $direction = 'DESC';

    public function __construct($field, SugarQuery $query, $direction = null)
    {
        $this->direction = $direction;
        parent::__construct($field, $query);
    }
    
    public function expandField()
    {
        if (!empty($this->def['sort_on'])) {
            $this->def['sort_on'] = !is_array($this->def['sort_on']) ? array($this->def['sort_on']) : $this->def['sort_on'];
        }

        if (!empty($this->def['rname']) && !empty($this->def['link'])) {
            $jta = $this->query->getJoinAlias($this->def['link']);
            if (empty($jta)) {
                $this->def['link'] = $jta = $this->table;
            }

            $fieldsToOrder = empty($this->def['sort_on']) ? array($this->def['rname']) : $this->def['sort_on'];
            foreach ($fieldsToOrder as $fieldToOrder) {
                // Some sort_on fields are already prefixed with a table name, like
                // in the case of team_name. This cleans that up.
                $field = $this->getTrueFieldNameFromField($fieldToOrder);
                $this->query->orderBy("{$jta}.{$field}", $this->direction);
                if (!$this->query->select->checkField($field, $this->table)) {
                    $this->query->select->addField("{$jta}.{$field}", array('alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->def['link']}__{$field}", false, 'alias')));
                }
            }

            $this->markNonDb();
        } elseif (!empty($this->def['rname']) && !empty($this->def['table'])) {
            $jta = $this->query->getJoinAlias($this->def['table']);
            if (empty($jta)) {
                $jta = $this->table;
            }

            $fieldsToOrder = empty($this->def['sort_on']) ? array($this->def['rname']) : $this->def['sort_on'];
            foreach ($fieldsToOrder as $fieldToOrder) {
                $field = $this->getTrueFieldNameFromField($fieldToOrder);
                $this->query->orderBy("{$jta}.{$field}", $this->direction);
                if (!$this->query->select->checkField($field, $this->table)) {
                    $this->query->select->addField("{$jta}.{$field}", array('alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->def['table']}__{$field}", false, 'alias')));
                }
            }

            $this->markNonDb();
        } elseif (!empty($this->def['rname_link'])) {
            $this->query->orderBy("{$this->table}.{$this->def['rname_link']}", $this->direction);
            if (!$this->query->select->checkField($this->def['rname_link'], $this->table)) {
                $this->query->select->addField("{$this->table}.{$this->def['rname_link']}", array('alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->def['rname_link']}", false, 'alias')));
            }
            $this->markNonDb();
        } else {
            if (!empty($this->def['sort_on'])) {
                $table = $this->table;
                //Custom fields may use standard or custom fields for sort on.
                //Let that SugarQuery_Builder_Field figure out if it's custom or not.
                if (!empty($this->custom) && !empty($this->standardTable)) {
                    $table = $this->standardTable;
                }
                foreach ($this->def['sort_on'] as $field) {
                    $this->query->orderBy("{$table}.{$field}", $this->direction);
                    if (!$this->query->select->checkField($field, $this->table)) {
                        $this->query->select->addField("{$table}.{$field}", array('alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->field}", false, 'alias')));
                    }
                }
            } else {
                if (!$this->query->select->checkField($this->field, $this->table)) {
                    $this->query->select->addField("{$this->table}.{$this->field}", array('alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->field}", false, 'alias')));
                }
            }            
        }

        $this->checkCustomField();
    }
}
