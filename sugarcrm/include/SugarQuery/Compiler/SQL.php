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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/SugarQuery/SugarQuery.php';

class SugarQuery_Compiler_SQL
{
    /**
     * @var SugarBean
     */
    protected $from_bean;
    /**
     * @var SugarQuery
     */
    protected $sugar_query;
    /**
     * @var null|string
     */
    protected $from_alias = null;
    /**
     * @var string
     */
    protected $primary_table;
    /**
     * @var string
     */
    protected $primary_custom_table;

    /**
     * @var dbManager
     */
    protected $db;

    protected $jtcount = 0;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Build out the Query in SQL
     *
     * @param SugarQuery $sugar_query
     *
     * @return string
     */
    public function compile(SugarQuery $sugar_query)
    {
        $this->sugar_query = $sugar_query;
        if (empty($this->sugar_query->select)) {
            $this->sugar_query->select = new SugarQuery_Builder_Select($this->sugar_query, array('*'));
        }
        return $this->compileSelectQuery();
    }

    /**
     * Convert a Select SugarQuery Object into a string
     *
     * @return string
     */
    protected function compileSelectQuery()
    {
        $select_part = '*';
        $from_part = '';
        $where_part = '';
        $join_part = '';
        $distinct = '';
        $group_by_part = '';
        $order_by_part = '';
        $having_part = '';

        $group_by = $this->sugar_query->group_by;
        $having = $this->sugar_query->having;
        $order_by = $this->sugar_query->order_by;
        $limit = $this->sugar_query->limit;
        $offset = $this->sugar_query->offset;

        $union = $this->sugar_query->union;
        // if there aren't any selected fields, add them all
        if (empty($this->sugar_query->select->select)) {
            $this->sugar_query->select('*');
        }
        /* order by clauses should be in SELECT, ensure they are there */
        if (!empty($order_by)) {
            $order_fields = array();
            foreach ($order_by as $order) {
                $field = $this->compileField($order->column);
                if (!empty($field)) {
                    $order_fields[] = $field;
                }
            }
            if (!empty($order_fields)) {
                $this->sugar_query->select->field($order_fields);
            }
        }

        if (!empty($this->sugar_query->from)) {
            $from_part = trim($this->compileFrom($this->sugar_query->from));
        }

        if (!empty($this->sugar_query->select)) {
            $select_part = trim(
                $this->compileSelect($this->sugar_query->select)
            );
        }
        if (!empty($this->sugar_query->where)) {
            $where_part = trim($this->compileWhere($this->sugar_query->where));
        }

        if ($this->sugar_query->distinct) {
            $distinct = 'DISTINCT';
        }

        if (!empty($group_by)) {
            $group_by_part = $this->compileGroupBy($group_by);
        }

        if (!empty($having)) {
            $having_part = $this->compileHaving($having);
        }

        if (!empty($order_by)) {
            $order_by_part = $this->compileOrderBy($order_by);
        }

        if (!empty($this->sugar_query->join)) {
            $join_part = trim($this->compileJoin($this->sugar_query->join));
        }


        $sql = "SELECT {$distinct} {$select_part} FROM {$from_part}";
        if (!empty($join_part)) {
            $sql .= " {$join_part} ";
        }
        if (!empty($where_part)) {
            $sql .= " WHERE {$where_part} ";
        }
        if (!empty($group_by_part)) {
            $sql .= " GROUP BY {$group_by_part} ";
        }
        if (!empty($having_part)) {
            $sql .= " HAVING {$having_part} ";
        }
        if (!empty($order_by_part)) {
            $sql .= " ORDER BY {$order_by_part} ";
        }
        if (!empty($limit)) {
            $sql = $this->db->limitQuery($sql, $offset, $limit, false, '', false);
        }
        if (!empty($union)) {
            foreach ($union as $u) {
                if (isset($u['select'])) {
                    $sql .= ' UNION ';
                    $sql .= ($u['all']) ? 'ALL ' : '';
                    $sql .= $u['select']->compileSql();
                }
            }
        }
        return trim($sql);
    }

    /**
     * Create a GroupBy statement
     *
     * @param array $groupBy
     *
     * @return string
     */
    protected function compileGroupBy($groupBy)
    {
        $return = array();
        foreach ($groupBy AS $groupBy) {
            $return[] = $this->compileField($groupBy->column);
        }

        return implode(', ', $return);
    }

    /**
     * Create a Having statement
     *
     * @param string $having
     *
     * @return string
     */
    protected function compileHaving($having)
    {
        return $this->compileWhere($having);
    }

