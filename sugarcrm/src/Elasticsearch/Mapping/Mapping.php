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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Mapping;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\ProviderCollection;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\PropertyInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;

/**
 *
 * Mapping handler for a specific module
 *
 */
class Mapping
{
    /**
     * @var string Module name
     */
    protected $module;

    /**
     * @var array Elasticsearch mapping properties
     */
    protected $properties = array();

    /**
     * Every field which is added to the index will automatically be available
     * in its raw format. Additional provider mapping are added in multi field
     * notation.
     *
     * @var array Default property map
     */
    protected $defaultMapping = array(
        'type' => 'string',
        'index' => 'not_analyzed',
        'include_in_all' => false,
    );

    /**
     * @param string $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Build mapping
     * @param ProviderCollection $providers
     */
    public function buildMapping(ProviderCollection $providers)
    {
        foreach ($providers as $provider) {
            $provider->buildMapping($this);
        }

        // TODO: add visibility
    }

    /**
     * Get module
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Compile mapping properties
     * @return array
     */
    public function compile()
    {
        return $this->properties;
    }

    /**
     * Add property to mapping
     * @param string $field Field name
     * @param string $name Name of the multi field
     * @param array $mapping
     * @throws \Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException
     */
    public function addProperty($field, $name, array $mapping)
    {
        $this->initProperty($field);
        if (!isset($this->properties[$field]['fields'])) {
            $msg = sprintf(
                'Using addProperty on a raw field is not allowed for %s/%s',
                $field,
                $name
            );
            throw new MappingException($msg);
        }
        $this->properties[$field]['fields'][$name] = $mapping;
    }

    /**
     * Add raw property to mapping. Raw fields will not receive any default
     * mapping definition and cannot be altered later on by different
     * providers.
     * @param string $field
     * @param array $mapping
     */
    public function addRawProperty($field, array $mapping)
    {
        $this->properties[$field] = $mapping;
    }

    /**
     * Initialize base property mapping
     * @param string $field
     */
    public function initProperty($field)
    {
        if (!isset($this->properties[$field])) {
            $this->properties[$field] = $this->defaultMapping;
            $this->properties[$field]['fields'] = array();
        }
    }
}
