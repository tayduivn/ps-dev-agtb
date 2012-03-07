<?php

abstract class SugarVisibility
{
    /**
     * Parent bean
     * @var SugarBean
     */
    protected $bean;
    protected $module_dir;

    /**
     * @param SugarBean $bean
     */
    public function __construct($bean)
    {
        $this->bean = $bean;
        $this->module_dir = $this->bean->module_dir;
    }

    /**
     * Add visibility clauses to the query
     * @param string $query
     */
    abstract public function addVisibilityClause(&$query);
}