    /**
     * Create an Order By Statement
     *
     * @param array $orderBy
     *
     * @return string
     */
    protected function compileOrderBy($orderBy)
    {
        $return = array();
        // order by ID
        $orderId = new SugarQuery_Builder_Orderby($this->sugar_query);
        $orderId->addField('id');
        $orderBy[] = $orderId;

        foreach ($orderBy as $order) {
            if ($order->column->isNonDb() == 1) {
                continue;
            }
            $field = trim($this->compileField($order->column));
            if (empty($field)) {
                continue;
            }

            $direction = trim($order->direction);

            if (!isset($return[$field])) {
                $return[$field] = "{$field} {$direction}";
            }
        }

        return implode(',', $return);
    }


    /**
     * Create a select statement
     *
     * @param SugarQuery_Builder_Select $selectObj
     *
     * @return string
     */
    protected function compileSelect(SugarQuery_Builder_Select $selectObj)
    {
        $return = array();

        if ($selectObj->getCountQuery() === true) {
            return 'count(0) AS record_count';
        }

        foreach ($selectObj->select as $field) {
            if ($field->isNonDb() == 1) {
                continue;
            }
            $compiledField = $this->compileField($field);
            $return[$compiledField] = $compiledField;
        }

        return implode(", ", $return);

    }

    protected function compileField($field)
    {
        if (!is_object($field)) {
            return $field;
        }

        if ($field instanceof SugarQuery_Builder_Field_Raw) {
            return $field->field;
        }

        if ($field->isNonDb() == 1) {
            return '';
        }
        $sql = "{$field->table}.{$field->field}";
        $sql .= !empty($field->alias) ? " {$field->alias}" : "";
        return  $sql;
    }

    /**
     * Create a from statement
     *
     * @param SugarBean|array $bean
     * @return string
     */
    protected function compileFrom($bean)
    {
        $alias = "";
        $return = array();
        if (is_array($bean)) {
            list($bean, $alias) = $bean;
            $this->from_alias = $alias;
        }
        $this->from_bean = $bean;
        $table = $bean->getTableName();
        $table_cstm = '';
        $from_clause = "{$table}";

        if (!empty($alias)) {
            $from_clause .= " {$alias}";
        }

        //SugarQuery will determine if we actually need to add the table or not.
        $this->sugar_query->joinCustomTable($bean, $alias);

        if (!empty($this->from_alias)) {
            $this->primary_table = $this->from_alias;
            $this->primary_custom_table = $this->from_alias . '_c';
        } else {
            $this->primary_table = $this->from_bean->getTableName();
            $this->primary_custom_table = $this->from_bean->get_custom_table_name();
        }

        $return = $from_clause;

        return $return;
    }

    /**
     * Create a where statement
     *
     * @param array $where SugarQuery_Builder_Where
     *
     * @return string
     */
    protected function compileWhere(array $where)
    {
        $sql = false;
        foreach ($where as $whereObj) {
            if ($whereObj instanceof SugarQuery_Builder_Andwhere) {
                $operator = " AND ";
            } else {
                $operator = " OR ";
            }

            if (!empty($whereObj->raw)) {
                $sql .= $this->compileField($whereObj->raw);
            }
            foreach ($whereObj->conditions as $condition) {
                if ($condition instanceof SugarQuery_Builder_Where) {
                    if (!empty($sql) && substr($sql, -1) != '(') {
                        $sql .= $operator;
                    }
                    $sql .= ' (' . $this->compileWhere(array($condition)) . ')';
                    continue;
                } elseif ($condition instanceof SugarQuery_Builder_Condition) {
                    $sql = $this->compileCondition($condition, $sql, $operator);
                } else {
                    if (is_array($condition)) {
                        $sql .= join(' ', $condition);
                    }
                }
            }
        }
        return $sql;
    }


