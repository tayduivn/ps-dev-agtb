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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation;

/**
 *
 * Abstract aggregation builder
 *
 */
abstract class AbstractAggregation implements AggregationInterface
{
    /**
     *
     * Options passed in from AggregationHandler
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
     * the id of the user
     * @var string
     */
    protected $userId;

    /**
     *
     * Ctor
     * @param array $defaultOpts
     */
    public function __construct($defaultOpts = array())
    {
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
     * Set the user id
     * @param string $userId
     */
    final public function setUser($userId)
    {
        $this->userId = $userId;
    }
}
