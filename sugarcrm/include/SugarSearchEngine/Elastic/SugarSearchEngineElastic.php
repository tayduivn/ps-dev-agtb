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
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
require_once('include/SugarSearchEngine/Interface.php');
require_once('include/SugarSearchEngine/Solr/PHPSolr/Service.php');

class SugarSearchEngineElastic implements SugarSearchEngineInterface
{
    private $_server = "";
    private $_config = array();
    private $_backend = null;
    public function __construct($params)
    {
        $this->_config = $params;

        //TODO: Support basic auth?
        $scheme = isset($this->_config['scheme']) ? $this->_config['scheme'] : 'http';
        $port = isset($this->_config['port']) ? $this->_config['port'] : '9200';
        $host = isset($this->_config['host']) ? $this->_config['host'] : 'localhost';
        $index = isset($this->_config['index']) ? $this->_config['index'] : ($GLOBALS['sugar_config']['unique_key']);
        $this->_server = "{$scheme}://{$host}:$port/$index";
        $this->_backend = new SugarSearchEngineElasticClient();
    }

    public function connect($config)
    {

    }

    public function indexBean($bean)
    {

    }

    public function flush()
    {

    }

    public function delete($bean)
    {

    }

    public function search($query, $offset = 0, $limit = 20)
    {
        $url = $this->_server . "/_search?" . http_build_query(array('q' => $query));
        $rs = $this->_backend->callRest($url, TRUE);
        return $rs;

    }
}


class SugarSearchEngineElasticClient
{
    private $last_error;

    public function callRest($url, $isGET = true, $postArgs = array() )
    {
        if(!function_exists("curl_init")) {
            $this->last_error = 'ERROR_NO_CURL';
            $GLOBALS['log']->fatal("Sugar Elastic Search Failed - no cURL!");
            return false;
        }

        $curl = curl_init($url);
        if(!$isGET)
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        }
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $GLOBALS['log']->debug("Elastic Search call: $url -> $postArgs");
        $response = curl_exec($curl);
        if($response === false) {
            $this->last_error = 'ERROR_REQUEST_FAILED';
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $GLOBALS['log']->error("cURL call failed: error $curl_errno: $curl_error");
            return false;
        }
        $GLOBALS['log']->debug("Elastic Search response: $response");
        curl_close($curl);
        return $response;
    }

}


