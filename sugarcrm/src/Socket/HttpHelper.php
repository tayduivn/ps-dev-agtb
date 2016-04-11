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

namespace Sugarcrm\Sugarcrm\Socket;

/**
 * Class HttpHelper extends SugarHttpClient functions and provides convenient
 * methods to sends JSON requests to server.
 *
 * For checking server availability use @see HttpHelper::ping()
 *
 * For sending request to server use @see HttpHelper::send()
 *
 * Examples:
 *
 * <code>
 * // instantiate helper
 * $httpHelper = new HttpHelper();
 *
 * // checks server availability by url
 * $httpHelper->ping('http://example.site');
 *
 * // sends HTTP request to server
 * $httpHelper->send(
 *      'delete',
 *      'http://example.site/',
 *      '{"url":"http://sugar_host.site","token": "20db6fcd-0ce9-4da4-87d1-fae1d563b5a2"}'
 *      array(
 *          'X-Auth-Token: auth-token',
 *          'X-Auth-Version: 1',
 *      )
 * );
 *
 * </code>
 *
 * @package Sugarcrm\Sugarcrm\Socket
 */
class HttpHelper extends \SugarHttpClient
{
    /**
     * @var mixed
     */
    protected $lastResponse = null;

    /**
     * Checks server availability.
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
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (200 === $returnCode);
    }

    /**
     * Performs server request.
     *
     * @param string $method (get|post|put|delete)
     * @param string $url
     * @param string $args
     * @param array $headers
     * @return bool was request performed successfully
     */
    public function send($method, $url, $args = '', $headers = array())
    {
        $this->last_error = '';
        $this->lastResponse = null;

        $curlOpts = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($args)),
        );
        if ($headers) {
            $curlOpts[CURLOPT_HTTPHEADER] = array_merge($curlOpts[CURLOPT_HTTPHEADER], $headers);
        }

        if ($curlOpts[CURLOPT_CUSTOMREQUEST] !== 'POST') {
            $curlOpts[CURLOPT_POST] = false;
        }

        if ($args) {
            $curlOpts[CURLOPT_POSTFIELDS] = $args;
        }

        $response = $this->callRest($url, $args, $curlOpts);
        $this->lastResponse = $this->isSuccess() ? json_decode($response, true) : null;
        return $this->isSuccess();
    }

    /**
     * Returns true if last request doesn't contain any errors.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return ($this->last_error === '');
    }

    /**
     * Returns decoded response's body from last request.
     *
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
