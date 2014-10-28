<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
//FILE SUGARCRM flav=pro ONLY

class DnbCurlWrapper
{
    /**
     * @var resource curl handle
     */
    private $curlHandle;

    public function __construct() {
        $this->curlHandle = curl_init();
    }

    /**
     * Set curl options
     * @param array $curlOptions
     */
    public function setCurlOptions($curlOptions) {
        curl_setopt_array($this->curlHandle, $curlOptions);
    }

    /**
     * @param array $curlOptions
     * @return mixed
     */
    public function execute($curlOptions) {
        $this->setCurlOptions($curlOptions);
        return curl_exec($this->curlHandle);
    }

    /**
     * Get cURL information on $option
     * @param string $option
     * @return mixed
     */
    public function getInfo($option) {
        return curl_getinfo($this->curlHandle, $option);
    }

    /**
     * @return int
     */
    public function getErrorNo() {
        return curl_errno($this->curlHandle);
    }

    /**
     * @return string
     */
    public function getError() {
        return curl_error($this->curlHandle);
    }
}