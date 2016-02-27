<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Doctrine\DBAL\Query\QueryBuilder;

class SugarQuery_Compiler_Doctrine
{
    /**
     * @var DBManager
     */
    protected $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     * Build out the Query in SQL
     *
     * @param SugarQuery $query
     * @return QueryBuilder
     */
    public function compile(SugarQuery $query)
    {
        if ($query->union instanceof SugarQuery_Builder_Union) {
            return $this->compileUnionQuery($query);
        }

        return $this->compileSelectQuery($query);
    }

    /**
     * Build out the Query in SQL
     *
     * @param SugarQuery $query
     * @return QueryBuilder
     * @throws SugarQueryException
     */
    protected function compileUnionQuery(SugarQuery $query)
    {
        $unions = $query->union->getQueries();

        if (count($unions) == 0) {
            throw new SugarQueryException('The UNION query does not contain sub-queries');
        }

        $conn = $this->db->getConnection();
        $builder = $conn->createQueryBuilder();

        $sql = '';
        foreach ($unions as $i => $union) {
            if ($i > 0) {
                $sql .= ' UNION ';
                if ($union['all']) {
                    $sql .= 'ALL ';
                }
            }

            $sql .= '(' . $this->compileSubQuery($builder, $union['query']) . ')';
        }

        $this->compileOrderBy($builder, $query, false);

        // combine manually built SELECT with the ORDER BY built by builder
        $sql = str_replace('SELECT  FROM  ', $sql, $builder->getSQL());

        // manually apply LIMIT to the resulting SQL
        if ($query->limit !== null || $query->offset !== null) {
            $sql = $conn->getDatabasePlatform()->modifyLimitQuery($sql, $query->limit, $query->offset);
        }

        // inject the SQL back to builder
        $re = new ReflectionProperty($builder, 'sql');
        $re->setAccessible(true);
        $re->setValue($builder, $sql);

        $re = new ReflectionProperty($builder, 'state');
        $re->setAccessible(true);
        $re->setValue($builder, QueryBuilder::STATE_CLEAN);

        return $builder;
    }

    /**
     * Build out the Query in SQL
     *
     * @param SugarQuery $query
     *
     * @return QueryBuilder
     */
    protected function compileSelectQuery(SugarQuery $query)
    {
        $builder = $this->db->getConnection()
            ->createQueryBuilder();

        $query->ensureGroupByFields();
        $this->compileSelect($builder, $query);
        $this->compileFrom($builder, $query);
        $this->compileJoins($builder, $query);
        $this->compileWhere($builder, $query);
        $this->compileGroupBy($builder, $query);
        $this->compileHaving($builder, $query);
        $this->compileOrderBy($builder, $query, true);
        $this->compileLimit($builder, $query);

        return $builder;
    }

    /**
     * Create a select statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileSelect(QueryBuilder $builder, SugarQuery $query)
    {
        // if there aren't any selected fields, add them all
        if (empty($query->select->select) && $query->select->getCountQuery() === false) {
            $query->select('*');
        }

        $select = $query->select;

        $columns = array();
        if ($select->getCountQuery()) {
            $columns[] = 'count(0) AS record_count';
        }

        foreach ($select->select as $field) {
            if ($field->isNonDb()) {
                continue;
            }

            $columns[] = $this->compileField($field);
            if ($select->getCountQuery()) {
                $query->groupBy("{$field->table}.{$field->field}");
            }
        }

        if ($query->distinct && count($columns) > 0) {
            $columns[0] = 'DISTINCT ' . $columns[0];
        }

        $builder->select($columns);
    }

    /**
     * Create a from statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileFrom(QueryBuilder $builder, SugarQuery $query)
    {
        $bean = $query->getFromBean();
        if (!$bean) {
            return;
        }

        $alias = $query->getFromAlias();
        $table = $bean->getTableName();
        if ($alias == $table) {
            $alias = null;
        }

        $builder->from($table, $alias);

        // SugarQuery will determine if we actually need to add the table or not.
        $query->joinCustomTable($bean, $alias);
    }

    /**
     * Creates join syntax for the query
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileJoins(QueryBuilder $builder, SugarQuery $query)
    {
        foreach ($this->sortJoins($query->join) as $join) {
            $this->compileJoin($builder, $join);
        }
    }

    /**
     * @param SugarQuery_Builder_Join[] $joins
     * @return SugarQuery_Builder_Join[]
     */
    protected function sortJoins(array $joins)
    {
        $sorted = array();
        foreach ($joins as $name => $join) {
            if (isset($sorted[$name])) {
                continue;
            }

            // If there is a relationship table alias, we need to build the join
            // part before the join alias is referenced or there will be sadness
            // in SQLland
            if (isset($join->relationshipTableAlias)) {
                $alias = $join->relationshipTableAlias;
                if (isset($joins[$alias]) && !isset($sorted[$alias])) {
                    $sorted[$alias] = $joins[$alias];
                }
            }

            $sorted[$name] = $join;
        }

        return $sorted;
    }

