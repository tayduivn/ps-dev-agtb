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

namespace Sugarcrm\Sugarcrm\Trigger\Repair;

require_once 'include/TimeDate.php';

/**
 * Iterator into beans of future events, for the re-creation reminders for each event.
 *
 * Class BeanIterator
 * @package Sugarcrm\Sugarcrm\Trigger\Repair
 */
class BeanIterator implements \Iterator
{
    /**
     * Traversable buffer.
     *
     * @var \SugarBean[]
     */
    public $buffer = array();

    /**
     * Module name of beans will be traversable.
     *
     * @var string
     */
    protected $module;

    /**
     * The size of the maximum number of beans filled with buffer.
     *
     * @var int
     */
    protected $chunkSize;

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
     * @param int $chunkSize the size of the maximum number of beans filled with buffer.
     */
    public function __construct($module, $chunkSize = 100)
    {
        $this->module = $module;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->buffer[$this->key];
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
        if (!isset($this->buffer[$this->key])) {
            $this->fillBuffer();
        }

        return isset($this->buffer[$this->key]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->key = 0;
        $this->buffer = array();
    }

    /**
     * Fill traversable buffer.
     *
     * @throws \SugarQueryException
     */
    protected function fillBuffer()
    {
        $beanForList = \BeanFactory::getBean($this->module);

        $query = $this->getSugarQuery();
        $query->from($beanForList, array('team_security' => false));
        $query->limit($this->chunkSize);
        $query->offset($this->key);

        $query->where()->gt('date_start', $this->getTimeDate()->nowDb(), $beanForList);

        $beans = $beanForList->fetchFromQuery($query);
        $this->buffer = array();
        /** @var \SugarBean $bean */
        foreach (array_values($beans) as $key => $bean) {
            $bean->load_relationship('users');
            $bean->users_arr = $bean->users->get();
            $this->buffer[$key + $this->key] = $bean;
        }
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

    /**
     * Return TimeDate instance.
     *
     * @return \TimeDate
     */
    protected function getTimeDate()
    {
        return \TimeDate::getInstance();
    }
}
