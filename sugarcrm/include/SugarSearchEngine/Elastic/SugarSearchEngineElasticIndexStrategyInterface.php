<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Interface for strategies to index data into elastic
 */
interface SugarSearchEngineElasticIndexStrategyInterface
{
    /**
     * Returns all indexes used
     *
     * @abstract
     * @param array $modules OPTIONAL module name to get indexes
     * @return array list of indexes
     */
    public function getAllIndexes($moduleName = '');

    /**
     * Returns index objects for all given module names
     *
     * @param \Elastica\Client $client
     * @param array            $moduleNames
     *
     * @return Elastica_Index[]
     */
    public function getReadIndices(\Elastica\Client $client, $moduleNames = array());

    /**
     * Creates an elastic index
     *
     * @abstract
     * @param \Elastica\Client $client
     * @param array $modules OPTIONAL list of modules to create an index for
     * @param boolean $recreate OPTIONAL Deletes index first if already exists (default = false)
     * @param $params
     */
    public function createIndex(
        \Elastica\Client $client,
        $modules,
        SugarSearchEngineElasticMapping $mapper,
        $params = array(),
        $recreate = false
    );

    /**
     * Retrieves the index name for reading
     *
     * @abstract
     * @param $params
     * @return string
     */
    public function getReadIndexName($params = array());

    /**
     * Retrieves the index name for writing
     *
     * @abstract
     * @param $params
     * @return string
     */
    public function getWriteIndexName($params = array());
}
