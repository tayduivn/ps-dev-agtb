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
 *
 * Facet interface
 *
 */
interface FacetInterface
{
    /**
     *
     * Returns an Elastica Facet object
     * @param  string                          $fieldName
     * @param  \Elastica\Filter\AbstractFilter $mainFilter
     * @return \Elastica\Facet\AbstractFacet
     */
    public function getFacet($fieldName, \Elastica\Filter\AbstractFilter $mainFilter);

    /**
     *
     * Returns an Elastica Filter object
     * @param  string $fieldName
     * @param  array  $values
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter($fieldName, array $values);

    /**
     *
     * Parse facet results
     * @param  string $id
     * @param  array  $defs
     * @param  array  $data
     * @return array
     */
    public function parseData($facetId, array $facetDefs, array $facetData);
}
