<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * This is the base object for building SugarQueries
 *
 */

require_once 'include/SugarQuery/Compiler.php';
require_once 'include/SugarQuery/Builder/Where.php';
require_once 'include/SugarQuery/Builder/Andwhere.php';
require_once 'include/SugarQuery/Builder/Orwhere.php';
require_once 'include/SugarQuery/Builder/Join.php';
require_once 'include/SugarQuery/Builder/Select.php';
require_once 'include/SugarQuery/Builder/Condition.php';
require_once 'include/SugarQuery/Builder/Literal.php';

class SugarQuery
{

    /**
     * This is the Select Object
     * @var null|SugarQuery_Builder_Select
     */
    public $select = null;

    /**
     * @var null|array
     */
    public $group_by = null;

    /**
     * @var null|array
     */
    public $having = null;

    /**
     * @var null|array
     */
    public $order_by = null;

    /**
     * @var null|integer
     */
    public $limit = null;

    /**
     * @var null|integer
     */
    public $offset = null;

    /**
     * @var null|array(SugarQuery)
     */
    public $union = null;

    /**
     * @var bool
     */
    public $distinct = false;

    /**
     * @var null|SugarBean
     */
    public $from = null;

    /**
     * @var array(SugarQuery_Builder_Where)
     */
    public $where = array();

    /**
     * @var array(SugarQuery_Builder_Join)
     */
    public $join = array();

    protected $joined_tables = array();
    /**
     * @var DBManager
     */
    protected $db;

    /**
     * Stores joins corresponding to links
     * @var array
     */
    protected $links = array();

    /**
     * Stores parent field for this query
     * @var array
     */
    protected $has_parent;

    /**
     * Build the select object
     *
     * @param bool $fields
     *
     * @return null|SugarQuery_Builder_Select
     */
    public function select($fields = false)
    {
        if (empty($this->select)) {
            $this->select = new SugarQuery_Builder_Select($this, $fields);
        }
        return $this->select;
    }


    /**
     * Set the from bean
     *
     * @param SugarBean $bean
     * @param bool $alias
     *
     * @return SugarQuery
     */
    public function from(SugarBean $bean, $options = array())
    {
        $alias = (isset($options['alias'])) ? $options['alias'] : false;
        $team_security = (isset($options['team_security'])) ? $options['team_security'] : true;
        $add_deleted = (isset($options['add_deleted'])) ? $options['add_deleted'] : true;
        $this->from = $bean;
        if (!empty($alias)) {
            $this->from = array($bean, $alias);
        }

        if ($team_security === true) {
            $bean->addVisibilityQuery($this);
        }

        if ($add_deleted === true) {
            $this->where()->equals('deleted', 0);
        }

        return $this;
    }

    /**
     * Add an AND Where Object to this query
     *
     * @param array $conditions
     *
     * @return SugarQuery_Builder_Where
     */
    public function where($conditions = array())
    {
        if (!isset($this->where['and'])) {
            $this->where['and'] = new SugarQuery_Builder_Andwhere($conditions);
        }
        if (!empty($conditions)) {
            $this->where['and']->add($conditions);
        }
        return $this->where['and'];
    }

    /**
     * Build a raw where statement
     *
     * @param $sql
     *
     * @return SugarQuery_Builder_Andwhere
     */
    public function whereRaw($sql)
    {
        $where = new SugarQuery_Builder_Andwhere();
        $where->addRaw($sql);
        $this->where['and']->add($where);
        return $this->where['and'];
    }


    /**
     * Add an Or Where Object to this query
     *
     * @param array $conditions
     *
     * @return SugarQuery_Builder_Orwhere
     */
    public function orWhere($conditions = array())
    {
        if (!isset($this->where['or'])) {
            $this->where['or'] = new SugarQuery_Builder_Orwhere($conditions);
        }

        return $this->where['or'];
    }


    /**
     * Add a traditional query builder join object to this query
     *
     * @param string $table
     * @param array $options
     *
     * @return SugarQuery_Builder_Join
     */
    public function joinTable($table, $options = array())
    {

        $join = new SugarQuery_Builder_Join($table, $options);
        if (isset($options['alias'])) {
            $key = $options['alias'];
        } else {
            $key = $table;
        }

        $this->join[$key] = $join;
        return $join;
    }

    /**
     * Add a raw [straight SQL] join object to this query
     *
     * @param string $sql
     * @param array $options
     *
     * @return SugarQuery_Builder_Join
     */
    public function joinRaw($sql, $options = array())
    {
        $join = new SugarQuery_Builder_Join();
        $join->addRaw($sql);
        if (isset($options['alias']) && !empty($options['alias'])) {
            $this->join[$options['alias']] = $join;
        } else {
            $this->join[md5($sql)] = $join;
        }


        return $join;
    }

