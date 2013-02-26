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
 * Newrelic data provider class
 *
 * Implements basic Newrelic functions and configuration
 */
class SugarMetric_Provider_Newrelic implements SugarMetric_Provider_Interface
{
    /**
     * Contains information about loaded status of newrelic extension
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * Initialize Newrelic Metric Provider and add it to SugarMetric_Manager listeners chain
     *
     * @param array $additionalParams
     */
    public function __construct(array $additionalParams)
    {
        if ($this->isLoaded = extension_loaded('newrelic')) {

            foreach ($additionalParams as $name => $param) {

                switch (strtolower($name)) {
                    case 'applicationname' :
                        newrelic_set_appname($param);
                        break;
                    default :
                        break;
                }
            }
        } else {
            if (isset($GLOBALS['log'])) {
                $GLOBALS['log']->debug('SugarMetric_Provider_Newrelic: newrelic php extension is not loaded on server');
            }
        }
    }

    /**
     * Returns "true" if Newrelic extension is loaded
     * Otherwise returns false
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Set up a name for current Web Transaction
     *
     * @param string $name
     * @return null
     */
    public function setTransactionName($name)
    {
        newrelic_name_transaction($name);
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
        newrelic_add_custom_parameter($name, $value);
    }

    public function setCustomMetric($name, $value)
    {
        newrelic_custom_metric($name, floatval($value));
    }

    /**
     * Provide exception handling and reports to server stack trace information
     *
     * @param Exception $exception
     * @return null
     */
    public function handleException(Exception $exception)
    {
        newrelic_notice_error($exception->getMessage(), $exception);
    }

    /**
     * Mark transaction as background or web transaction
     *
     * @param string $name
     * @return null|void
     */
    public function setMetricClass($name)
    {
        if ($name == 'background') {
            newrelic_background_job(true);
        }
    }
}
