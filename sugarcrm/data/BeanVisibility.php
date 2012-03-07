<?php

/**
 * Bean visibility manager
 * @api
 */
class BeanVisibility
{
    /**
     * List of strategies to apply to this bean
     * @var array
     */
    protected $strategies = array();
    /**
     * Parent bean
     * @var SugarBean
     */
    protected $bean;

    /**
     * @param array $metadata
     * @param SugarBean $bean
     */
    public function __construct($bean, $metadata)
    {
        $this->bean = $bean;
        foreach($metadata as $visclass => $data) {
            $this->strategies[] = new $visclass($bean, $data);
        }
    }

    /**
     * Add the strategy to the list
     * @param string $strategy Strategy class name
     * @param mixed $data Strategy params
     */
    public function addStrategy($strategy, $data = null)
    {
        $this->strategies[] = new $strategy($this->bean, $data);
    }

    /**
     * Add visibility clauses to the query
     * @param string $query
     * @return string Modified query
     */
    public function addVisibilityClause(&$query)
    {
        foreach($this->strategies as $strategy) {
            $strategy->addVisibilityClause($query);
        }
        return $query;
    }
}