    /**
     * Compile a condition into SQL
     *
     * @param SugarQuery_Builder_Condition $condition
     * @param string $sql Current SQL string
     * @param string $operator Preceding logical operator - AND/OR
     *
     * @return string
     */
    public function compileCondition(
        SugarQuery_Builder_Condition $condition,
        $sql,
        $operator
    ) {
        if (!empty($sql) && substr($sql, -1) != '(') {
            $sql .= $operator;
        }

        $field = $this->compileField($condition->field);

        if (empty($field)) {
            return false;
        }

        if ($condition->isNull) {
            $sql .= "{$field} IS NULL";
        } elseif ($condition->notNull) {
            $sql .= "{$field} IS NOT NULL";
        } else {
            switch ($condition->operator) {
                case 'IN':
                    $valArray = array();
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} IN (" . $condition->values->compileSql() . ")";
                    } else {
                        foreach ($condition->values as $val) {
                            $valArray[] = $condition->field->quoteValue($val, $condition->operator);
                        }
                        $sql .= "{$field} IN (" . implode(',', $valArray) . ")";
                    }
                    break;
                case 'NOT IN':
                    $valArray = array();
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} NOT IN (" . $condition->values->compileSql() . ")";
                    } else {
                        foreach ($condition->values as $val) {
                            $valArray[] = $condition->field->quoteValue($val, $condition->operator);
                        }
                        $sql .= "{$field} NOT IN (" . implode(',', $valArray) . ")";
                    }
                    break;
                case 'BETWEEN':
                    $value['min'] = $condition->field->quoteValue($condition->values['min'], $condition->operator);
                    $value['max'] = $condition->field->quoteValue($condition->values['max'], $condition->operator);
                    $sql .= "{$field} BETWEEN {$value['min']} AND {$value['max']}";
                    break;
                case 'STARTS':
                case 'CONTAINS':
                case 'DOES NOT CONTAIN':
                case 'ENDS':
                    //Handling for not contains
                    $comparitor = 'LIKE';
                    $chainWith = 'OR';
                    if ($condition->operator == 'DOES NOT CONTAIN') {
                        $comparitor = 'NOT LIKE';
                        $chainWith = 'AND';
                    }

                    if (is_array($condition->values)) {
                        foreach ($condition->values as $value) {
                            $val = $condition->field->quoteValue($value, $condition->operator);
                            $sql .= "{$field} {$comparitor} {$val} {$chainWith} ";
                        }
                        $sql .= rtrim($sql, "$chainWith ");
                    } else {
                        $value = $condition->field->quoteValue($condition->values, $condition->operator);
                        $sql .= "{$field} {$comparitor} {$value}";
                    }
                    break;
                case 'EQUALFIELD':
                    $sql .= "{$field} = " . $this->compileField(new SugarQuery_Builder_Field_Condition($condition->values, $this->sugar_query));
                    break;
                case 'NOTEQUALFIELD':
                    $sql .= "{$field} != " . $this->compileField(new SugarQuery_Builder_Field_Condition($condition->values, $this->sugar_query));
                    break;
                case '=':
                case '!=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                default:
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} {$condition->operator} (" . $condition->values->compileSql() . ")";
                    } else {
                        $value = $condition->field->quoteValue($condition->values, $condition->operator);
                        $sql .= "{$field} {$condition->operator} {$value}";
                    }
                    break;
            }
        }
        return $sql;
    }

    /**
     * Creates join syntax for the query
     *
     * @param array $join
     *
     * @return string
     */
    protected function compileJoin(array $join)
    {
        // get the related beans for everything
        $return = array();

        // check if any elements are relationships
        foreach ($join as $name => $j) {
            // Take raw as is and move on
            if (!empty($j->raw)) {
                $return[] = $j->raw;
                $built[$name] = true;
                continue;
            }

            // If this join has already been compiled, skip it. This happens in
            // cases of join table aliases that need to be declared before they
            // are referenced in a query. See BR-1057
            if (isset($built[$name])) {
                continue;
            }

            // If there is a relationship table alias, we need to build the join
            // part before the join alias is referenced or there will be sadness
            // in SQLland
            $buildAlias = isset($j->relationshipTableAlias) && isset($join[$j->relationshipTableAlias]);
            if ($buildAlias && !isset($built[$j->relationshipTableAlias])) {
                $return[] = $this->getJoinSQLString($join[$j->relationshipTableAlias]);
                $built[$j->relationshipTableAlias] = true;
            }

            $return[] = $this->getJoinSQLString($j);
            $built[$name] = true;
        }

        return implode("\n ", $return);
    }

    /**
     * Gets a join SQL string
     * 
     * @param  SugarQuery_Builder_Join $join A join object
     * @return string A join SQL part
     */
    protected function getJoinSQLString(SugarQuery_Builder_Join $join)
    {
        // Raw sql can just be returned
        if (!empty($join->raw)) {
            return $join->raw;
        }

        // Build out the return SQL now
        if (isset($join->options['joinType'])) {
            $sql = strtoupper($join->options['joinType']) . ' JOIN';
        } else {
            $sql = 'JOIN';
        }

        $table = $join->table;

        if ($table instanceof SugarQuery) {
            $table = "(" . $table->compileSql() . ")";
        }
        // Quote the table name that is being joined
        $sql .= ' ' . $table;

        if (isset($join->options['alias']) && strtolower(
                $join->options['alias']
            ) != strtolower($table)
        ) {
            $sql .= ' ' . $join->options['alias'];
        }

        $sql .= ' ON ';
        $sql .= '(' . $this->compileWhere($join->on) . ')';
        
        return $sql;
    }
}
