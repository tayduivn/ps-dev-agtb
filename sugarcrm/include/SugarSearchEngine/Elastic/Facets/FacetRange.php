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
 * Range facet
 *
 * (see http://www.elasticsearch.org/guide/reference/api/search/facets/range-facet/)
 */
class FacetRange extends FacetAbstract
{
    /**
     *
     * List of supported range defintion in elastic
     * @var array
     */
    protected $elasticSupportedDefs = array(
        'to' => 'to',
        'from' => 'from',
    );

    /**
     * Facet Range constructor
     */
    public function __construct($options = array())
    {
        $defaultOpts = array(
            'ranges' => array()
        );
        parent::__construct(array_merge($defaultOpts, $options));
    }

    /**
     * @see FacetInterface::getFacet
     */
    public function getFacet($fieldName, \Elastica\Filter\AbstractFilter $mainFilter)
    {
        $mainFilter = $this->prepareMainFilter($mainFilter, $fieldName);
        $facet = new \Elastica\Facet\Range($fieldName);
        $facet->setField($fieldName)
            ->setRanges($this->getRangeDefinitions())
            ->setFilter($mainFilter);
        return $facet;
    }

    /**
     * @see FacetInterface::getFilter
     */
    public function getFilter($fieldName, array $values)
    {
        // combine selected filters in an or clause
        $filter = new \Elastica\Filter\Bool();
        $rangeDefs = $this->getRangeDefinitions();
        foreach ($values as $filterId) {
            if (isset($rangeDefs[$filterId])) {
                $rangeFilter = new \Elastica\Filter\Range();
                $rangeFilter->addField($fieldName, $rangeDefs[$filterId]);
                $filter->addShould($rangeFilter);
            }
        }
        return $filter;
    }

    /**
     *
     * @see FacetInterface::parseData()
     */
    public function parseData($facetId, array $facetDefs, array $facetData)
    {
        if (empty($facetData['ranges'])) {
            return false;
        }
        $list = array();
        foreach ($this->options['ranges'] as $id => $range) {
            // only surface facets with hits as selecting a facet with 0 hits will empty the UI
            if ($facetData['ranges'][$id]['count']) {
                $list[] = array(
                    'filter_id' => $id,
                    'label' => translate($range['label']),
                    'count' => $facetData['ranges'][$id]['count'],
                );
            }
        }
        return $this->addBaseFields($facetId, $facetDefs, array('list' => $list));
    }

    /**
     *
     * Filter elastic range definition from options. We need to do this for example
     * to get rid of additional vardefs like the label
     * @return array
     */
    protected function getRangeDefinitions()
    {
        $ranges = array();
        foreach ($this->options['ranges'] as $range) {
            $ranges[] = array_intersect_key($range, $this->elasticSupportedDefs);
        }
        return $ranges;
    }

    /**
     *
     * Prepare main filter
     * @param \Elastica\Filter\Bool $mainFilter
     * @param string $field
     * @see FacetAbstract::getMainFilters
     */
    protected function prepareMainFilter(\Elastica\Filter\Bool $mainFilter, $field)
    {
        // dont mangle the reference
        $mainFilter = clone($mainFilter);

        $filters = $this->getMainFilters($mainFilter);
        foreach ($filters as $key => $filter) {
            if (!empty($filter['bool']['should'][0]['range'][$field])) {
                $this->log->debug("Removing filter for facet $field -> ".var_export($filter, true));
                unset($filters[$key]);
            }
        }

        return $this->setMainFilters($mainFilter, $filters);
    }
}
