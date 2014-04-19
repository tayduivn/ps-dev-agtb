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

require_once('data/BeanFactory.php');

class HelpApi extends SugarApi 
{
    /**
     * The help endpoints need some client files to function properly.
     * 
     * @var array
     */
    protected $clientFiles = array(
        'cache/include/javascript/sugar_grp1_jquery.js',
    );
    
    /**
     * Used in transformations of class names, like making 
     * SugarApiExceptionRequestTooLarge into Request Too Large or request-too-large
     * 
     * @var string
     */
    protected $camelCasePattern = '#(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])#';

    public function registerApiRest() {
        return array(
            'getHelp' => array(
                'reqType' => 'GET',
                'path' => array('help'),
                'pathVars' => array(''),
                'method' => 'getHelp',
                'shortHelp' => 'Shows Help information',
                'longHelp' => 'include/api/help/help_get_help.html',
                'rawReply' => true,
                // Everyone needs some help sometimes.
                'noLoginRequired' => true,
            ),
            'getExceptions' => array(
                'reqType' => 'GET',
                'path' => array('help', 'exceptions'),
                'pathVars' => array('', ''),
                'method' => 'getExceptionsHelp',
                'shortHelp' => 'Shows the exceptions thrown by the API',
                'longHelp' => 'include/api/help/help_get_exceptions.html',
                'rawReply' => true,
                // Everyone needs some help sometimes.
                'noLoginRequired' => true,
            ),
        );
    }

    public function getHelp($api, $args) {
        // This function needs to peer into the deep, twisted soul of the RestServiceDictionary
        $dir = $api->dict->dict;

        if ( empty($args['platform']) ) {
            $platform = 'base';
        } else {
            $platform = $args['platform'];
        }

        $endpointList = array();
        foreach ( $dir as $startDepth => $dirPart ) {
            if ( isset($dirPart[$platform]) ) {
                $endpointList = array_merge($endpointList, $this->getEndpoints($dirPart[$platform],$startDepth));
            }
        }

        // Add in the full endpoint paths, so we can sort by them
        foreach ( $endpointList as $idx => $endpoint ) {
            $fullPath = '';
            foreach ( $endpoint['path'] as $pathIdx => $pathPart ) {
                if ( $pathPart == '?' ) {
                    // pull in the path variable in here so the documentation is readable
                    $pathPart = ':'.$endpoint['pathVars'][$pathIdx];
                }
                $fullPath .= '/'.$pathPart;
            }
            $endpointList[$idx]['fullPath'] = $fullPath;

            // Handle exception lists
            $exceptions = array();
            if (!empty($endpoint['exceptions']) && is_array($endpoint['exceptions'])) {
                foreach ($endpoint['exceptions'] as $exception) {
                    $id = $this->getExceptionId($exception);
                    $type = $this->getExceptionType($exception);
                    $exceptions[$id] = $type;
                }
            }
            $endpointList[$idx]['exceptions'] = $exceptions;
        }

        // Sort the endpoint list
        usort($endpointList,array('HelpApi','cmpEndpoints'));

        $this->ensureClientFiles();
        $jsfiles = $this->clientFiles;
        ob_start();
        require('include/api/help/extras/helpList.php');
        $endpointHtml = ob_get_clean();

        $api->setHeader('Content-Type', 'text/html');
        return $endpointHtml;
    }
    
    /**
     * Gets the exceptions list for the exceptions help endpoint
     * 
     * @param RestService $api The service object
     * @param array $args The request arguments
     * @return string The HTML output for this help endpoint
     */
    public function getExceptionsHelp($api, $args)
    {
        $exceptions = $this->getExceptions();
        $this->ensureClientFiles();
        $jsfiles = $this->clientFiles;
        ob_start();
        require('include/api/help/extras/exceptionList.php');
        $endpointHtml = ob_get_clean();

        $api->setHeader('Content-Type', 'text/html');
        return $endpointHtml;
    }
    
