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

namespace Sugarcrm\Sugarcrm\Trigger;

/**
 * Class HttpHelper wraps curl functions and provides
 * methods to set up and delete triggers on trigger server.
 *
 * For checking trigger server availability use @see HttpHelper::ping()
 *
 * For sending request to trigger server use @see HttpHelper::send()
 *
 * Examples:
 *
 * <code>
 * // instantiate helper
 * $httpHelper = new HttpHelper();
 *
 * // checks trigger server availability by url
 * $httpHelper->ping('http://trigger_server.site');
 *
 * // sends HTTP request to trigger server
 * $httpHelper->send(
 *      'delete',
 *      'http://trigger_server.site/',
 *      '{"url":"http://sugar_host.site","token": "20db6fcd-0ce9-4da4-87d1-fae1d563b5a2","id":"c92af13d"}'
 * );
 *
 * </code>
 *
 * @package Sugarcrm\Sugarcrm\Trigger
 */
class HttpHelper extends \SugarHttpClient
{

    /**
     * Checks trigger server availability.
     *
     * @param string $url
     * @return bool
     */
    public function ping($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (200 === $retcode);
    }

    /**
     * Performs trigger server request
     *
     * @param string $method (post or delete)
     * @param string $url
     * @param string $args
     * @return bool was request performed successfully
     */
    public function send($method, $url, $args = '')
    {
        $curl = curl_init($url);

        $curlOpts = $this->getCurlOpts(array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($args)),
            CURLOPT_POSTFIELDS => $args
        ));

        curl_setopt_array($curl, $curlOpts);

        $GLOBALS['log']->debug("HTTP client call: $method $url -> " . var_export($args, true));
        $response = curl_exec($curl);

        // Handle error
        if ($response === false) {
            $this->last_error = 'ERROR_REQUEST_FAILED';
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $GLOBALS['log']->error("HTTP client: cURL call failed for $method '$url': error $curl_errno: $curl_error");
            return false;
        }

        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl);

        return true;
    }
}
