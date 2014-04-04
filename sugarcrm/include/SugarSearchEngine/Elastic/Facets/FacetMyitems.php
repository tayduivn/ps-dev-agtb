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