    /**
     * Gets the list of exceptions for this system along with some useful information
     * about each exception
     * 
     * @return array
     */
    protected function getExceptions()
    {
        // Read the contents of the API exception file to get the list of API
        // exceptions we currently throw
        $file = 'include/api/SugarApiException.php';
        $content = file_get_contents($file);

        // Parse it for class names, as that will drive the list of data
        $pattern = '#class ([a-zA-Z0-9_]*) #';
        $matches = array();
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        // Prepare the return
        $exceptions = array();

        // Now loop through the exceptions and build a collection of information
        // on each one
        foreach ($matches as $match) {
            // The exception class
            $class = $match[1];

            // Start collecting information now
            $e = new $class();
            $code = $e->httpCode;
            $label = $e->errorLabel;
            $message = $e->messageLabel;
            $desc = $e->descriptionLabel;
            $exceptions[$class] = array(
                'element_id' => $this->getExceptionId($class),
                'class' => $class,
                'code' => $code,
                'label' => $label,
                'type' => $this->getExceptionType($class),
                'message_key' => $message,
                'message' => translate($message),
                'desc_key' => $desc,
                'desc' => translate($desc),
            );
        }

        return $exceptions;
    }

    /**
     * This function is called recursively to pull the endpoints out of the pre-optimized arrays that the service dictionary stores them in. It's complicated and slow, but since this function is only called when the developer wants some docs, it's not worth the cost of storing this information elsewhere.
     * @param $dirPart array required, the section of the directory you are looking at
     * @param $depth int required, how much deeper you need to go before you actually find the endpoints.
     * @return array An array of endpoints for that directory part.
     */
    protected function getEndpoints($dirPart, $depth) {
        if ( $depth == 0 ) {
            $endpoints = array();
            foreach ( $dirPart as $subEndpoints ) {
                $endpoints = array_merge($endpoints, $subEndpoints);
            }

            return $endpoints;
        }

        $newDepth = $depth - 1;
        $endpoints = array();
        foreach ( $dirPart as $subDir ) {
            $endpoints = array_merge($endpoints, $this->getEndpoints($subDir, $newDepth));
        }

        return $endpoints;
    }

    /**
     * This function compares endpoints, it would be an anonymous function but we have to support older versions of PHP
     * @param $endpoint1 hash required, This should be one endpoint element in the endpoint list. Should look pretty close to something registered through registerApiRest()
     * @param $endpoint2 hash required, Second verse, same as the first.
     * @return int +1 if endpoint1 is greater than endpoint2, -1 otherwise
     */
    public static function cmpEndpoints($endpoint1, $endpoint2) {
        return ( $endpoint1['fullPath'] > $endpoint2['fullPath'] ) ? +1 : -1;
    }

    /**
     * Ensures that necessary client files are in place 
     * 
     * @return boolean
     */
    protected function ensureClientFiles()
    {
        foreach ($this->clientFiles as $file) {
            // (re)build the necessary cache files if the file we want does not exist
            if (!file_exists($file)) {
                // Maintain state as well as possible
                $hasRootDirectory = isset($_REQUEST['root_directory']);

                // Same process as SugarView
                $_REQUEST['root_directory'] = ".";
                require_once("jssource/minify_utils.php");
                $minifyUtils = new SugarMinifyUtils();
                $minifyUtils->ConcatenateFiles(".");

                // If we didn't start with this index, clean up after ourselves
                if (!$hasRootDirectory) {
                    unset($_REQUEST['root_directory']);
                }
                return true;
            }
        }

        return true;
    }

    /**
     * Gets the exception type label for an exception from the exception class
     * name
     * 
     * @param string $class The exception class to get the type from
     * @return string
     */
    protected function getExceptionType($class)
    {
        // The exception type name (class name less SugarApiException)
        $typeName = str_replace('SugarApiException', '', $class);

        // Type of the class, normalized for your viewing pleasure
        $type = preg_replace($this->camelCasePattern, ' $1', $typeName);
        if (empty($type)) {
            $type = 'General Exception';
        }

        return $type;
    }

    /**
     * Takes a camel case exception class name and makes it lower case hyphenated:
     * SugarApiExceptionRequestTooLarge -> sugar-api-exception-request-too-large
     * 
     * @param string $class The exception class name to transform
     * @return string
     */
    protected function getExceptionId($class)
    {
        return strtolower(preg_replace($this->camelCasePattern, '-$1', $class));
    }
}
