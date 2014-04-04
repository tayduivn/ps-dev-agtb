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

require_once("include/SugarSearchEngine/Elastic/SugarSearchEngineElasticIndexStrategyInterface.php");
require_once("include/SugarSearchEngine/Elastic/SugarSearchEngineElasticIndexStrategyBase.php");

/**
 * Elastic index strategy factory class
 */
class SugarSearchEngineElasticIndexStrategyFactory
{
    /**
     * @var array
     */
    protected static $instance;

    /**
     * getInstance()
     *
     * Create or fetch index strategy
     *
     * @static
     * @param string $name
     * @param array $config
     * @return ElasticIndexStrategyInterface
     */
    public static function getInstance($strategy = '', $config = null)
    {
        $sugarConfig = SugarConfig::getInstance();
        // get strategy name from $sugar_config, default to "single"
        if (empty($strategy)) {
            $strategy = $sugarConfig->get('full_text_engine.Elastic.index_strategy.name', 'single');
        }

        if (!empty(self::$instance[$strategy])) {
            return self::$instance[$strategy];
        }

        // use strategy config from $sugar_config if not set explicitly
        if (!is_array($config)) {
            $config = $sugarConfig->get('full_text_engine.Elastic.index_strategy.config', array());
        }

        // setup instance
        self::$instance[$strategy] = self::setupStrategy($strategy, $config);
        return self::$instance[$strategy];
    }

    /**
     * @static
     * @param string $name
     * @param array $config
     * @return mixed (bool|SugarSearchEngineInterface)
     */
    protected static function setupStrategy($name, $config = array())
    {
        $className = "SugarSearchEngineElasticIndexStrategy" . ucfirst($name);
        $filePath = "include/SugarSearchEngine/Elastic/{$className}.php";

        if (SugarAutoLoader::requireWithCustom($filePath, true)) {
            $className = SugarAutoLoader::customClass($className, true);
            $strategy = new $className();
            if ($strategy instanceof SugarSearchEngineElasticIndexStrategyInterface) {
                $strategy->setConfig($config);
                $GLOBALS['log']->info("Found Sugar Search Elastic Index Strategy: {$className}");
                return $strategy;
            }
        }
        $GLOBALS['log']->fatal("Failure loading SSE index strategy class: {$className}");
        return false;
    }
}
