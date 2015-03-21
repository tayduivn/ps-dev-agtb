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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Adapter;

use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\HighlighterInterface;

/**
 *
 * Adapter class for \Elastica\ResultSet
 *
 */
class ResultSet implements \Iterator, \Countable, ResultSetInterface
{
    /**
     * @var \Elastica\ResultSet
     */
    protected $resultSet;

    /**
     * @var HighlighterInterface
     */
    protected $highlighter;

    /**
     * Ctor
     * @param \Elastica\ResultSet $resultSet
     * @param HighlighterInterface $highlighter
     */
    public function __construct(\Elastica\ResultSet $resultSet, HighlighterInterface $highlighter = null)
    {
        $this->resultSet = $resultSet;
        $this->highlighter = $highlighter;
    }

    /**
     * Overload \Elastica\ResultSet
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        return call_user_func_array(array($this->resultSet, $method), $args);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return new Result($this->resultSet->current(), $this->highlighter);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->resultSet->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return $this->resultSet->next();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return $this->resultSet->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->resultSet->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->resultSet->count();
    }

    /**
     * @return \Elastica\ResultSet
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    //// ResultSetInterface ////

    /**
     * {@inheritdoc}
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryTime()
    {
        return $this->resultSet->getTotalTime();
    }
}