    protected function compileJoin(QueryBuilder $builder, SugarQuery_Builder_Join $join)
    {
        if ($join->table instanceof SugarQuery) {
            $table = $this->compileSubQuery($builder, $join->table);
        } else {
            $table = $join->table;
        }

        if ($join->on) {
            $condition = $this->compileExpression($builder, $join->on);
        } else {
            $condition = null;
        }

        $fromAlias = $join->query->getFromAlias();
        $alias = $join->joinName();
        switch (strtolower($join->options['joinType'])) {
            case 'left':
                $builder->leftJoin($fromAlias, $table, $alias, $condition);
                break;
            case 'right':
                $builder->rightJoin($fromAlias, $table, $alias, $condition);
                break;
            default:
                $builder->join($fromAlias, $table, $alias, $condition);
                break;
        }
    }

    /**
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileWhere(QueryBuilder $builder, SugarQuery $query)
    {
        if ($query->where) {
            $builder->where(
                $this->compileExpression($builder, $query->where)
            );
        }
    }

    /**
     * Create a GroupBy statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileGroupBy(QueryBuilder $builder, SugarQuery $query)
    {
        foreach ($query->group_by as $column) {
            if ($column->column->isNonDb()) {
                continue;
            }

            $builder->addGroupBy(
                $this->compileField($column->column)
            );
        }
    }

    /**
     * Create a Having statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileHaving(QueryBuilder $builder, SugarQuery $query)
    {
        if ($query->having) {
            $builder->having(
                $this->compileExpression($builder, $query->having)
            );
        }
    }

    /**
     * Create an Order By Statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     * @param bool $applyOrderStability
     */
    protected function compileOrderBy(QueryBuilder $builder, SugarQuery $query, $applyOrderStability)
    {
        $orderBy = $query->order_by;
        if ($applyOrderStability && !$this->db->supports('order_stability')) {
            $orderBy = $this->applyOrderByStability($query, $orderBy);
        }

        foreach ($orderBy as $column) {
            if ($column->column->isNonDb()) {
                continue;
            }

            $builder->addOrderBy(
                $this->compileField($column->column),
                $column->direction
            );
        }
    }

    /**
     * Add additional column to `ORDER BY` clause for order stability, defaults
     * to using the `id` column.
     *
     * @param SugarQuery $query
     * @param SugarQuery_Builder_Orderby[] $orderBy List of already existing `ORDER BY` defs
     * @return SugarQuery_Builder_Orderby[]
     */
    protected function applyOrderByStability(SugarQuery $query, array $orderBy)
    {
        if (count($orderBy) > 0) {
            $uniqueCol = new SugarQuery_Builder_Orderby($query);
            $uniqueCol->addField('id');
            $orderBy[] = $uniqueCol;
        }

        return $orderBy;
    }

    /**
     * Compile LIMIT Statement
     *
     * @param QueryBuilder $builder
     * @param SugarQuery $query
     */
    protected function compileLimit(QueryBuilder $builder, SugarQuery $query)
    {
        if ($query->select->getCountQuery()) {
            return;
        }

        $builder->setFirstResult($query->offset);
        $builder->setMaxResults($query->limit);
    }

