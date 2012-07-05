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
     * The leading portion of the URI for building request URIs with in the API
     * @var
     */
    protected $resourceURIBase;

    /**
     * This function executes the current request and outputs the response directly.
     */
    public function execute() {
        try {
            $rawPath = $this->getRawPath();

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
                // We need to pre-parse this for JSON-encoded arguments because the XSS stuff will mangle them, and to keep symmetrywith POST style data
                $getVars = $_GET;
                if ( !empty($route['jsonParams']) ) {
                    foreach ( $route['jsonParams'] as $fieldName ) {
                        if ( isset($_GET[$fieldName]) && $_GET[$fieldName]{0} == '{' ) {
                            // This may be JSON data
                            $rawValue = $GLOBALS['RAW_REQUEST'][$fieldName];
                            $jsonData = json_decode($rawValue,true,32);
                            if ( $jsonData == null ) {
                                // Did not decode, could be a string that just happens to start with a '{', don't mangle it further
                                continue;
                            }
                            // Need to dig through this array and make sure all of the elements in here are safe
                            $getVars[$fieldName] = securexss($jsonData);
                        }
                    }
                }
            } else {
                $getVars = array();
            }
            

            if ( isset($route['rawPostContents']) && $route['rawPostContents'] ) {
                // This route wants the raw post contents
                // We just ignore it here, the function itself has to know how to deal with the raw post contents
                // this will mostly be used for binary file uploads.
                $postVars = array();
            } else if ( count($_POST) > 0 ) {
                // They have normal post arguments
                $postVars = securexss($_POST);
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
                    $postVars = securexss($postVars);
                } else {
                    // No posted variables
                    $postVars = array();
                }
            }
            
            // I know this looks a little weird, overriding post vars with get vars, but 
            // in the case of REST, get vars are fairly uncommon and pretty explicit, where
            // the posted document is probably the output of a generated form.
            $argArray = array_merge($postVars,$getVars,$pathVars);

            $apiClass = $this->loadApiClass($route);
            $apiMethod = $route['method'];
            
            $output = $apiClass->$apiMethod($this,$argArray);

            $this->respond($output, $route, $argArray);

        } catch ( Exception $e ) {
            $this->handleException($e);
        }
    }

    /**
     * Gets the raw path of the request
     *
     * @return string
     */
    public function getRawPath() {
        if ( !empty($_REQUEST['__sugar_url']) ) {
            $rawPath = $_REQUEST['__sugar_url'];
        } else if ( !empty($_SERVER['PATH_INFO']) ) {
            $rawPath = $_SERVER['PATH_INFO'];
        } else {
            $rawPath = '/';
        }

        return $rawPath;
    }

    /**
     * Gets the leading portion of the URI for a resource
     *
     * @param array|string $resource The resource to fetch a URI for as an array
     *                               of path parts or as a string
     * @return string The path to the resource
     */
    public function getResourceURI($resource) {
        if (empty($this->resourceURIBase)) {
            $this->setResourceURIBase();
        }

        // Empty resources are simply the URI for the current request
        if (empty($resource)) {
            $siteUrl = SugarConfig::get('site_url');
            return $siteUrl . $_SERVER['REQUEST_URI'];
        }

        if (is_string($resource)) {
            $parts = explode('/', $resource);
            return $this->getResourceURI($parts);
        } elseif (is_array($resource)) {
            // Logic here is, if we find a GET route for this resource then it
            // should be valid. In most cases, where there is a POST|PUT|DELETE
            // route that does not have a GET, we're not going to be handing that
            // URI out anyway, so this is a safe validation assumption.
            list($version,) = $this->parsePath($this->getRawPath());
            $route = $this->findRoute($resource, $version, 'GET');
            if ($route != false) {
                return $this->resourceURIBase . implode('/', $resource);
            }
        }
    }

    /**
     * For cases in which HTML is the requested response type but json is the
     * intended body content, this returns an array of status code and message.
     * This will also be used by the exception handler when dispatching exceptions
     * under the same requested response type conditions.
     *
     * @param string $message
     * @param int $code
     * @return array
     */
    public function getHXRReturnArray($message, $code = 200) {
        return array(
            'xhr' => array(
                'code' => $code,
                'message' => $message,
            ),
        );
    }

    /**
     * Attempts to find the route for this request, API version and request method
     *
     * @param array $path The request path
     * @param int $version The API version number
     * @param string $requestType The request method
     * @return mixed
     */
    protected function findRoute($path, $version, $requestType) {
        // Load service dictionary
        $this->dict = $this->loadServiceDictionary('ServiceDictionaryRest');
        return $this->dict->lookupRoute($path, $version, $requestType);
    }

    /**
     * Maps the route path with the request path to set variables from the request
     *
     * @param array $path The request path
     * @param array $route The route for this request
     * @return array
     */
    protected function getPathVars($path,$route) {
        $outputVars = array();
        foreach ( $route['pathVars'] as $i => $varName ) {
            if ( !empty($varName) ) {
                $outputVars[$varName] = $path[$i];
            }
        }
        
        return $outputVars;
    }

    /**
     * Handles exception responses
     *
     * @param Exception $exception
     */
    protected function handleException(Exception $exception) {
        if ( is_a($exception,"SugarApiException") ) {
            $httpError = $exception->getHttpCode();
            $errorLabel = $exception->getErrorLabel();
            $description = $exception->getDescription();
        } else if ( is_a($exception,"OAuth2ServerException") ) {
            $httpError = $exception->getHttpCode();
            $errorLabel = $exception->getMessage();
            $description = $exception->getDescription();
        } else {
            $httpError = 500;
            $errorLabel = 'unknown_error';
            $description = $exception->getMessage();
        }
        header("HTTP/1.1 {$httpError}");

        $GLOBALS['log']->error('An unknown exception happened: ('.$errorLabel.')'.$description);
        
        // TODO: Translate error messages
        $reply = "ERROR: ".$description;

        $crazyEncoding = false;
        // For edge cases when an HTML response is needed as a wrapper to JSON
        if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'sugar-html-json') {
            if (!isset($_REQUEST['platform']) || (isset($_REQUEST['platform']) && $_REQUEST['platform'] == 'portal')) {
                $reply = htmlentities(json_encode($this->getHXRReturnArray($reply, $exception->errorCode)));
                $crazyEncoding = true;
            }
        }
        if ( $crazyEncoding ) {
            echo($reply);
            die();
        }
        
        // Send proper headers
        header("Content-Type: application/json");
        header("Cache-Control: no-store");

        $replyData = array(
            'error'=>$errorLabel,
        );
        if ( !empty($description) ) {
            $replyData['error_description'] = $description;
        }
        echo(json_encode($replyData));
        die();
    }

    /**
     * Parses the request uri or request path as well as fetching the API request
     * version
     *
     * @param string $rawPath
     * @return array
     */
    protected function parsePath($rawPath) {
        $pathBits = explode('/',trim($rawPath,'/'));
        
        $versionBit = array_shift($pathBits);

        $version = (float)ltrim($versionBit,'v');

        return array($version,$pathBits);
        
    }

    /**
     * Handles authentication of the current user
     *
     * @throws SugarApiExceptionNeedLogin
     */
    protected function authenticateUser() {
        $valid = false;
        
        if ( isset($_SERVER['HTTP_OAUTH_TOKEN']) ) {
            // Passing a session id claiming to be an oauth token
            $this->sessionId = $_SERVER['HTTP_OAUTH_TOKEN'];
        } else if ( isset($_POST['oauth_token']) ) {
            $this->sessionId = $_POST['oauth_token'];
        } else if ( isset($_GET['oauth_token']) ) {
            $this->sessionId = $_GET['oauth_token'];
        }

        if ( !empty($this->sessionId) ) {
            $oauthServer = SugarOAuth2Server::getOAuth2Server();
            $oauthServer->verifyAccessToken($this->sessionId);

            if ( isset($_SESSION['authenticated_user_id']) ) {
                $valid = true;
                $GLOBALS['current_user'] = BeanFactory::getBean('Users',$_SESSION['authenticated_user_id']);
            }
        }

        if ( $valid === false ) {
            // In the case of large file uploads that are too big for the request too handle AND
            // the auth token being sent as part of the request body, you will get a no auth error
            // message on uploads. This check is in place specifically for file uploads that are too
            // big to be handled by checking for an empty request body for POST and PUT file requests.
            
            // Grab our path elements of the request and see if this is a files request
            $pathParts = $this->parsePath($this->getRawPath());
            if (isset($pathParts[1]) && is_array($pathParts[1]) && in_array('file', $pathParts[1])) {
                // If this is a POST request then we can inspect the $_FILES and $_POST arrays
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // If the post and files array are both empty on a POST request...
                    if (empty($_FILES) && empty($_POST)) {
                        throw new SugarApiExceptionRequestTooLarge('Request is too large');
                    }
                } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                    // PUT requests need to read the php://input stream
                    // Keep in mind that reading php://input is a one time deal
                    // But since we are bound for an exception here this is a safe
                    // consumption
                    $input = file_get_contents('php://input');
                    if (empty($input)) {
                        throw new SugarApiExceptionRequestTooLarge('Request is too large');
                    }
                }
            }

            // @TODO Localize exception strings
            throw new SugarApiExceptionNeedLogin("No valid authentication for user.");
        }
        
        //BEGIN SUGARCRM flav=pro ONLY
        SugarApplication::trackLogin();
        //END SUGARCRM flav=pro ONLY

        // Need to setup the session for portal users
        if( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            // Add the necessary visibility and acl classes to the default bean list
            require_once('modules/ACL/SugarACLSupportPortal.php');
            $default_acls = SugarBean::getDefaultACL();
            // This one overrides the Static ACL's, so disable that
            unset($default_acls['SugarACLStatic']);
            $default_acls['SugarACLStatic'] = false;
            $default_acls['SugarACLSupportPortal'] = true;
            SugarBean::setDefaultACL($default_acls);
            SugarACL::resetACLs();

            $default_visibility = SugarBean::getDefaultVisibility();
            $default_visibility['SupportPortalVisibility'] = true;
            SugarBean::setDefaultVisibility($default_visibility);
            $GLOBALS['log']->debug("Added SupportPortalVisibility to session.");
        }
        
        LogicHook::initialize()->call_custom_logic('', 'after_session_start');

        $this->user = $GLOBALS['current_user'];
    }

    /**
     * Sends the proper Content-Type header for the response based on either a
     * 'format' request arg or an Accept header.
     *
     * @TODO Handle Accept header parsing to determine content type
     * @access protected
     * @param array $args The request arguments
     */
    protected function sendContentTypeHeader($args) {
        if (isset($args['format']) && $args['format'] == 'sugar-html-json') {
            header('Content-Type: text/html');
        } else {
            // @TODO: Handle other response types here
            header('Content-Type: application/json');
        }
    }

    /**
     * Sends the content to the client
     *
     * @TODO Handle proper content disposition based on response content type
     * @access protected
     * @param mixed $content
     * @param string $encoding
     * @param array $args The request arguments
     */
    protected function sendContent($content, $encoding, $args) {
        // @TODO: Handle other content types for rendering
        if ( $encoding !== false ) {
            header('Content-Encoding: '.$encoding);
            $gzData = gzencode(json_encode($content));
            header('Content-Length: '.strlen($gzData));
            echo $gzData;
        } else {
            $response = json_encode($content);
            if (isset($args['format']) && $args['format'] == 'sugar-html-json' && (!isset($args['platform']) || $args['platform'] == 'portal')) {
                $response = htmlentities($response);
            }
            echo $response;
        }
    }

    /**
     * Sets the leading portion of any request URI for this API instance
     *
     * @access protected
     */
    protected function setResourceURIBase() {
        // Only do this if it hasn't been done already
        if (empty($this->resourceURIBase)) {
            // Default the base part of the request URI
            $apiBase = '/api/rest.php/';

            // Check rewritten URLs AND request uri vs script name
            if (isset($_REQUEST['__sugar_url']) && strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === false) {
                // This is a forwarded rewritten URL
                $apiBase = '/rest/';
            }

            // Get our version
            preg_match('#v(?>\d+)/#', $_SERVER['REQUEST_URI'], $m);
            if (isset($m[0])) {
                $apiBase .= $m[0];
            }

            // This is for our URI return value
            $siteUrl = SugarConfig::get('site_url');

            // Get the file uri bas
            $this->resourceURIBase = $siteUrl . $apiBase;
        }
    }

    /**
     * Handles the response
     *
     * @param array|string $output The output to send
     * @param array $route The route for this request
     * @param array $args The request arguments
     */
    protected function respond($output, $route, $args) {
        // TODO: gzip, and possibly XML based output
        if (!empty($route['rawReply'])) {
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

            // Handle content type header sending
            $this->sendContentTypeHeader($args);

            // Send the content
            $this->sendContent($output, $encoding, $args);
        }
    }
}
