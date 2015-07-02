<?php
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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query;

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
     * A flag indicates if it is for the sub-query of read owner fields.
     * @var boolean
     */
    protected $isReadOwnerQuery;

    /**
     * the id of the current user
     * @var string
     */
    protected $userId;

    /**
     * the search terms
     * @var string
     */
    protected $terms;

    /**
     * the search terms
     * @var array
     */
    protected $searchFields;

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
     * @param \User $user
     */
    public function setUser(\User $user)
    {
        $this->userId = $user->id;
    }

    /**
     * Create a multi-match query.
     * @return \Elastica\Query\Bool
     */
    public function build()
    {
        $boolQuery = new \Elastica\Query\Bool();

        //create the sub-query with read-acessible fields
        $this->isReadOwnerQuery = false;
        $this->createReadAccSubQuery($boolQuery);

        //create the sub-query with owner-read-only fields
        $this->isReadOwnerQuery = true;
        $this->createOwnerReadSubQuery($boolQuery);

        return $boolQuery;
    }

    /**
     * Create a multi-match query.
     * @param $fields array the searchable fields
     * @param $term string the search term
     * @return \Elastica\Query\MultiMatch
     */
    protected function createMultiMatchQuery(array $fields)
    {
        $query = new \Elastica\Query\MultiMatch();
        $query->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS);
        $query->setQuery($this->terms);
        $query->setFields($fields);
        $query->setTieBreaker(1.0); // TODO make configurable
        return $query;
    }

    /**
     * Create the sub-query for read-accessible fields.
     * @param $parentQuery object the parent query (i.e. bool query) that this sub-query is added to.
     */
    protected function createReadAccSubQuery($parentQuery)
    {
        $fields = $this->filterSearchFields();
        $query = $this->createMultiMatchQuery($fields);
        $parentQuery->addShould($query);
    }

    /**
     * Create the sub-query for owner read fields.
     * @param $parentQuery object the parent query (i.e. bool query) that this sub-query is added to.
     */
    protected function createOwnerReadSubQuery($parentQuery)
    {
        $this->hasReadOwnerFields = false;
        $fields = $this->filterSearchFields();
        //If no owner read fields are found from isFieldReadOwner(), do nothing.
        if ($this->hasReadOwnerFields === false) {
            return;
        }
        $query = $this->createMultiMatchQuery($fields);

        //If owner read fields are found, need to add a filtered query with the owner filter.
        $filteredQuery = new \Elastica\Query\Filtered();
        $filteredQuery->setQuery($query);

        // Add the owner filter to query
        $filteredQuery->setFilter($this->addOwnerFilter());

        $parentQuery->addShould($filteredQuery);
    }

    /**
     * Create the filter for the ownerId.
     * @return \Elastica\Filter\Terms
     */
    protected function addOwnerFilter()
    {
        $filter = new \Elastica\Filter\Terms();
        $filter->setTerms("owner_id", array($this->userId));
        return $filter;
    }

    /**
     * Filter the search fields based on ACL settings.
     * @return array
     */
    protected function filterSearchFields()
    {
        $fields = array();
        foreach ($this->searchFields as $field) {
            $values = explode(QueryBuilder::BOOST_SEP, $field);
            if (count($values) === 2) {
                $values = explode(QueryBuilder::FIELD_SEP, $values[0]);
                if (count($values) === 2) {
                    $isAccess = $this->isFieldAccessible($values);
                    if ($isAccess === true) {
                        $fields[] = $field;
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * Check if a field is accessible.
     * @param array $path Field path
     * @return bool
     */
    public function isFieldAccessible(array $path)
    {
        //Check ACL access
        if (is_array($path) && !empty($path)) {
            $names = explode(QueryBuilder::PREFIX_SEP, $path[0]);
            if (count($names) === 2) {
                $module = $names[0];
                $field = $names[1];
                $accessLevel = $this->getAccessLevel($module, $field);
                $isOwnerRead = $this->isFieldReadOwner($module, $field);

                if ($this->isReadOwnerQuery === true) {
                    // return the owner read fields for the read owner sub-query
                    if ($isOwnerRead === true) {
                        return true;
                    }
                } else {
                    // return the read accessible fields for the read accessible sub-query
                    // the "owner read" field has the access level of ACL_NO_ACCESS and hence no checking needed
                    if ($accessLevel !== \SugarACL::ACL_NO_ACCESS) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get the access level of a given module's field
     * @param $module string the module name
     * @param $field string the field name
     * @return int
     */
    protected function getAccessLevel($module, $field)
    {
        return \SugarACL::getFieldAccess($module, $field);
    }

    /**
     * Check if a field is a owner-read field.
     * @param $module string the name of the owner
     * @param $field string the name of the field
     * @return bool
     */
    protected function isFieldReadOwner($module, $field)
    {
        $object = \BeanFactory::getObjectName($module);
        $aclFields = \ACLField::loadUserFields($module, $object, $this->userId);

        if (isset($aclFields[$field])) {
            if ($aclFields[$field] == ACL_OWNER_READ_WRITE) {
                $this->hasReadOwnerFields = true;
                return true;
            }
        }
        return false;
    }
}
