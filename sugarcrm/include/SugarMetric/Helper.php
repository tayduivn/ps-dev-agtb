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
 * SugarMetric Helper class
 *
 * Loads SugarMetric_Manager with depending objects such as sugar configuration
 * Used to take all loading logic in one place
 */
class SugarMetric_Helper
{
    /**
     * Loads SugarCRM configuration files
     *
     * In case global configuration files are not loaded
     * (f.e. on entryPoint "getImage" or "getYUIComboFile"
     * @see include/preDispatch.php)
     * we should load them to use in SugarMetric_Manager class
     */
    public static function loadSugarConfig()
    {
        global $sugar_config;

        if ($sugar_config) {
            return;
        }

        if (is_file('config.php')) {
            require_once('config.php');
        }

        if (is_file('config_override.php')) {
            require_once('config_override.php');
        }
    }

    /**
     * Helper method to load SugarMetric_Manager
     *
     * SugarAutoLoader is not available only in case of entryPoint = "getYUIComboFile"
     * @see include/preDispatch.php
     */
    public static function loadManagerClass()
    {
        if (class_exists('SugarAutoLoader')) {
            SugarAutoLoader::requireWithCustom('include/SugarMetric/Manager.php');
        } else {
            if (file_exists('custom/include/SugarMetric/Manager.php')) {
                require_once 'custom/include/SugarMetric/Manager.php';
            } elseif (file_exists('include/SugarMetric/Manager.php')) {
                require_once 'include/SugarMetric/Manager.php';
            }
        }
    }

    /**
     * Helper method to load SugarMetric_Manager and set endPoints and transaction name
     *
     * @param string|bool $transaction is $transaction is FALSE do not call setTransactionName method
     */
    public static function run($transaction = '')
    {
        self::loadSugarConfig();
        self::loadManagerClass();

        $instance = SugarMetric_Manager::getInstance();

        if ($transaction !== false) {
            $instance->setTransactionName($transaction);
        }
    }
}
