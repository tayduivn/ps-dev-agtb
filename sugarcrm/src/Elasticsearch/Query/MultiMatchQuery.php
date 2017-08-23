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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query;

use Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\HighlighterInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use BeanFactory;
use Exception;
use SugarACL;
use ACLField;
use User;

/**
 *
 * MultiMatch query builder
 *
 */
class MultiMatchQuery implements QueryInterface
{
    /**
     * Check if the query has any read owner fields
     * @var boolean
     */
    protected $hasReadOwnerFields;

    /**
     * Current user object
     * @var User
     */
    protected $user;

    /**
     * the search terms
     * @var string
     */
    protected $terms;

    /**
     * the search fields
     * @var array
     */
    protected $searchFields;

    /**
     * the search highlight
     * @var HighlighterInterface
     */
    protected $highlighter;

    /**
     * default operator for space in elastic search
     * @var string
     */
    protected $defaultOperator;

    /**
     * Visibility Provider
     * @var Visibility
     */
    protected $visibility;

    /**
     * Set visibility provider
     * @param Visibility $visibility
     */
    public function setVisibilityProvider(Visibility $visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * set default search logic operator for space
     * @param string $operator
     * @return string|false
     */
    public function setOperator($operator)
    {
        $this->defaultOperator = TermParserHelper::getOperator($operator);
    }

    /**
     * Set the search terms.
     * @param string $terms the search terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

    /**
     * Set the search fields.
     * @param array $searchFields
     */
    public function setSearchFields(array $searchFields)
    {
        $this->searchFields = $searchFields;
    }

    /**
     * Set the user.
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Set the highlighter interface in order to normalize the field name
     * @param HighlighterInterface $highlighter
     */
    public function setHighlighter(HighlighterInterface $highlighter)
    {
        $this->highlighter = $highlighter;
    }

    /**
     * Create a multi-match query.
     * @return \Elastica\Query\BoolQuery
     * @throws QueryBuilderException
     */
    public function build()
    {
        try {
            $parser = new SimpleTermParser();
            $parser->setDefaultOperator($this->defaultOperator);

            $terms = $parser->parse($this->terms);
            $query = $this->buildBoolQuery($terms);
            //for a single word
            if (is_string($query)) {
                $query = $this->buildMultiMatchQuery($query);
            }
            return $query;
        } catch (Exception $ex) {
            throw new QueryBuilderException("exception in building query: " . $ex->getMessage());
        }
    }

    /**
     * Build the bool query, based on the parsed boolean expression.
     * @param array|string $terms the boolean expression from the parser.
     * @return mixed
     */
    protected function buildBoolQuery($terms)
    {
        if (is_string($terms)) {
            return $terms;
        }

        $result = array();
        foreach ($terms as $operator => $operands) {
            if (TermParserHelper::isAndOperator($operator) || TermParserHelper::isOrOperator($operator)) {
                $returnExpr = $this->buildBoolQuery($operands);

                $boolQuery = new \Elastica\Query\BoolQuery();
                foreach ($returnExpr as $expr) {
                    //convert a single string to a multi-match query
                    if (is_string($expr)) {
                        $expr = $this->buildMultiMatchQuery($expr);
                    }
                    if (TermParserHelper::isAndOperator($operator)) {
                        $boolQuery->addMust($expr);
                    } else {
                        $boolQuery->addShould($expr);
                    }
                }
                return $boolQuery;
            } elseif (TermParserHelper::isNotOperator($operator)) {
                $boolQuery = new \Elastica\Query\BoolQuery();
                foreach ($operands as $operand) {
                    $query = $this->buildMultiMatchQuery($operand);
                    $boolQuery->addMustNot($query);
                }
                return $boolQuery;
            } else {
                $expr = $this->buildBoolQuery($operands);
                array_push($result, $expr);
            }
        }
        return $result;
    }

    /**
     * Create a multi-match query.
     * @param string $terms
     * @return \Elastica\Query\BoolQuery
     */
    protected function buildMultiMatchQuery($terms)
    {
        $query = new \Elastica\Query\BoolQuery();
        $this->addReadAccessibleQuery($query, $terms);
        $this->addOwnerReadQuery($query, $terms);
        return $query;
    }

    /**
     * Create a multi-match query.
     * @param $fields array the searchable fields
     * @param $term string the search term
     * @return \Elastica\Query\MultiMatch
     */
    protected function createMultiMatchQuery(array $fields, $terms)
    {
        $query = new \Elastica\Query\MultiMatch();
        $query->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS);
        $query->setQuery($terms);
        $query->setFields($fields);
        $query->setTieBreaker(1.0); // TODO make configurable
        return $query;
    }

