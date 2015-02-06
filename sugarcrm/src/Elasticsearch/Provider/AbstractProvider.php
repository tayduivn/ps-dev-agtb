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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\InvalidMappingException;

/**
 *
 * Base abstract provider
 *
 */
abstract class AbstractProvider implements ProviderInterface
{
    const DEFAULT_SUGAR_TYPE = '_default_';

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * User context
     * @var \User
     */
    protected $user;

    /**
     * List of sugar field types mapped to mappingDefs. This list needs to be
     * populated in the implementing Provider class to be able to use
     * `$this->getMappingForSugarType`.
     *
     * @var array
     */
    protected $sugarTypes = array();

    /**
     * Mapping definitions as defined by `$this->sugarTypes`
     * @var array
     */
    protected $mappingDefs = array();

    /**
     * Ctor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->user = $GLOBALS['current_user'];
    }

    /**
     * Get service container
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set user context
     * @param \User $user
     */
    public function setUser(\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user context
     * @return \User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Build mapping
     * @param Mapping $mapping
     */
    final public function buildMapping(Mapping $mapping)
    {
        $this->buildProviderMapping($mapping);
    }

    /**
     * Build analysis settings
     * @param AnalysisBuilder $analysisBuilder
     */
    final public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $this->buildProviderAnalysis($analysisBuilder);
    }

    /**
     * Return vardefs for given module
     * @param string $module Module name
     * @return array
     */
    protected function getVardefs($module)
    {
        return $this->container->metaDataHelper->getModuleVardefs($module);
    }

    /**
     * Get fts field defs
     * @param string $module
     * @return array
     */
    protected function getFtsFields($module)
    {
        return $this->container->metaDataHelper->getFtsFields($module);
    }

    /**
     * Get module list for user
     * @return array
     */
    protected function getUserModules()
    {
        return $this->container->metaDataHelper->getAvailableModulesForUser($this->user);
    }

    /**
     * Add property list to mapping
     * @param string $field
     * @param Mappging $mapping
     * @param array $properties
     */
    protected function addProperties($field, Mapping $mapping, array $properties)
    {
        foreach ($properties as $name => $fieldMapping) {
            $mapping->addProperty($field, $name, $fieldMapping);
        }
    }

    /**
     * Add mapping properties based on the passed in array contain field names
     * and sugar type association.
     * @param Mapping $mapping
     * @param array $fields
     */
    protected function buildMappingFromSugarType(Mapping $mapping, array $fields)
    {
        foreach ($fields as $field => $type) {
            if ($properties = $this->getMappingForSugarType($type)) {
                $this->addProperties($field, $mapping, $properties);
            }
        }
    }

    /**
     * Get mapping properties for given sugar field type
     * @param string $sugarType
     * @return array
     * @throws \Sugarcrm\Sugarcrm\Elasticsearch\Exception\InvalidMappingException
     */
    public function getMappingForSugarType($sugarType)
    {
        $properties = array();
        foreach ($this->getMappingDefsForSugarType($sugarType) as $mappingDef) {
            if (!isset($this->mappingDefs[$mappingDef])) {
                throw new InvalidMappingException("Unknown mapping def '{$mappingDef}'");
            }
            $properties[$mappingDef] = $this->mappingDefs[$mappingDef];
        }
        return $properties;
    }

    /**
     * Get mapping definitions for given sugar field type
     * @param string $sugarType
     * @return array
     */
    public function getMappingDefsForSugarType($sugarType)
    {
        // resolve sugar type with fallback to default definition if set
        if (!isset($this->sugarTypes[$sugarType])) {
            if (!isset($this->sugarTypes[self::DEFAULT_SUGAR_TYPE])) {
                return array();
            } else {
                $sugarType = self::DEFAULT_SUGAR_TYPE;
            }
        }

        $mappingDefs = $this->sugarTypes[$sugarType];

        // one or multiple mappings can be defined
        if (!is_array($mappingDefs)) {
            $mappingDefs = array($mappingDefs);
        }

        return $mappingDefs;
    }

    /**
     * Verify if given sugar type is supported
     * @param string $type Sugar type
     * @return boolean
     */
    public function isSupportedSugarType($type)
    {
        return isset($this->sugarTypes[$type]);
    }
}
