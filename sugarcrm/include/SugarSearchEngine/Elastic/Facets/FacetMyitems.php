<?php

require_once 'include/SugarSearchEngine/Elastic/Facets/FacetFilter.php';

/**
 *
 * Basic Sugar "my_items" facet ("assigned to me" docs)
 */
class FacetMyitems extends FacetFilter
{
    /**
     * @var string
     */
    protected $userId;

    /**
     *
     * Ctor
     */
    public function __construct($options = array())
    {
        $this->userId = $GLOBALS['current_user']->id;
        $defaultOpts = array();
        parent::__construct(array_merge($defaultOpts, $options));
    }

    /**
     *
     * MyItems filter
     * @see FacetFilter
     */
    protected function getBoolFilter()
    {
        $docOwner = new \Elastica\Filter\Term(array('doc_owner' => $this->userId));
        $filter = new \Elastica\Filter\Bool();
        $filter->addShould($docOwner);
        return $filter;
    }
}