    /**
     * Add a join based on a link with the from bean
     *
     * @param string $link_name
     * @param array $options
     *
     * @return SugarQuery
     */
    public function join($link_name, $options = array())
    {
        if (!isset($options['alias'])) {
            $options['alias'] = $link_name;
        }

        if (!empty($this->links[$link_name])) {
            return $this->links[$link_name];
        }

        if ($link_name == 'favorites') {
            $sfOptions = array('joinType' => 'LEFT');
            $sf = new SugarFavorites();
            $options['alias'] = $sf->addToSugarQuery($this, $sfOptions);
        } else {
            $this->loadBeans($link_name, $options);
        }
        $this->join[$options['alias']]->addLinkName($link_name);
        $this->links[$link_name] = $this->join[$options['alias']];
        return $this->join[$options['alias']];
    }

    /**
     * Compile this SugarQuery into a standard SQL-92 Query string
     * @return string
     */
    public function compileSql()
    {
        global $db;
        $compiler = new SugarQuery_Compiler();
        return $compiler->compile($this, $db);
    }

    /**
     * Execute this query and return it as a raw string, db object json, or array
     *
     * @param string $type
     *
     * @return array|dbObject|string
     */
    public function execute($type = "array", $encode = true)
    {
        switch ($type) {
            case 'raw':
                return $this->compileSql($this);
                break;
            case 'db':
                return $this->runQuery($this);
                break;
            case 'json':
            case 'array':
            default:
                $results = $this->runQuery($this);
                $return = array();
                while ($row = $this->db->fetchByAssoc($results, $encode)) {
                    $return[] = $row;
                }
                if ($type == 'json') {
                    return json_encode($return);
                }
                return $return;
                break;
        }

    }

    /**
     * Run the query and return the db result object
     * @return db result object
     */
    protected function runQuery()
    {
        $this->db = DBManagerFactory::getInstance();
        return $this->db->query($this->compileSql($this));
    }


    /**
     * This will eventually determine the type of query [select, update, delete, insert] and return the specific type
     * @return string
     */
    public static function getType()
    {
        return 'select';
    }

    /**
     * Set this Query as Distinct
     *
     * @param bool $value
     *
     * @return SugarQuery
     */
    public function distinct($value)
    {
        $this->distinct = (bool) $value;
        return $this;
    }


    /**
     * Set the offset of this query
     *
     * @param int $number
     *
     * @return SugarQuery
     */
    public function offset($number)
    {
        $this->offset = $number;

        return $this;
    }

    /**
     * Add a union query to this query
     *
     * @param SugarQuery $select
     * @param bool $all
     *
     * @return SugarQuery
     */
    public function union(SugarQuery $select, $all = true)
    {

        $this->union [] = array('select' => $select, 'all' => $all);

        return $this;
    }

    /**
     * Add a group by statement to this query
     *
     * @param array $array
     *
     * @return SugarQuery
     */
    public function groupBy($array)
    {
        $this->group_by[] = $array;

        return $this;
    }

    /**
     * Add a having statement to this query
     *
     * @param array $array
     *
     * @return SugarQuery
     */
    public function having($array)
    {
        $this->having[] = array($array);
        return $this;
    }


    /**
     * Add an order by statement for this query
     *
     * @param string $column
     * @param string $direction
     *
     * @return SugarQuery
     */
    public function orderBy($column, $direction = null)
    {
        $this->order_by[] = array($column, $direction);

        return $this;
    }

    /**
     * Set the limit of this query
     *
     * @param int $number
     *
     * @return SugarQuery
     */
    public function limit($number)
    {
        $this->limit = $number;

        return $this;
    }

    /**
     * Load Beans uses Link2 to take a SugarQuery object and add the joins needed to take a link and make the connection
     *
     * @param Linkname $join
     * @param $alias
     */
    protected function loadBeans($join, $options)
    {
        $alias = (!empty($options['alias'])) ? $options['alias'] : $join;
        $joinType = (!empty($options['joinType'])) ? $options['joinType'] : 'INNER';
        $team_security = (!empty($options['team_security'])) ? $options['team_security'] : true;

        $bean = $this->from;
        if (is_array($bean)) {
            list($bean, $alias) = $bean;
        }

        $bean->load_relationship($join);


        $bean->$join->buildJoinSugarQuery(
            $this,
            array(
                'joinTableAlias' => $bean->module_name,
                'myAlias' => $alias,
                'joinType' => $joinType
            )
        );
        $joined = BeanFactory::newBean($bean->$join->getRelatedModuleName());
        if ($team_security === true) {
            $joined->addVisibilityQuery(
                $this,
                array("table_alias" => $alias, 'as_condition' => true)
            );
        }


        if ($joined->hasCustomFields()) {
            $table_cstm = $joined->get_custom_table_name();
            // TODO: CLEAN THIS UP TO USE A JOIN OBJECT, IT WOULD BE NICER
            if (!empty($table_cstm)) {
                $sql = "LEFT JOIN {$table_cstm} ON {$table_cstm}.id_c = {$alias}.id";
                $this->joinRaw($sql);
            }
        }

    }

    public function hasParent($has = null)
    {
        if ($has !== null) {
            $this->has_parent = $has;
        }
        return $this->has_parent;
    }
}
