<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
 * Check that the Sugar FTS Engine configuration is valid
 */
class SugarUpgradeCheckFTSConfig extends UpgradeScript
{
    public $order = 200;
    public $version = '7.1.5';

    public function run()
    {
        global $sugar_config;

        $ftsConfig = isset($sugar_config['full_text_engine']) ? $sugar_config['full_text_engine'] : null;
        // Check that Elastic info is set (only currently supported search engine)
        if (empty($ftsConfig) || empty($ftsConfig['Elastic']) ||
            empty($ftsConfig['Elastic']['host']) || empty($ftsConfig['Elastic']['port'])
        ) {
            // error implies fail
            $this->error('Elastic Full Text Search engine needs to be configured on this Sugar instance prior to upgrade.');
            $this->error('Access Full Text Search configuration under Administration > Search.');
        } else {
            // Test Elastic FTS connection
            require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
            $searchEngine = SugarSearchEngineFactory::getInstance('Elastic', $ftsConfig['Elastic']);
            $status = $this->getServerStatusElastic($searchEngine, $ftsConfig['Elastic']);

            if (!$status['valid']) {
                $this->error('Connection test for Elastic Full Text Search engine failed.  Check your FTS configuration.');
                $this->error('Access Full Text Search configuration under Administration > Search.');
            }
        }
    }

    /**
     * The older versions of getServerStatus may be broken, so we need to re-implement this to have it pass
     *
     * @return array
     */
    protected function getServerStatusElastic($searchEngine, $config)
    {
        $this->_client = $this->getElasticaClient($config);
        global $app_strings;
        $isValid = false;
        try {
            $results = $this->_client->request('', $this->getElasticaRequestConstant('GET'))->getData();
            if (!empty($results['status']) && $results['status'] === 200) {
                $isValid = true;
                $displayText = $app_strings['LBL_EMAIL_SUCCESS'];
            } else {
                $displayText = $app_strings['ERR_ELASTIC_TEST_FAILED'];
            }
        } catch (Exception $e) {
            $displayText = $e->getMessage();
            $this->error("Unable to get server status: $displayText");
        }

        return array('valid' => $isValid, 'status' => $displayText);
    }

    /**
     * Wrapper to instantiate Elastica Client object
     *
     * @param array $config
     * @return mixed \Elastica\Client|Elastica_Client
     */
    protected function getElasticaClient($config)
    {
        $class = $this->getElasticaFQClassName('Client');
        return new $class($config);
    }

    /**
     * Wrapper to get constant values from Elastica Request
     *
     * @param string $name
     * @return string
     */
    protected function getElasticaRequestConstant($name)
    {
        $class = $this->getElasticaFQClassName('Request');
        return constant($class.'::'.$name);
    }

    /**
     * Get fully qualified Elastica class name based on the available Elastica library
     *
     * @param string $class Base Elastica class name (i.e. Client, Request, ...)
     * @return string
     */
    protected function getElasticaFQClassName($class)
    {
        if (class_exists('Elastica_'.$class)) {
            $prefix =  'Elastica_';
        } else {
            $prefix = '\\Elastica\\';
        }
        return $prefix.$class;
    }
}
