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

use  Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;

/**
 *
 * Aggregation Handler is the main controller in the aggregation framework.
 *
 */
class AggregationHandler
{
    /**
     * @var GlobalSearch provider
     */
    protected $provider;

    /**
     * the list of aggregations
     * @var array
     */
    protected $aggs;

    /**
     * the list of aggregation filters
     * @var array
     */
    protected $aggFilters;

    /**
     * Constructor
     * @param GlobalSearch $provider
     */
    public function __construct(GlobalSearch $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Compose the filters for every aggregation.
     */
    public function buildAggFilters()
    {
        $inputFilters = $this->provider->getAggFilters();

        //Expect the format of $inputFilters according to GlobalSearchApi::parseAggFilters()
        //array("agg1" => array("bucket_1a", "bucket_1c"), "agg2" => array("bucket_2b", "bucket_2c", bucket_2d"), ...)

        //Assumption: "agg1", "agg2", "agg3" are the aggregation names
        //Cross module aggregations: is the same as the field name, such as "assigned_user_id", "date_modified"
        //Per module aggregation: is a concatenation of module name and field name, such as "accounts.industry"

        $this->aggFilters = array();
        $aggDefs = $this->getCrossModuleAggregations();

        //convert $inputFilters to $aggFilters
        foreach ($inputFilters as $field => $values) {

            $def = array();
            //special handling for module aggregation filtering
            if ($field == "_type") {
                $def = array('type' => 'terms', 'options' => array(), 'cross_module' => true);
            } else {
                // check that we have an aggregation def available
                $def = $this->getAggDef($field, $aggDefs);
                if (empty($def)) {
                    continue;
                }
            }

            $agg = AggregationFactory::get($def['type']);
            if (isset($agg)) {
                // set options from aggregation definition
                $agg->setOptions($def['options']);

                // get Elastica filter object and add it to the module filter
                if ($eFilter = $agg->buildFilter($field, $values)) {
                    $this->aggFilters[$field] = $eFilter;
                }
            }
        }
    }

    /**
     * Get the aggregation defintion for the given field
     * @param string $field the name of the field
     * @param array $aggDefs the definitions of the cross module facets
     * @return array
     */
    protected function getAggDef($field, array $aggDefs)
    {
        //check if the field is cross_module
        if (!empty($aggDefs[$field])) {
            return $aggDefs[$field];
        }

        //check if the field is module based
        //Expect the field in the format of "module_name.field_name"
        $names = explode(".", $field);
        if (sizeof($names) == 2) {
            $moduleName = $names[0];
            $moduleAggDefs = $this->getModuleAggregations($moduleName);
            return $moduleAggDefs[$field];
        }
        return array();
    }

    /**
     * Compose the aggregations.
     * @param array $moduleList : the list of enabled module
     */
    public function buildAggregations(array $moduleList = array())
    {
        $this->aggs = array();
        $aggDefs = $this->getAllAggDefs($moduleList);
        foreach ($aggDefs as $field => $def) {
            if ($agg = AggregationFactory::get($def['type'])) {
                // set options from aggregation definition
                $agg->setOptions($def['options']);

                //compose the filter for the aggregation
                $filter = $this->composeFiltersForAgg($field);

                // get Elastica aggregation object
                $eAgg = $agg->buildAgg($field, $filter);
                if (isset($eAgg)) {
                    $this->aggs[$field] = $eAgg;
                }
            }
        }
    }

    /**
     * Get the aggregation definitions of both cross_modules and selected modules
     * @param array $moduleList : the list of enabled module
     * @return array
     */
    protected function getAllAggDefs(array $moduleList)
    {
        $aggDefs = $this->getCrossModuleAggregations();
        foreach ($moduleList as $module) {
            $aggDefs = array_merge($aggDefs, $this->getModuleAggregations($module));
        }
        return $aggDefs;
    }

    /**
     * Combine all the filters from other aggregations
     * @param string $aggFieldName : the name of the field
     * @return \Elastica\Filter\Bool
     */
    public function composeFiltersForAgg($aggFieldName)
    {
        $comFilter = new \Elastica\Filter\Bool();
        foreach ($this->aggFilters as $field => $filter) {
            //exclude its own filter
            if ($aggFieldName != $field) {
                $comFilter->addMust($filter);
            }
        }
        return $comFilter;

    }

    /**
     * Retrieve the list of aggregation filters.
     * @return array
     */
    public function getAggFilters()
    {
        return $this->aggFilters;
    }

    /**
     * Retrieve the list of aggregations.
     * @return array
     */
    public function getAggs()
    {
        return $this->aggs;
    }

    /**
     * Retrieve the cross module aggregation defs from MetaDataHelper
     * @return array
     */
    public function getCrossModuleAggregations()
    {
        return $this->provider->getContainer()->metaDataHelper->getCrossModuleAggregations();
    }

    /**
     * Retrieve the per module aggregation defs from MetaDataHelper
     * @return array
     */
    public function getModuleAggregations($module)
    {
        return $this->provider->getContainer()->metaDataHelper->getModuleAggregations($module);
    }
}
