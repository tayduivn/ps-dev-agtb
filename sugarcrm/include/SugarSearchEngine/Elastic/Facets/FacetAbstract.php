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

require_once 'include/SugarSearchEngine/Elastic/Facets/FacetInterface.php';

/**
 *
 * Abstract base class for facet implements
 *
 */
abstract class FacetAbstract implements FacetInterface
{
    /**
     *
     * Options passed in from FacetHandler
     * @var array
     */
    protected $options;

    /**
     *
     * Default options as defined by the implement class
     * @var array
     */
    protected $defaultOpts;

    /**
     *
     * Logger instance 
     * @var SugarLogger  
     */
    protected $log;

    /**
     *
     * Ctor
     * @param array $defaultOpts
     */
    public function __construct($defaultOpts = array())
    {
        $this->log = LoggerManager::getLogger();
        $this->defaultOpts = $defaultOpts;
        $this->options     = $defaultOpts;
    }

    /**
     *
     * Set options to be consumed
     * @param array $options
     * @return array
     */
    final public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            if (isset($this->defaultOpts[$key])) {
                $this->options[$key] = $value;
            }
        }
        return $this->options;
    }

    /**
     *
     * Add base fields to parsed facet results
     * @param string $id
     * @param array $defs
     * @param array $result
     * @return array
     */
    protected function addBaseFields($id, $defs, $result)
    {
        $base =  array(
            'id' => $id,
            'label' => $defs['label'],
            'facet_type' => $defs['type'],
            'ui_type' => $defs['ui_type'],
        );
        return array_merge($base, $result);
    }

    /**
     *
     * Facet results are based on the items returned by the query
     * only and are NOT restricted by the filter added on top of the
     * actual query. Therefor we need to apply the main filter too
     * on top of every facet to use the same result base to calculate
     * the facet hits.
     *
     * However if facet filter x is selected in the UI, this restriction
     * will already be added to the main filter itself as the user
     * specifically requested to filter the results.
     *
     * The result will be that for every facet filter being set in
     * the main query, we will get a limited list back on those
     * particular facets - being the selected one's in the UI. However
     * we still want to get all available facet hits and not only
     * those which are selected.
     *
     * Therefor we need to remove the filter from the main filter
     * for a given field when applied on a fact. Because Elastica
     * library is not able to return set objects (they are on the
     * fly parsed into an array) we need to crawl the (boolean)
     * main filter to search if a filter is set for a given field
     * and remove it. It would be easier and cleaner if we were
     * able to retrieve the added filter objects on top of the
     * main filter but this seems not to be supported by Elastica.
     *
     * The following methods are available to facilitate this:
     * functionality in the facet implementation classes:
     * $this->getMainFilters() - return current filters
     * $this->setMainFilters() - override current filters
     *
     * The logic needs resides in the facet implementation class
     * as it depends on which type of facet we are talking about.
     *
     * The third one ($this->getReflectionFilters()) is used to be
     * able to access the protected _should property on the actual
     * main filter object as Elastica does not allow us to overwrite,
     * only add new filter. To avoid reconstructing the full main
     * filter again, we just access this protected property by
     * removing what we don't need.
     *
     */

    /**
     *
     * Return the list of filters from the main filter object
     * @param \Elastica\Filter\Bool $mainFilter
     * @return array
     */
    protected function getMainFilters(\Elastica\Filter\Bool $mainFilter)
    {
        $refProp = $this->getReflectionFilters($mainFilter);
        $filters = $refProp->getValue($mainFilter);

        // structure of the array to search is based on current implementation
        // array ( 0 => array ( 'bool' => array ( 'must' => array ( stuff_to_mangle ) )
        if (empty($filters[0]['bool']['must']) || !is_array($filters[0]['bool']['must'])) {
            return array();
        }

        return $filters[0]['bool']['must'];
    }

    /**
     *
     * Remove a filter item from the main filter
     * @param \Elastica\Filter\Bool $mainFilter
     * @param array $filters
     */
    protected function setMainFilters(\Elastica\Filter\Bool $mainFilter, $filters)
    {
        // encapsulate filters into original structure
        $filters = array(
            'bool' => array(
                'must' => array_values($filters),
            ),
        );
        $refProp = $this->getReflectionFilters($mainFilter);
        $refProp->setValue($mainFilter, $filters);
        return $mainFilter;
    }

    /**
     *
     * Return reflection object for protected filters
     * @param \Elastica\Filter\Bool $mainFilter
     * @return ReflectionProperty
     */
    protected function getReflectionFilters(\Elastica\Filter\Bool $mainFilter)
    {
        $reflection = new ReflectionProperty($mainFilter, '_should');
        $reflection->setAccessible(true);
        return $reflection;
    }
}
