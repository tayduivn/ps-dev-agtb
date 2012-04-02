<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/api/SugarApi/ServiceBase.php');
require_once('include/api/SugarApi/ServiceDictionaryRest.php');

class RestService extends ServiceBase {

    /**
     * This function executes the current request and outputs the response directly.
     */
    public function execute() {
        try {
            if ( !isset($_SERVER['PATH_INFO']) ) {
                $rawPath = '/';
            } else {
                $rawPath = $_SERVER['PATH_INFO'];
            }
            list($version,$path) = $this->parsePath($_SERVER['PATH_INFO']);
            $route = $this->findRoute($path,$version,$_SERVER['REQUEST_METHOD']);
            
            if ( !isset($route['noLoginRequired']) || $route['noLoginRequired'] == false ) {
                $this->authenticateUser();
            }

            // This loads the path variables in, so that on the /Accounts/abcd, $module is set to Accounts, and $id is set to abcd
            $pathVars = $this->getPathVars($path,$route);

            if ( count($_GET) > 0 ) {
                // This has some get arguments, let's parse those in
                $getVars = $_GET;
            } else {
                $getVars = array();
            }
            
            if ( count($_POST) > 0 ) {
                // They have normal post arguments
                $postVars = array();
            } else if ( isset($route['rawPostContents']) && $route['rawPostContents'] ) {
                // This route wants the raw post contents
                // We just ignore it here, the function itself has to know how to deal with the raw post contents
                // this will mostly be used for binary file uploads.
                $postVars = array();
            } else if ( $postContents = file_get_contents("php://input") ) {
                // This looks like the post contents are JSON
                // Note: If we want to support rest based XML, we will need to change this
                $postVars = json_decode($postContents,true);
            } else {
                // No posted variables
                $postVars = array();
            }
            
            // I know this looks a little weird, overriding post vars with get vars, but 
            // in the case of REST, get vars ar fairly uncommon and pretty explicit, where
            // the posted document is probably the output of a generated form.
            $argArray = array_merge($postVars,$getVars,$pathVars);

            $apiClass = $this->loadApiClass($route);
            $apiMethod = $route['method'];
            
            $output = $apiClass->$apiMethod($this,$argArray);

            // TODO: gzip, and possibly XML based output
            if ( isset($route['rawReply']) && $route['rawReply']) {
                echo $output;
            } else {
                echo json_encode($output);
            }

        } catch ( SugarApiException $e ) {
            $this->handleException($e);
        } catch ( Exception $e ) {
            // Unknown exception
            $apiException = new SugarApiExceptionError('LBL_GENERIC_ERROR',0,$e);
            $this->handleException($apiException);
        }
    }

    protected function findRoute($path, $version, $requestType) {
        // Load service dictionary
        $this->dict = $this->loadServiceDictionary('ServiceDictionaryRest');
        return $this->dict->lookupRoute($path, $version, $requestType);
    }

    protected function getPathVars($path,$route) {
        $outputVars = array();
        foreach ( $route['pathVars'] as $i => $varName ) {
            if ( !empty($varName) ) {
                $outputVars[$varName] = $path[$i];
            }
        }
        
        return $outputVars;
    }

    protected function handleException(SugarApiException $exception) {
        // FIXME: Do something for real with the exceptions
        echo("ERROR: ".$exception->getMessage());
        die();
    }

    protected function parsePath($rawPath) {
        $pathBits = explode('/',$rawPath);
        
        $versionBit = array_shift($pathBits);

        $version = (float)ltrim($versionBit,'v');

        return array($version,$pathBits);
        
    }

    protected function authenticateUser() {
        // FIXME: Actually authenticate
    }

}
