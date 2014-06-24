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
 * Base class for visibility implementations
 * @api
 */
abstract class SugarVisibility
{
    /**
     * Parent bean
     * @var SugarBean
     */
    protected $bean;
    protected $module_dir;

    /**
     * Options for this run
     * @var array|null
     */
    protected $options;

    /**
     * @param SugarBean $bean
     */
    public function __construct($bean)
    {
        $this->bean = $bean;
        $this->module_dir = $this->bean->module_dir;
    }

    /**
     * Add visibility clauses to the FROM part of the query
     * @param string $query
     * @return string
     */
    public function addVisibilityFrom(&$query)
    {
        return $query;
    }

    /**
     * Add visibility clauses to the WHERE part of the query
     * @param string $query
     * @return string
     */
    public function addVisibilityWhere(&$query)
    {
        return $query;
    }

   /**
     * Add visibility clauses to the FROM part of the query
     * @param string $query
     * @return string
     */
    public function addVisibilityFromQuery(SugarQuery $query)
    {
        return $query;
    }

    /**
     * Add visibility clauses to the WHERE part of the query
     * @param string $query
     * @return string
     */
    public function addVisibilityWhereQuery(SugarQuery $query)
    {
        return $query;
    }


    /**
     * Get visibility options
     * @param string $name
     * @param mixed $default Default value if option not set
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        return $default;
    }

    /**
     * Set visibility options
     * @param array $options
     * @return SugarVisibility
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /** Override to implement visibility related attribute updates before the bean is indexed
     * @param string $engine search engine name
     * @return array
     * Called before the bean is indexed so that any calculated attributes can updated.
     * Since the team security id is updated directly, there is no need to implement anything custom
     */
    public function beforeSseIndexing()
    {
    }

    public function addSseVisibilityFilter($engine, $filter)
    {
    	return $filter;
    }

}
