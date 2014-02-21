<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
*/

/**
 * Class SugarQuery_Builder_Union.
 */
class SugarQuery_Builder_Union
{
    /**
     * @var SugarQuery
     */
    protected $query;

    /**
     * Array of union queries.
     * @var array
     */
    protected $queries = array();

    /**
     * Create Union Object.
     * @param SugarQuery $query
     */
    public function __construct(SugarQuery $query)
    {
        $this->query = $query;
    }

    /**
     * Add new query for union.
     * @param SugarQuery $query Query object to add.
     * @param bool $all (optional) Indicates should 'UNION ALL' be used or not. Default is `true`.
     */
    public function addQuery(SugarQuery $query, $all = true)
    {
        $this->queries[] = array('query' => $query, 'all' => (boolean) $all);
    }

    /**
     * Return queries for union.
     * @return array Set of query objects.
     */
    public function getQueries()
    {
        return $this->queries;
    }
}
