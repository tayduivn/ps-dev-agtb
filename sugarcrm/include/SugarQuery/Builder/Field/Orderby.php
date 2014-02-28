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
            // this is a compound field
            $this->def['sort_on'] = !is_array($this->def['sort_on']) ? array($this->def['sort_on']) : $this->def['sort_on'];
            foreach ($this->def['sort_on'] as $field) {
                $table = $this->table;
                //Custom fields may use standard or custom fields for sort on.
                //Let that SugarQuery_Builder_Field figure out if it's custom or not.
                if (!empty($this->custom) && !empty($this->standardTable)) {
                    $table = $this->standardTable;
                }
                $this->query->orderBy("{$table}.{$field}", $this->direction);
                $this->query->select->addField("{$table}.{$field}", array('alias'=>"{$table}__{$field}"));                
            }
            $this->markNonDb();
        }
        if (!empty($this->def['rname']) && !empty($this->def['table'])) {
            $jta = $this->query->getJoinAlias($this->def['table']);
            $fieldToOrder = $this->def['rname'];
            $this->query->orderBy("{$jta}.{$fieldToOrder}", $this->direction);
            if (!empty($jta)) {
                $this->query->orderBy("{$jta}.{$fieldToOrder}", $this->direction);
                $this->query->select->addField("{$jta}.{$fieldToOrder}", array('alias'=>"{$this->def['table']}__{$fieldToOrder}"));
            }
            $this->markNonDb();
        } elseif(!empty($this->def['rname']) && !empty($this->def['link'])) {
            $jta = $this->query->getJoinAlias($this->def['link']);
            $fieldToOrder = $this->def['rname'];
            if (!empty($jta)) {
                $this->query->orderBy("{$jta}.{$fieldToOrder}", $this->direction);
                $this->query->select->addField("{$jta}.{$fieldToOrder}", array('alias' => "{$this->def['link']}__{$fieldToOrder}"));
            }
            $this->markNonDb();
        } else {
            $this->query->select->addField("{$this->table}.{$this->field}", array('alias' => "{$this->table}__{$this->field}"));
        }
        $this->checkCustomField();
    }

}
