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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property;

/**
 *
 * Raw properties are possible but are very exceptional. Use this object
 * with caution when needed. Mostly other higher level mapping objects are
 * more appropriate to use.
 *
 */
class RawProperty implements PropertyInterface
{
    /**
     * @var array Mapping definition
     */
    protected $mapping = array();

    /**
     * {@inheritdoc}
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Set mapping
     * @param array $mapping
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }
}
