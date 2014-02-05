<?php

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
