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

/**
 *
 * Adapter class for \Elastica\Result
 *
 */
class Result
{
    /**
     * @var \Elastica\Result
     */
    private $result;

    /**
     * Ctor
     * @param \Elastica\Result $result
     */
    public function __construct(\Elastica\Result $result)
    {
        $this->result = $result;
    }

    /**
     * Overload \Elastica\Result
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        return call_user_func_array(array($this->result, $method), $args);
    }

    /**
     * Override highlights
     * @return array
     */
    public function getHighlights()
    {
        $cleaned = array();
        $highlights = $this->result->getHighlights();
        foreach ($highlights as $field => $value) {
            $cleaned[$this->normalizeFieldName($field)] = $value;
        }
        return $cleaned;
    }

    /**
     * Normalize field name, removes multi field notation
     * @param string $field
     * @return string
     */
    protected function normalizeFieldName($field)
    {
        return array_shift(explode('.', $field));
    }
}
