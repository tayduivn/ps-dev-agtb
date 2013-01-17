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

require_once 'include/SugarMetric/Provider/Interface.php';

/**
 * SugarMetric_Provider_Log class used for logging and debugging metric's providers
 *
 * Registered in SugarMetric_Manager only if some logger class is available
 *
 * //TODO : Add setLogger($logger) and getLogger() method
 */
class SugarMetric_Provider_Log implements SugarMetric_Provider_Interface
{
    /**
     * @var LoggerManager
     */
    protected $logger;

    /**
     * Default logging level
     *
     * @var string
     */
    protected $logLevel = 'debug';

    /**
     * Initialize Log Metric Provider
     *
     * @param array $params optional params that come up from config.php
     */
    public function __construct($params)
    {
        if (!isset($GLOBALS['log']) && class_exists('SugarObject')) {
            require_once 'include/SugarLogger/LoggerManager.php';
            $this->logger = LoggerManager::getLogger('SugarCRM');
        } elseif (isset($GLOBALS['log'])) {
            $this->logger = $GLOBALS['log'];
        }

        if (isset($params['log_level'])) {
            $this->logLevel = $params['log_level'];
        }
    }

    /**
     * Returns "true" if some logger is available and was
     * Otherwise returns false
     *
     * @return bool
     */
    public function isLoaded()
    {
        return (bool) $this->logger;
    }

    /**
     * Set up a name for current Web Transaction
     *
     * @param string $name
     * @return null
     */
    public function setTransactionName($name)
    {
        $this->logger->{$this->logLevel}('Log Metric Provider: setTransactionName with "' . $name . '" is called');
    }

    /**
     * Add custom parameter to transaction stack trace
     *
     * @param string $name
     * @param mixed $value
     * @return null
     */
    public function addTransactionParam($name, $value)
    {
        $this->logger->{$this->logLevel}('Log Metric Provider: addTransactionParam with "' . $name . ' - ' . '" is called');
    }

    public function setCustomMetric($name, $value)
    {
        $this->logger->{$this->logLevel}('Log Metric Provider: setCustomMetric with "' . $name . ' - ' . $value . '" is called');
    }

    /**
     * Provide exception handling and reports to server stack trace information
     *
     * @param Exception $exception
     * @return null
     */
    public function handleException(Exception $exception)
    {
        $this->logger->{$this->logLevel}('Log Metric Provider: handleException with "' . $exception->getMessage() . '" is called');
    }

    /**
     * Set transaction class name (f.e. background, massupdate)
     *
     * @param string $name
     * @return null
     */
    public function setMetricClass($name)
    {
        $this->logger->{$this->logLevel}('Log Metric Provider: setMetricClass with class name: "' . $name . '" is called');
    }
}
