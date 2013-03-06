<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Metric Manager class
 *
 * Provides basic interface to work with Metric Providers such as Newrelic provider
 * All configuration should be done in Config/providers.php file
 * or initialize method should be called with config array
 */
class SugarMetric_Manager
{
    /**
     * @var SugarMetric_Provider_Interface[]
     */
    protected $metricProviders = array();

    /**
     * @var SugarMetric_Manager
     */
    protected static $instance = null;

    /**
     * Transaction naming state
     *
     * @var bool
     */
    protected $transactionNamed = false;

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        global $sugar_config;

        // Check metrics is enabled in configuration
        if (empty($sugar_config['metrics_enabled'])) {
            return $this;
        }

        if (isset($sugar_config['metric_providers'])) {
            foreach ($sugar_config['metric_providers'] as $name => $path) {

                // Could not use SugarAutoLoader there, because in case of
                // entryPoint=getYUIComboFile script do not loads SugarAutoLoader
                if (file_exists($path)) {
                    require_once $path;

                    $additionalConfig = isset($sugar_config['metric_settings'][$name])
                        ? $sugar_config['metric_settings'][$name]
                        : array();

                    /** @var SugarMetric_Provider_Interface $metric  */
                    $metric = new $name($additionalConfig);

                    if ($metric->isLoaded()) {
                        $this->registerMetricProvider($name, $metric);
                    }
                }
            }
        }
    }

    /**
     * Deny cloning Singleton
     */
    protected function __clone()
    {

    }

    /**
     * Singleton initialization
     *
     * @return SugarMetric_Manager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new SugarMetric_Manager();
        }

        return self::$instance;
    }


    /**
     * Register Metric Provider as listener
     *
     * @param string $name
     * @param SugarMetric_Provider_Interface $metricProvider
     * @return SugarMetric_Manager
     */
    public function registerMetricProvider($name, SugarMetric_Provider_Interface $metricProvider)
    {
        if (!isset($this->metricProviders[$name])) {
            $this->metricProviders[$name] = $metricProvider;
        }

        return $this;
    }

    /**
     * Return registered Metric Providers
     *
     * @return SugarMetric_Provider_Interface[]
     */
    public function getMetricProviders()
    {
        return $this->metricProviders;
    }

    /**
     * Set up a name for current transaction
     *
     * @param string $name
     * @return SugarMetric_Manager
     */
    public function setTransactionName($name = '')
    {
        foreach ($this->metricProviders as $provider) {
            $provider->setTransactionName($name);
        }

        $this->transactionNamed = true;

        return $this;
    }

    /**
     * Returns transaction naming state
     *
     * @return bool
     */
    public function isNamedTransaction()
    {
        return $this->transactionNamed;
    }

    /**
     * Set current transaction as background
     *
     * @param string $name class metrics job name (f.e. "background", "massupdate")
     * @return SugarMetric_Manager
     */
    public function setMetricClass($name)
    {
        foreach ($this->metricProviders as $provider) {
            $provider->setMetricClass($name);
        }

        return $this;
    }

    /**
     * Add params to current transaction stack trace
     *
     * @param string $name
     * @param mixed $value
     * @return SugarMetric_Manager
     */
    public function addTransactionParam($name, $value)
    {
        foreach ($this->metricProviders as $provider) {
            $provider->addTransactionParam($name, $value);
        }

        return $this;
    }

    /**
     * Send exception trace to providers
     *
     * @param Exception $exception
     */
    public function handleException($exception)
    {
        foreach ($this->metricProviders as $provider) {
            $provider->handleException($exception);
        }
    }

}
