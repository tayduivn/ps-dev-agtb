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

require_once 'include/SugarQuery/SugarQuery.php';

/**
 * This is the base object for compiling SugarQueries
 * TODO:
 * Move all bean/vardef functionality out of here and into sugarquery
 * This will allow compilers to be strictly object->desired output without
 * using beans or anything.
 *
 * This can be accomplished by expanding Sugar query to almost do a preCompile
 * and check fields and verify prefixes before it even pushes to the compiler
 */
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
    /**
     * Bean templates for used tables
     * @var array
     */
    protected $table_beans = array();

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

        /* order by clauses should be in SELECT, ensure they are there */
        if (!empty($order_by)) {
            $order_fields = array();
            foreach ($order_by as $order) {
                $order_fields[] = $order[0];
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
        if (!empty($this->sugar_query->join)) {
            $join_part = trim($this->compileJoin($this->sugar_query->join));
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

        // DB MANAGER::limitQuerySql
        $limit_part = (!empty($limit)) ? " LIMIT {$limit} " : '';
        $offset_part = (!empty($offset)) ? " OFFSET {$offset} " : '';

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
        if (!empty($limit_part)) {
            $sql .= $limit_part;
        }
        if (!empty($offset_part)) {
            $sql .= $offset_part;
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
        foreach($groupBy AS $field) {
            $return[] = $this->canonicalizeFieldName($field);
        }
        return implode(',', $return);
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
        $return = array();
        foreach ($having as $have) {
            $return[] = $have[0] . $have[1] . (isset($have[2])) ? $have[2] : '';
        }

        return implode(' ', $return);
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
        foreach ($orderBy as $order) {
            list($field, $direction) = $order;
            $field = $this->canonicalizeFieldName($field);
            $defs = $this->getFieldVardef($field);
            if (empty($defs)) {
                $GLOBALS['log']->error(
                    "Could not find definition for field $field, skipping ORDER BY"
                );
                continue;
            } else {
                if (!empty($defs['sort_on'])) {
                    $field = $this->canonicalizeFieldName($defs['sort_on']);
                } elseif (!empty($defs['source']) && $defs['source'] === 'non-db') {
                    $GLOBALS['log']->error(
                        "Could not sort on non-db field $field, skipping ORDER BY"
                    );
                }
            }
            if (strcasecmp($direction, "ASC") !== 0) {
                $direction = "DESC";
            }
            $return[] = "{$field} {$direction}";
        }

        return implode(',', $return);
    }

    /**
     * Get bean that corresponds to this table name
     *
     * @param string $table_name
     *
     * @return SugarBean
     */
    protected function getTableBean($table_name)
    {
        if (!isset($this->table_beans[$table_name])) {
            if (empty($this->sugar_query->join[$table_name])) {
                return null;
            }
            $link_name = $this->sugar_query->join[$table_name]->linkName;
            if (empty($link_name)) {
                $this->table_beans[$table_name] = null;
                return null;
            }
            //BEGIN SUGARCRM flav=pro ONLY
            if ($link_name == 'favorites') {
                // FIXME: special case, should eliminate it
                $module = 'SugarFavorites';
            }
            //END SUGARCRM flav=pro ONLY
            /* TODO Fix this hack so we don't need to have special cases for these modules */
            if ($link_name == 'tracker') {
                $module = 'Trackers';
            }
            if (empty($module)) {
                $this->from_bean->load_relationship($link_name);
                if (!empty($this->from_bean->$link_name)) {
                    $module = $this->from_bean->$link_name->getRelatedModuleName(
                    );
                }
            }
            if (empty($module)) {
                $this->table_beans[$table_name] = null;
                return null;
            }
            $bean = BeanFactory::newBean($module);
            $this->table_beans[$table_name] = $bean;
        }
        return $this->table_beans[$table_name];
    }

    /**
     * Bring field name to canonical form of table_name.field_name
     *
     * @param string $field
     *
     * @return string
     */
    protected function canonicalizeFieldName($field)
    {
        /**
         * We need to figure out if the field is prefixed with an alias.
         * If it is and the alias is not the from beans table,
         * we must load the relationship that the alias is referencing so that
         * we can determine if they are using the correct alias and change it
         * around if necessary
         * An exception must be made for link tables because there could be
         * multiple joins to different link tables and these aliases are
         * taken care of automatically when M2M relationships are joined.
         */
        $bean = $this->from_bean;
        if (strstr($field, '.')) {
            list($table_name, $field) = explode('.', $field);
            if ($table_name != $bean->getTableName()) {
                $bean = $this->getTableBean($table_name);
                if (empty($bean)) {
                    return "{$table_name}.{$field}";
                }
            }
        } else {
            $table_name = $bean->getTableName();
        }

        return "{$table_name}.{$field}";
    }

    /**
     * Get vardef for the field in the query
     *
     * @param string $field
     *
     * @return array|null
     */
    protected function getFieldVardef($field)
    {
        $bean = $this->from_bean;
        if (strstr($field, '.')) {
            list($table_name, $field) = explode('.', $field);
            if ($table_name != $bean->getTableName()) {
                $bean = $this->getTableBean($table_name);
            }
        }

        if (!empty($bean) && !empty($bean->field_defs[$field])) {
            return $bean->field_defs[$field];
        }
        return null;
    }

    /**
     * @param $field
     *
     * @return string
     */
    protected function resolveField($field, $alias = null)
    {
        $bean = $this->from_bean;
        if ($field == '*') {
            // Not really a good idea, but let's support it for now
            return array($field, null);
        }

        /**
         * We need to figure out if the field is prefixed with an alias.
         * If it is and the alias is not the from beans table,
         * we must load the relationship that the alias is referencing so that
         * we can determine if they are using the correct alias
         * and change it around if necessary
         * An exception must be made for link tables because there could be
         * multiple joins to different link tables and these aliases are
         * taken care of automatically when M2M relationships are joined.
         */
        if (strstr($field, '.')) {
            list($table_name, $field) = explode('.', $field);
            if ($table_name != $bean->getTableName()) {
                $bean = $this->getTableBean($table_name);
                if (empty($bean)) {
                    return array("{$table_name}.{$field}", $alias);
                }
            }
            if ($field == "*") {
                // don't do anything with * for now
                return array("{$table_name}.{$field}", null);
            }
        } else {
            $table_name = $bean->getTableName();
        }

        if (!isset($bean->field_defs[$field])) {
            // FIXME: we don't know about if - how it even ended up here?
            return false;
        }
        $data = $bean->field_defs[$field];

        if (!isset($data['source']) || $data['source'] == 'db') {
            return array("{$table_name}.{$field}", $alias);
        }

        if (isset($data['source']) && $data['source'] == 'custom') {
            // FIXME: if we're given table.field with custom field, we should use custom alias
            $table_name = $bean->get_custom_table_name();
            return array("{$table_name}.{$field}", $alias);
        }

        if ($data['type'] == 'parent') {
            // special hack to handle parent rels
            $this->sugar_query->hasParent($field);
            return array(
                $this->resolveField('parent_type'),
                $this->resolveField('parent_id')
            );
        }

        if ($data['type'] == 'relate') {
            // this is a link field
            $bean->load_relationship($data['link']);
            if (empty($bean->$data['link'])) {
                // failed to load link - bail out
                return false;
            }
            $this->jtcount++;
            $params = array(
                'joinType' => 'LEFT',
                'alias' => 'jt' . $this->jtcount
            );
            if (isset($data['join_name'])) {
                $params['alias'] = $data['join_name'];
            }

            $join = $this->sugar_query->join($data['link'], $params);
            $jalias = $join->joinName();
            $fields = $this->resolveField("$jalias.{$data['rname']}", $field);
            if (!empty($fields)) {
                if (!is_array($fields[0])) {
                    $fields = array($fields);
                }
            }
            if (!empty($data['id_name']) && $data['id_name'] != $field && !in_array(
                $data['id_name'],
                $this->sugar_query->select->select
            )
            ) {
                $id_field = $this->resolveField(
                    $data['id_name'],
                    $data['id_name']
                );
                if (!empty($id_field)) {
                    $fields[] = $id_field;
                }
            }
            if (isset($data['custom_type']) && $data['custom_type'] == 'teamset') {
                $fields[] = $this->resolveField('team_set_id', 'team_set_id');
            }
            return $fields;
        }

        if (!empty($data['fields'])) {
            // this is a compound field
            $sub_fields = array();
            foreach ($data['fields'] as $field) {
                $sub_fields[] = array(
                    "{$table_name}.{$field}",
                    !empty($alias) ? "{$alias}__{$field}" : $field
                );
            }
            if (!empty($data['id_name'])) {
                $sub_fields[] = array(
                    "{$table_name}.id",
                    !empty($alias) ? "{$alias}__{$data['id_name']}" : $data['id_name']
                );
            }

            return $sub_fields;
        }

        return false;
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
        foreach ($selectObj->select as $field) {
            $alias = null;
            $s_alias = '';
            if (is_array($field)) {
                list($field, $alias) = $field;
                $s_alias = " AS {$alias}";
            }

            if ($field instanceof SugarQuery) {
                $return[] = '(' . $field->compileSql() . ')' . $s_alias;
            } else {
                $resolvedFields = $this->resolveField($field, $alias);
                if (empty($resolvedFields)) {
                    // FIXME: can be dangerous to put $field here
                    $return[] = "NULL $s_alias /* $field */";
                    continue;
                }
                if (!is_array($resolvedFields[0])) {
                    $resolvedFields = array($resolvedFields);
                }
                foreach ($resolvedFields as $resolvedField) {
                    if (empty($resolvedField)) {
                        continue;
                    }
                    $alias = $resolvedField[1];
                    if (empty($alias)) {
                        $s_alias = "";
                    } else {
                        $s_alias = " AS $alias";
                    }
                    $return[] = $resolvedField[0] . $s_alias;
                }
            }
        }

        return implode(", ", $return);

    }

    /**
     * Create a from statement
     *
     * @param SugarBean|array $bean
     *
     * @return string
     */
    protected function compileFrom($bean)
    {
        $return = array();
        $alias = false;
        if (is_array($bean)) {
            list($bean, $alias) = $bean;
            $this->from_alias = $alias;
        }
        $this->from_bean = $bean;
        $table = $bean->getTableName();
        $table_cstm = '';
        $from_clause = "{$table}";

        if (isset($alias)) {
            $from_clause .= " {$alias}";
        }

        if ($bean->hasCustomFields()) {
            $table_cstm = $bean->get_custom_table_name();
            if (!empty($table_cstm)) {
                // TODO: CLEAN THIS UP
                if (isset($alias)) {
                    $sql = "LEFT JOIN {$table_cstm} {$alias}_c ON {$alias}_c.id_c = {$alias}.id";
                } else {
                    $sql = "LEFT JOIN {$table_cstm} ON {$table_cstm}.id_c = {$table}.id";
                }
                // can do a join here because we haven't got to the joins yet in the compile sequence.
                $this->sugar_query->joinRaw($sql);
            }
        }

        if (!empty($this->from_alias)) {
            $this->primary_table = $this->from_alias;
            $this->primary_custom_table = $this->from_alias . '_c';
        } else {
            $this->primary_table = $this->from_bean->getTableName();
            $this->primary_custom_table = $this->from_bean->get_custom_table_name(
            );
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
                $sql .= $whereObj->raw;
                continue;
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
     * @param string $sql
     * @param string $operator
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
        $field = $this->canonicalizeFieldName($condition->field);

        if ($condition->isNull) {
            $sql .= "{$field} IS NULL";
        } elseif ($condition->notNull) {
            $sql .= "{$field} IS NOT NULL";
        } else {
            switch ($condition->operator) {
                case 'IN':
                    $valArray = array();
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} IN (" . $condition->values->compileSql(
                        ) . ")";
                    } else {
                        foreach ($condition->values as $val) {
                            $valArray[] = $this->quoteValue(
                                $condition->field,
                                $val,
                                $condition->bean
                            );
                        }
                        $sql .= "{$field} IN (" . implode(',', $valArray) . ")";
                    }
                    break;
                case 'BETWEEN':
                    $value['min'] = $this->quoteValue(
                        $condition->field,
                        $condition->values['min'],
                        $condition->bean
                    );
                    $value['max'] = $this->quoteValue(
                        $condition->field,
                        $condition->values['max'],
                        $condition->bean
                    );
                    $sql .= "{$field} BETWEEN {$value['min']} AND {$value['max']}";
                    break;
                case 'STARTS':
                case 'CONTAINS':
                case 'ENDS':
                    $value = $this->quoteValue(
                        $condition->field,
                        $condition->values,
                        $condition->bean,
                        $condition->operator
                    );
                    $sql .= "{$field} LIKE {$value}";
                    break;
                case 'EQUALFIELD':
                    $sql .= "{$field} = {$condition->values}";
                    break;
                case 'NOTEQUALFIELD':
                    $sql .= "{$field} != {$condition->values}";
                    break;
                case '=':
                case '!=':
                case '>':
                case '<':
                case '>=':
                case '<=':
                default:
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= "{$field} {$condition->operator} (" . $condition->values->compileSql(
                        ) . ")";
                    } else {
                        $value = $this->quoteValue(
                            $condition->field,
                            $condition->values,
                            $condition->bean
                        );
                        $sql .= "{$field} {$condition->operator} {$value}";
                    }
                    break;
            }
        }
        return $sql;
    }

    /**
     * @param $field
     * @param $value
     * @param bool $bean
     * @param bool $operator
     *
     * @return string
     */
    protected function quoteValue(
        $field,
        $value,
        $bean = false,
        $operator = false
    ) {
        if ($value instanceof SugarQuery_Builder_Literal) {
            return (string) $value;
        }

        if ($bean === false) {
            $bean = $this->from_bean;
        }

        /**
         * We need to check the field to determine if it is coming from the from bean or a link.
         * If it is coming from a link we need to load the bean on the other side of the relationship
         * so that we get the type of the field we are trying to quote.
         */
        if (stristr($field, '.')) {
            list($table, $field) = explode('.', $field);
            if ($table != $bean->getTableName()) {
                $bean = $this->getTableBean($table);
                if (empty($bean)) {
                    // quote the value by default
                    return $this->db->quoted($value);
                }
            }
        }


        if (isset($bean->field_defs[$field])) {
            $dbtype = $this->db->getFieldType($bean->field_defs[$field]);

            if (empty($value)) {
                return $this->db->emptyValue($dbtype);
            }

            switch ($dbtype) {
                case 'date':
                case 'datetime':
                case 'time':
                    if ($value == 'NOW()') {
                        return $this->db->now();
                    }
            }

            if ($this->db->getTypeClass($dbtype) == 'string') {
                if ($operator == 'STARTS') {
                    $value = $value . '%';
                }
                if ($operator == 'CONTAINS') {
                    $value = '%' . $value . '%';
                }
                if ($operator == 'ENDS') {
                    $value = '%' . $value;
                }
            }
            return $this->db->quoteType($dbtype, $value);
        }
        return $this->db->quoted($value);
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
        foreach ($join as $j) {
            if (!empty($j->raw)) {
                $return[] = $j->raw;
                continue;
            }
            if (isset($j->options['joinType'])) {
                $sql = strtoupper($j->options['joinType']) . ' JOIN';
            } else {
                $sql = 'JOIN';
            }

            $table = $j->table;

            if ($table instanceof SugarQuery) {
                $table = "(" . $table->compileSql() . ")";
            }
            // Quote the table name that is being joined
            $sql .= ' ' . $table;

            if (isset($j->options['alias']) && strtolower(
                $j->options['alias']
            ) != strtolower($table)
            ) {
                $sql .= ' ' . $j->options['alias'];
            }

            $sql .= ' ON ';
            $sql .= '(' . $this->compileWhere($j->on) . ')';

            $return[] = $sql;
        }

        return implode("\n ", $return);
    }
}