    /**
     * @param $field
     * @return string
     */
    protected function compileField(SugarQuery_Builder_Field $field)
    {
        if ($field instanceof SugarQuery_Builder_Field_Raw) {
            if (!empty($field->alias)) {
                return "{$field->field} {$field->alias}";
            } else {
                return $field->field;
            }
        }

        if ($field->isNonDb()) {
            return '';
        }

        if ($field->table) {
            $sql = $field->table . '.' . $field->field;
        } else {
            $sql = $field->field;
        }

        if (!empty($field->alias)) {
            $sql .= ' ' . $field->alias;
        }

        return  $sql;
    }

    /**
     * Build the Where Statement using arrays, to keep it nice and clean
     *
     * @param QueryBuilder $builder
     * @param SugarQuery_Builder_Where $expression
     *
     * @return array
     */
    protected function compileExpression(QueryBuilder $builder, SugarQuery_Builder_Where $expression)
    {
        $sql = array();

        if (!empty($expression->raw)) {
            $compiledField = $this->compileField($expression->raw);
            if (!empty($compiledField)) {
                $sql[] = $compiledField;
            }
        }

        foreach ($expression->conditions as $condition) {
            if ($condition instanceof SugarQuery_Builder_Where) {
                $compiledField = $this->compileExpression($builder, $condition);
                if (count($compiledField) > 0) {
                    $sql[] = $compiledField;
                }
            } elseif ($condition instanceof SugarQuery_Builder_Condition) {
                $compiledField = $this->compileCondition($builder, $condition);
                if (!empty($compiledField)) {
                    $sql[] = $compiledField;
                }
            } elseif (is_array($condition) && !empty($condition)) {
                $sql[] = join(' ', $condition);
            }
        }

        $method = strtolower($expression->operator()) . 'X';
        return call_user_func_array(array($builder->expr(), $method), $sql);
    }

    protected function compileCondition(QueryBuilder $builder, SugarQuery_Builder_Condition $condition)
    {
        global $current_user;

        $field = $this->compileField($condition->field);

        if (empty($field)) {
            return false;
        }

        if (!empty($condition->field->def['type']) && $this->db->isTextType($condition->field->def['type'])) {
            $castField = $this->db->convert($field, 'text2char');
        } else {
            $castField = $field;
        }

        $expr = $builder->expr();

        if ($condition->isNull) {
            $sql = $expr->isNull($field);
        } elseif ($condition->notNull) {
            $sql = $expr->isNotNull($field);
        } else {
            $fieldDef = $condition->field->def;
            switch ($condition->operator) {
                case 'IN':
                    $sql = $castField . ' IN (' . $this->compileSet($builder, $condition->values, $fieldDef) . ')';
                    break;
                case 'NOT IN':
                    $sql = $field . ' IS NULL OR '
                        . $castField . ' NOT IN (' . $this->compileSet($builder, $condition->values, $fieldDef) . ')';
                    break;
                case 'BETWEEN':
                    $min = $this->bindValue($builder, $condition->values['min'], $fieldDef);
                    $max = $this->bindValue($builder, $condition->values['max'], $fieldDef);
                    $sql = "{$field} BETWEEN {$min} AND {$max}";
                    break;
                case 'STARTS':
                case 'DOES NOT START':
                case 'CONTAINS':
                case 'DOES NOT CONTAIN':
                case 'ENDS':
                case 'DOES NOT END':
                    $sql = $this->compileLike($builder, $field, $condition->operator, $condition->values, $fieldDef);
                    break;
                case 'EQUALFIELD':
                    $sql = "{$castField} = " . $this->compileField(
                        $this->getFieldCondition($condition->values, $condition->query)
                    );
                    break;
                case 'NOTEQUALFIELD':
                    $sql = "{$castField} != " . $this->compileField(
                        $this->getFieldCondition($condition->values, $condition->query)
                    );
                    break;
                default:
                    $sql = $castField . ' ' . $condition->operator . ' ';
                    if ($condition->values instanceof SugarQuery) {
                        $sql .= '(' . $this->compileSubQuery($builder, $condition->values) . ')';
                    } elseif ($condition->field->isFieldCompare()) {
                        $condition->field->field = $condition->field->getFieldCompare();
                        $sql .= $this->compileField($condition->field);
                    } else {
                        $sql .= $this->bindValue($builder, $condition->values, $fieldDef);
                    }
                    break;
            }
        }

        if (!$condition->isAclIgnored()) {
            $isFieldAccessible = ACLField::generateAclCondition($condition, $current_user);
            if ($isFieldAccessible) {
                $sql = '(' . $sql . ' AND (' . $this->compileExpression($builder, $isFieldAccessible) . '))';
            }
        }

        return $sql;
    }

