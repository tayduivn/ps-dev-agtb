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
     * @var boolean the flag to enable or disable adding cross_module fields
     */
    protected $crossModuleEnabled = false;

    /**
     * @var string the name of the field for copy_to.
     */
    protected $copyToFieldName;

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

    /**
     * Set the flag crossModuleEnabled.
     * @param boolean $value TRUE or FALSE
     */
    public function setCrossModuleEnabled($value)
    {
        $this->crossModuleEnabled = $value;
    }

    /**
     * Check the flag crossModuleEnabled.
     * @return boolean
     */
    public function isCrossModuleEnabled()
    {
        return $this->crossModuleEnabled;
    }

    /**
     * Set the field name for copy_to.
     * @param string $fieldName the name of the field for 'copy_to' property
     */
    public function setCopyToFieldName($fieldName)
    {
        $this->copyToFieldName = $fieldName;
    }

    /**
     * Add the copy_to property to the field.
     * @param array $mapping the mapping to be modified
     * @return array
     */
    public function setCopyToProperty(array $mapping)
    {
        if (isset($this->copyToFieldName)) {
            return array_merge(
                $mapping,
                array('copy_to' => $this->copyToFieldName)
            );
        }
        return $mapping;
    }
}
