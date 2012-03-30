<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License foru the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Very basic HTTP client
 * @api
 * Used in various places of the code and can be mocked out.
 * Presently does only one op - POST to url.
 * If you need more complex stuff, use Zend_Http_Client
 */
class SugarHttpClient
{
    protected $last_error = '';
    /**
     * sends POST request to REST service via CURL
     * @param string $url URL to call
     * @param string $postArgs POST args
     */
    public function callRest($url, $postArgs)
    {
        if(!function_exists("curl_init")) {
            $this->last_error = 'ERROR_NO_CURL';
            $GLOBALS['log']->fatal("REST call failed - no cURL!");
            return false;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $GLOBALS['log']->debug("HTTP client call: $url -> $postArgs");
        $response = curl_exec($curl);
        if($response === false) {
            $this->last_error = 'ERROR_REQUEST_FAILED';
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $GLOBALS['log']->error("HTTP client: cURL call failed: error $curl_errno: $curl_error");
            return false;
        }
        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl);
        return $response;
    }

    /**
     * Returns code of last error that happened to the client
     * @return string
     */
    public function getLastError()
    {
        return $this->last_error;
    }
}