    protected function compileSet($builder, $set, $fieldDef)
    {
        if ($set instanceof SugarQuery) {
            return $this->compileSubQuery($builder, $set);
        }

        if (empty($set)) {
            return 'NULL';
        }

        $values = array();
        foreach ($set as $value) {
            $values[] = $this->bindValue($builder, $value, $fieldDef);
        }

        return implode(',', $values);
    }

    /**
     * Compiles subquery and returns it as SQL
     *
     * @param QueryBuilder $builder Primary query builder
     * @param SugarQuery $subQuery Subquery
     *
     * @return string
     */
    protected function compileSubQuery(QueryBuilder $builder, SugarQuery $subQuery)
    {
        $subBuilder = $this->compile($subQuery);

        $params = $subBuilder->getParameters();
        foreach ($params as $key => $value) {
            $builder->createPositionalParameter(
                $value,
                $subBuilder->getParameterType($key)
            );
        }

        return $subBuilder->getSQL();
    }

    protected function compileLike(QueryBuilder $builder, $field, $operator, $values, array $fieldDef)
    {
        switch ($operator) {
            case 'STARTS':
            case 'DOES NOT START':
                $format = '%s%%';
                break;
            case 'CONTAINS':
            case 'DOES NOT CONTAIN':
                $format = '%%%s%%';
                break;
            case 'ENDS':
            case 'DOES NOT END':
                $format = '%%%s';
                break;
            default:
                $format = null;
                break;
        }

        $isNegation = strpos($operator, 'NOT') !== false;
        if ($isNegation) {
            $comparator = 'NOT LIKE';
            $chainWith = 'AND';
        } else {
            $comparator = 'LIKE';
            $chainWith = 'OR';
        }

        if ($this->db->supports('case_insensitive')) {
            $field = "UPPER($field)";
        }

        $sql = '';
        if ($isNegation) {
            $sql .= $field . ' IS NULL OR ';
        }

        if (is_array($values)) {
            $conditions = array();
            foreach ($values as $value) {
                $conditions[] = $this->compilePattern($builder, $field, $comparator, $format, $value, $fieldDef);
            }
            $sql .= implode(' ' . $chainWith . ' ', $conditions);
        } else {
            $sql .= $this->compilePattern($builder, $field, $comparator, $format, $values, $fieldDef);
        }

        return $sql;
    }

    protected function compilePattern(
        QueryBuilder $builder,
        $field,
        $comparator,
        $format,
        $value,
        array $fieldDef = null
    ) {
        if ($this->db->supports('case_insensitive')) {
            $value = strtoupper($value);
        }

        $escape = '!';
        $pattern = sprintf($format, str_replace(
            array($escape,           '_',           '%'),
            array($escape . $escape, $escape . '_', $escape . '%'),
            $value
        ));

        return $field . ' ' . $comparator . ' ' . $this->bindValue($builder, $pattern, $fieldDef)
            . ' ESCAPE \'' . $escape . '\'';
    }

    /**
     * Binds value to the query and returns the query fragment representing the placeholder
     *
     * @param QueryBuilder $builder Query builder
     * @param mixed $value The value to be bound
     * @param array $fieldDef Field definition
     * @return string
     */
    protected function bindValue(QueryBuilder $builder, $value, array $fieldDef)
    {
        return $this->db->bindValue($builder, $value, $fieldDef);
    }

    /**
     * Method allows us to mock creation of SugarQuery_Builder_Field_Condition
     *
     * @param string $field
     * @param SugarQuery $query
     * @return SugarQuery_Builder_Field_Condition
     */
    protected function getFieldCondition($field, SugarQuery $query)
    {
        return new SugarQuery_Builder_Field_Condition($field, $query);
    }
}
