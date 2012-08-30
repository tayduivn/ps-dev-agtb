<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMappingHelper.php');

/**
 * Module mapping for Elastica
 */
class SugarSearchEngineElasticMapping
{
    /**
     * @var \SugarSearchEngineElastic
     */
    private $sse;

    public function __construct(SugarSearchEngineElastic $sse)
    {
        $this->sse = $sse;
    }

    /**
     *
     * This function creates the mapping on particular type/module and field.
     * Ths can be used when user changes the field settings (like boost level) in Studio.
     * index must exist before calling this function.
     *
     * @param $module module name
     * @param $fieldDefs field name of the module
     *
     * @return boolean true if mapping successfully created, false otherwise
     */
    public function setFieldMapping($module, $fieldDefs)
    {
        $properties = $this->constructMappingProperties($fieldDefs);

        if (is_array($properties) && count($properties) > 0)
        {
            $index = new Elastica_Index($this->sse->getClient(), $this->sse->getIndexName());
            $type = new Elastica_Type($index, $module);
            $mapping = new Elastica_Type_Mapping($type, $properties);
            $mapping->setProperties($properties);
            try
            {
                $mapping->send();
            }
            catch (Elastica_Exception_Response $e)
            {
                $GLOBALS['log']->error("elastic response exception when creating mapping, message= " . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     *
     * This function returns an array of properties given a field definition array.
     *
     * @param $fieldDefs array of field definitions
     *
     * @return an array of properties
     */
    protected function constructMappingProperties($fieldDefs) {
        $properties = array();

        foreach ($fieldDefs as $name => $fieldDef)
        {
            if (!empty($fieldDef['name']))
            {
                $fieldName = $fieldDef['name'];
            }
            else
            {
                continue;
            }

            if (isset($fieldDef['full_text_search']))
            {
                $tmpArray = array();

                foreach ($fieldDef['full_text_search'] as $sugarName => $val)
                {
                    $mappingName = SugarSearchEngineMappingHelper::getMappingName('Elastic', $sugarName);
                    if (!empty($mappingName))
                    {
                        $tmpArray[$mappingName] = $fieldDef['full_text_search'][$sugarName];
                    }
                }

                // field type is required when setting mapping
                if (empty($tmpArray['type']))
                {
                    $tmpArray = SugarSearchEngineMappingHelper::getTypeFromSugarType('Elastic', $fieldDef);
                }

                $properties[$fieldName] = $tmpArray;
            }
        }
        if (isset($properties['doc_owner']) == false)
        {
            $properties['doc_owner'] = array(
                'boost' => 1,
                'type' => 'string',
                'index' => 'not_analyzed'
            );
        }
        return $properties;
    }

    /**
     *
     * This function creates a full mapping for all modules.
     * index must exist before calling this function.
     *
     */
    public function setFullMapping()
    {
        $allModules = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();

        // if the index already exists, is there a way to create mapping for multiple modules at once?
        // for now, create one mapping for a module at a time
        foreach ($allModules as $name => $module)
        {
            $this->setFieldMapping($name, $module);
        }
    }

    /**
     *
     * This function creates the mapping for particular module/type.
     * index must exist before calling this function.
     *
     * @param $module module name
     *
     * @return boolean true if mapping successfully created, false otherwise
     */
    public function setModuleMapping($module)
    {
        $fieldDefs = $this->sse->retrieveFtsEnabledFieldsPerModule($module);

        return $this->setFieldMapping($module, $fieldDefs);
    }

}
