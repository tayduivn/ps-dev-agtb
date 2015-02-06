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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch;

/**
 *
 * BoostHandler
 *
 */
class BoostHandler
{
    const BOOST_SEP = '^';

    /**
     * Default boost value if non defined
     * @var float
     */
    protected $defaultBoost = 1;

    /**
     * Normalization precision
     * @var integer
     */
    protected $precision = 2;

    /**
     * List of mapping types which are weighted
     * @var array
     */
    protected $weighted = array();

    /**
     * Set weighted list
     * @param array $weighted
     */
    public function setWeighted(array $weighted)
    {
        $this->weighted = $weighted;
    }

    /**
     * Get boosted field definition
     * @param string $field Field name
     * @param array $defs Field vardefs
     * @param string $type Mapping type
     * @return string
     */
    public function getBoostedField($field, array $defs, $type)
    {
        return $field . self::BOOST_SEP . $this->getBoostValue($defs, $type);
    }

    /**
     * Get boost value from defs or use default
     * @param array $defs Field vardefs
     * @param string $type Mapping type
     * @return float
     */
    public function getBoostValue(array $defs, $type)
    {
        if (isset($defs['full_text_search']['boost'])) {
            $boost = (float) $defs['full_text_search']['boost'];
        } else {
            $boost = $this->defaultBoost;
        }
        return $this->normalizeBoost($boost, $type);
    }

    /**
     * Normalize boost value
     * @param float $boost
     * @param string $type Mapping type
     * @return float
     */
    public function normalizeBoost($boost, $type)
    {
        $boost = $this->weight($boost, $type);
        return round($boost, $this->precision);
    }

    /**
     * Weight the boost
     * @param float $boost
     * @param string $type Mapping type
     */
    public function weight($boost, $type)
    {
        if (isset($this->weighted[$type])) {
            $boost = $boost * $this->weighted[$type];
        }
        return $boost;
    }
}
