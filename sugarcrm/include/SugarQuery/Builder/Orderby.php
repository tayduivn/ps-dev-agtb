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

class SugarQuery_Builder_Orderby
{
    public $column;
    public $direction = 'DESC';
    public $query;

    public function __construct($query, $direction = 'DESC')
    {
        $this->query = $query;
        $this->direction = $direction;
    }

    public function addField($column, $options = array())
    {
        $this->column = new SugarQuery_Builder_Field_Orderby($column, $this->query, $this->direction);
        return $this;
    }

    public function addRaw($expression) {
        $this->column = new SugarQuery_Builder_Field_Raw($expression, $this->query);
    }
}
