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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Rebuild;

/**
 * Iterator into beans of meetings and calls, for the re-export to external application.
 *
 * Class BeanIterator
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Rebuild
 */
class BeanIterator implements \Iterator
{
    /**
     * @var array
     */
    protected $ids = array();

    /**
     * Module name of beans will be traversable.
     *
     * @var string
     */
    protected $module;

    /**
     * Current position in traversable.
     *
     * @var int
     */
    protected $key = 0;

    /**
     * Create new instance of BeanIterator, set module name with beans will be traversable, and bean buffer size.
     *
     * @param string $module module name with beans will be traversable.
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return \BeanFactory::getBean($this->module, $this->ids[$this->key]);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->key ++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->module . ':' . $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        if (!isset($this->ids[$this->key]) && count($this->ids) == 0) {
            $this->fillIds();
        }

        return isset($this->ids[$this->key]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->key = 0;
        $this->ids = array();
    }

    /**
     * Fill ids buffer.
     *
     * @throws \SugarQueryException
     */
    protected function fillIds()
    {
        $beanForList = \BeanFactory::getBean($this->module);
        $query = $this->getSugarQuery();
        $query->from($beanForList);
        $query->select(array('id'));
        $query->where()->isEmpty('repeat_parent_id');
        $rows = $query->execute();
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }
        $this->ids = $ids;
    }

    /**
     * Return SugarQuery instance.
     *
     * @return \SugarQuery sugar query instance.
     */
    protected function getSugarQuery()
    {
        return new \SugarQuery();
    }
}
