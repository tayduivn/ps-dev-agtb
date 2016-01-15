<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarHttpClient.php';

/**
 * Class HealthCheckClient
 */
class HealthCheckClient extends SugarHttpClient
{
    const DEFAULT_ENDPOINT = "https://updates.sugarcrm.com/sortinghat.php";

    /**
     * @param $key
     * @param $logFilePath
     * @return bool
     */
    public function send($key, $logFilePath)
    {
        $data = array(
            "key" => $key,
            "log" => "@$logFilePath",
        );

        $endpoint = $this->getEndpoint();
        $curlOpts = $this->getOptsForEndpoint($endpoint);
        $response = $this->callRest($endpoint, $data, $curlOpts);

        return strpos($response, "Saved:") !== false;
    }

    /**
     * Returns endpoint
     * reads $sugar_config['healthcheck']['endpoint']
     * default is HealthCheckClient::DEFAULT_ENDPOINT
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return SugarConfig::getInstance()->get('healthcheck.endpoint', self::DEFAULT_ENDPOINT);
    }

    /**
     * Get cURL options for given endpoint
     * @param string $endpoint Endpoint
     * @return array
     */
    protected function getOptsForEndpoint($endpoint)
    {
        $curlOpts = array();

        // validate server cert for SSL connections
        if (strpos($endpoint, 'https://') === 0) {
            $curlOpts = array(
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            );
        }
        return $curlOpts;
    }
}
