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
require_once('include/SugarOAuth2Server.php');

class RestService extends ServiceBase {

    public $sessionId;
    public $user;

    /**
     * This function executes the current request and outputs the response directly.
     */
    public function execute() {
        try {
            if ( !empty($_REQUEST['__sugar_url']) ) {
                $rawPath = $_REQUEST['__sugar_url'];
            } else if ( !empty($_SERVER['PATH_INFO']) ) {
                $rawPath = $_SERVER['PATH_INFO'];
            } else {
                $rawPath = '/';
            }
            list($version,$path) = $this->parsePath($rawPath);
            $route = $this->findRoute($path,$version,$_SERVER['REQUEST_METHOD']);
            
            if ( !isset($route['noLoginRequired']) || $route['noLoginRequired'] == false ) {
                $this->authenticateUser();
            }
            
            if ( $route == false ) {
                throw new SugarApiExceptionNoMethod('Could not find any route that accepted a path like: '.$rawPath);
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
            } else {
                $postContents = null;
                if ( !empty($GLOBALS['HTTP_RAW_POST_DATA']) ) {
                    $postContents = $GLOBALS['HTTP_RAW_POST_DATA'];
                } else {
                    $postContents = file_get_contents('php://input');
                }
                if ( !empty($postContents) ) {
                    // This looks like the post contents are JSON
                    // Note: If we want to support rest based XML, we will need to change this
                    $postVars = json_decode($postContents,true);
                    if ( !is_array($postVars) ) {
                        // FIXME: Handle improperly encoded JSON
                        $postVars = array();
                    }
                } else {
                    // No posted variables
                    $postVars = array();
                }
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
                if ( isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
                    $httpAccept = $_SERVER['HTTP_ACCEPT_ENCODING'];
                }
                if( headers_sent() || empty($httpAccept) ) {
                    $encoding = false;
                } else if( strpos($httpAccept,'x-gzip') !== false ) {
                    $encoding = 'x-gzip';
                } else if( strpos($httpAccept,'gzip') !== false ) {
                    $encoding = 'gzip';
                } else {
                    $encoding = false;
                }
                header('Content-Type: application/json');
                if ( $encoding !== false ) {
                    header('Content-Encoding: '.$encoding);
                    $gzData = gzencode(json_encode($output));
                    header('Content-Length: '.strlen($gzData));
                    echo $gzData;
                } else {
                    echo json_encode($output);
                }
            }

        } catch ( SugarApiException $e ) {
            $this->handleException($e);
        } catch ( Exception $e ) {
            // Unknown exception
            $apiException = new SugarApiExceptionError($e->getMessage(),0,$e);
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
        header("HTTP/1.1 {$exception->errorCode}");

        $GLOBALS['log']->fatal('An unknown exception happened: '.$exception->getMessage());
        
        // TODO: Translate error messages
        echo("ERROR: ".$exception->getMessage());
        die();
    }

    protected function parsePath($rawPath) {
        $pathBits = explode('/',trim($rawPath,'/'));
        
        $versionBit = array_shift($pathBits);

        $version = (float)ltrim($versionBit,'v');

        return array($version,$pathBits);
        
    }

    protected function authenticateUser() {
        $valid = false;
        
        if ( isset($_SERVER['HTTP_OAUTH_TOKEN']) ) {
            // Passing a session id claiming to be an oauth token
            $this->sessionId = $_SERVER['HTTP_OAUTH_TOKEN'];

            $oauthServer = SugarOAuth2Server::getOAuth2Server();
            $oauthServer->verifyAccessToken($this->sessionId);
        } else if ( isset($_REQUEST[session_name()]) ) {
            // They just have a regular web session
            $this->sessionId = $_REQUEST[session_name()];
            // The OAuth server starts a session to validate the token, we have to start it manually, like a sucker.
            session_start();
        }
        
        if ( !empty($this->sessionId) ) {
            if ( isset($_SESSION['authenticated_user_id']) ) {
                $valid = true;
                $GLOBALS['current_user'] = BeanFactory::getBean('Users',$_SESSION['authenticated_user_id']);
            }
        }


        if ( $valid === false ) {
            throw new SugarApiExceptionNeedLogin("No valid authentication for user.");
        }

        //BEGIN SUGARCRM flav=pro ONLY
        SugarApplication::trackLogin();
        //END SUGARCRM flav=pro ONLY

        LogicHook::initialize()->call_custom_logic('', 'after_session_start');

        $this->user = $GLOBALS['current_user'];
    }

}