    /**
     * Add query for all read accessible fields
     * @param \Elastica\Query\BoolQuery $parent Parent query object that this sub-query is added to.
     * @param string $terms
     */
    protected function addReadAccessibleQuery(\Elastica\Query\BoolQuery $parent, $terms)
    {
        $fields = $this->getReadAccessibleSearchFields();
        $query = $this->createMultiMatchQuery($fields, $terms);
        $parent->addShould($query);
    }

    /**
     * Add query for owner read fields
     * @param \Elastica\Query\BoolQuery $parent Parent query object that this sub-query is added to.
     * @param string $terms
     */
    protected function addOwnerReadQuery(\Elastica\Query\BoolQuery $parent, $terms)
    {
        if ($fields = $this->getReadOwnerSearchFields()) {
            $query = $this->createMultiMatchQuery($fields, $terms);
            $filteredQuery = new \Elastica\Query\BoolQuery();
            $filteredQuery->addMust($query);
            $filteredQuery->addFilter($this->createOwnerFilter());
            $parent->addShould($filteredQuery);
        }
    }

    /**
     * Create owner filter
     * @return \Elastica\Query\Terms
     */
    protected function createOwnerFilter()
    {
        return $this->visibility->createFilter('Owner', ['user' => $this->user]);
    }

    /**
     * Get list of readable fields based on selected search fields.
     * @return array
     */
    protected function getReadAccessibleSearchFields()
    {
        return array_values(array_filter($this->searchFields, function ($field) {
            list($module, $field) = $this->processFieldName($field);
            return $this->isFieldReadAccessible($module, $field);
        }));
    }

    /**
     * Get list of "owner read" fields based on selected search fields.
     * @return array
     */
    protected function getReadOwnerSearchFields()
    {
        return array_values(array_filter($this->searchFields, function ($field) {
            list($module, $field) = $this->processFieldName($field);
            return $this->isFieldReadOwner($module, $field);
        }));
    }

    /**
     * Check if we have at least read access to a given module/field
     * @param string $module Module name
     * @param string $field Field name
     * @return bool
     */
    protected function isFieldReadAccessible($module, $field)
    {
        // Any "owner read" field is expected to have ACL_NO_ACCESS and should not be included here
        return $this->getFieldAccess($module, $field) !== SugarACL::ACL_NO_ACCESS ? true : false;
    }

    /**
     * Check if we have owner read access to a given module/field
     * @param string $module Module name
     * @param string $field Field name
     * @return bool
     */
    protected function isFieldReadOwner($module, $field)
    {
        $object = BeanFactory::getObjectName($module);
        $aclFields = ACLField::loadUserFields($module, $object, $this->user->id);
        if (isset($aclFields[$field]) && $aclFields[$field] === ACL_OWNER_READ_WRITE) {
            return true;
        }
        return false;
    }

    /**
     * Get the module name and the field name.
     *
     * Notes on input fields' formats:
     * 1) Normal case:
     * Example: Contacts__first_name.gs_string_wildcard^0.9
     *
     * 2) Exception case I: Email field
     * Contacts__email_search.primary.gs_email^1.95
     * Contacts__email_search.primary.gs_email_wildcard^0.88
     * Contacts__email_search.secondary.gs_email^1.46
     * Contacts__email_search.secondary.gs_email_wildcard^0.49
     *
     * 3) Exception case II: Field without boost value
     * Contacts__last_name.gs_string_wildcard
     *
     * @param string $field the combined search field name
     * @return array
     */
    protected function processFieldName($field)
    {
        $moduleName = "";
        $fieldName = $field;

        $value = explode('.', $field);
        // Field separate '.' is found
        if (is_array($value)) {
            $value = $value[0];
        }

        $names = explode(Mapping::PREFIX_SEP, $value);
        // Mapping::PREFIX_SEP is found
        if (is_array($names) && count($names)>1) {
            $moduleName = $names[0];
            $fieldName = $this->normalizeFieldName($value);
        }

        return array($moduleName, $fieldName);
    }

    /**
     * Normalize the field name.
     *
     * 1) Normal case:
     * Input: Contacts__first_name
     * Output: first_name
     *
     * 2) Email case:
     * Input: Contacts__email_search
     * Output: email
     *
     * @param string $fieldName the field name
     * @return string
     */
    protected function normalizeFieldName($fieldName)
    {
        if (!empty($this->highlighter)) {
            return $this->highlighter->normalizeFieldName($fieldName);
        }
        return $fieldName;
    }

    /**
     * Get the access level of a given module's field
     * @param $module string the module name
     * @param $field string the field name
     * @return int
     */
    protected function getFieldAccess($module, $field)
    {
        return SugarACL::getFieldAccess($module, $field, ['user' => $this->user]);
    }
}
