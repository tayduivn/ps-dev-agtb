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
 * Generic strategy for indexing data into elastic
 */
abstract class SugarSearchEngineElasticIndexStrategyBase implements SugarSearchEngineElasticIndexStrategyInterface
{
    /**
     * Strategy config settings
     * @var array
     */
    protected $config;

    /**
     * Sets the configuration settings for the strategy
     *
     * @param $config
     */
    public function setConfig($config = array())
    {
        $this->config = $config;
    }

    /**
     * Returns the configuration settings for the strategy
     *
     * @returns array config settings
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns a unique index name for the sugar instance
     * @see $sugar_config['unique_key']
     * @return string
     */
    protected function getUniqueIndexName($clearCache = false)
    {
        $config = SugarConfig::getInstance();

        // adding option to be able to clear SugarConfig cache (needed by installer)
        if ($clearCache) {
            $config->clearCache('unique_key');
        }

        return strtolower($config->get('unique_key', ''));
    }

    /**
     *
     * Create single index
     * @param \Elastica\Client $client
     * @param string $indexName
     * @param array $params
     * @param boolean $recreate
     * @throws Exception
     */
    protected function createSingleIndex(\Elastica\Client $client, $indexName, $params = array(), $recreate = false)
    {
        try {
            // create an elastic index
            $settings = $this->getIndexSetting($indexName, $params);
            $index = new \Elastica\Index($client, $indexName);
            $index->create($settings, $recreate);
        } catch (Exception $e) {
            // ignore the IndexAlreadyExistsException exception
            if (strpos($e->getMessage(), 'IndexAlreadyExistsException') === false) {
                throw $e;
            }
        }
        return $index;
    }

    /**
     *
     * Create index mapping
     * @param SugarSearchEngineElasticMapping $mapper
     */
    protected function setMapping(SugarSearchEngineElasticMapping $mapper)
    {
        $mapper->setFullMapping();
    }

    /**
     *
     * Read default and specific index settings from config
     * @param string  $indexName
     * @param array   $params
     * @param boolean $addDefaults
     * @return array
     */
    protected function getIndexSetting($indexName, $params = array(), $addDefaults = true)
    {
        $indexSettings = array(
            'index' => array(
                'analysis' => array(
                    'analyzer' => array(
                        'core_email_lowercase' => array(
                            'type' => 'custom',
                            'tokenizer' => 'uax_url_email',
                            'filter' => array(
                                'lowercase',
                            ),
                        ),
                    ),
                ),
            ),
        );

        if (empty($params['index_settings']) || !is_array($params['index_settings'])) {
            return $indexSettings;
        }

        $settings = $params['index_settings'];
        if ($addDefaults && isset($settings['default']) && is_array($settings['default'])) {
            $indexSettings = sugarArrayMergeRecursive($indexSettings, $settings['default']);
        }

        if (isset($settings[$indexName]) && is_array($settings[$indexName])) {
            $indexSettings = sugarArrayMergeRecursive($indexSettings, $settings[$indexName]);
        }

        $GLOBALS['log']->info("Index settings for $indexName -> ".var_export($indexSettings, true));
        return $indexSettings;
    }
}
