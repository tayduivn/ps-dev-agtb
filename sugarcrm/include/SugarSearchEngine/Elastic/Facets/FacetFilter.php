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

require_once 'include/SugarSearchEngine/Elastic/Facets/FacetAbstract.php';

/**
 *
 * Abstract Facet Filter implementation class for custom filter types
 * based on filter facets. Do not confuse the usage of this filter facet
 * implementation with facet filters !
 *
 * (see http://www.elasticsearch.org/guide/reference/api/search/facets/filter-facet/)
 */
abstract class FacetFilter extends FacetAbstract
{
    /**
     *
     * Ctor
     */
    public function __construct($options = array())
    {
        $defaultOpts = array();
        parent::__construct(array_merge($defaultOpts, $options));
    }

    /**
     *
     * @see FacetInterface::getFacet
     */
    public function getFacet($fieldName, \Elastica\Filter\AbstractFilter $mainFilter)
    {
        // encapsulate mainFilter and the facet filter
        $master = new \Elastica\Filter\Bool();
        $master->addMust($mainFilter);
        $master->addMust($this->getBoolFilter());

        // attach master filter to facet
        $facet = new \Elastica\Facet\Filter($fieldName);
        $facet->setFilter($master);
        return $facet;
    }

    /**
     *
     * @see FacetInterface::getFilter
     */
    public function getFilter($fieldName, array $values)
    {
        return $this->getBoolFilter();
    }

    /**
     *
     * @see FacetInterface::parseData()
     */
    public function parseData($facetId, array $facetDefs, array $facetData)
    {
        if (isset($facetData['count'])) {
            $parsed = array(
                'count' => $facetData['count'],
            );
            return $this->addBaseFields($facetId, $facetDefs, $parsed);
        }
        return false;
    }

    /**
     * To be defined by extension class
     * @return \Elastica\Filter\Bool
     */
    abstract protected function getBoolFilter();
}
