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

require_once('include/api/ServiceDictionary.php');

class ServiceDictionaryRest extends ServiceDictionary {
    /**
     * Loads the dictionary so it can be searche
     */
    public function loadDictionary() {
        $this->dict = $this->loadDictionaryFromStorage('rest');
    }

    /**
     * Looks up a route based on the passed in information
     * @param array $path Split up array of path elements
     * @param string $version The requested API verison
     * @param srting $requestType The HTTP request type (GET/POST/DELETE)
     * @param srting $platform The platform for the API request
     * @return array The best-match path element
     */
    public function lookupRoute($path, $version, $requestType, $platform) {
        $pathLength = count($path);

        // Put the request type on the front of the path, it is how it is indexed
        array_unshift($path,$requestType);

        // The first element (path length) we can find on our own, but the request type will need to be hunted normally
        if ( !isset($this->dict[$pathLength]) ) {
            // There is no route with the same number of /'s as the requested route, send them on their way
            throw new SugarApiExceptionNoMethod('Could not find a route with '.$pathLength.' elements');
        }

        if ( isset($this->dict[$pathLength][$platform]) ) {
            $route = $this->lookupChildRoute($this->dict[$pathLength][$platform], $path, $version);
        }
        
        // If we didn't find a route and we are on a non-base platform, see if we find one
        // in base. Using empty here because if platform route wasn't found then $route
        // will not yet be defined.
        if (empty($route) && $platform != 'base' && isset($this->dict[$pathLength]['base']) ) {
            $route = $this->lookupChildRoute($this->dict[$pathLength]['base'], $path, $version);
        }
        
        if ($route == false) {
            throw new SugarApiExceptionNoMethod('Could not find a route with '.$pathLength.' elements');
        }

        return $route;
    }

    /**
     * Recursively digs through routes to find all options and picks the best one
     * @param array $routeBase The current list of routes you are picking between
     * @param array $path The list of path elements to search through
     * @param float $version The API version you are looking for
     * @return array The best-match path element
     */
    protected function lookupChildRoute($routeBase, $path, $version) {
        if ( count($path) == 0 ) {
            // We are at the end of the lookup, the elements here are actual paths, we need to return the best one
            return $this->getBestEnding($routeBase,$version);
        }

        // Grab the element of the path we are actually looking at
        $pathElement = array_shift($path);
        
        $bestScore = 0.0;
        $bestRoute = false;
        // Try to match it against all of the options at this level
        foreach ( $routeBase as $routeKey => $subRoute ) {
            $match = false;
            
            if ( substr($routeKey,0,1) == '<' ) {
                // It's a data-specific function match
                switch ( $routeKey ) {
                    case '<module>':
                        $match = $this->matchModule($pathElement);
                        break;
                }
            } else if ( $routeKey == '?' ) {
                // Wildcard, matches everything
                $match = true;
            } else if ( $routeKey == $pathElement ) {
                // Direct string match
                $match = true;
            }
            
            if ( $match ) {
                $route = $this->lookupChildRoute($subRoute, $path, $version);
                if ( $route != false && $route['score'] > $bestScore ) {
                    $bestRoute = $route;
                    $bestScore = $route['score'];
                }
            }
        }
        
        return $bestRoute;
    }

    /**
     * Picks the best route from the leaf-nodes discovered through lookupChildRoute
     * @param array $routes The current list of routes you are picking between
     * @param array $path The list of path elements to search through
     * @param float $version The API version you are looking for
     * @return array The best-match path element
     */
    protected function getBestEnding($routes, $version)
    {
        $bestScore = 0.0;
        $bestRoute = false;
        
        foreach ( $routes as $route ) {
            if ( isset($route['minVersion']) && $route['minVersion'] > $version ) {
                // Min version is too low, look for another route
                continue;
            }
            if ( isset($route['maxVersion']) && $route['maxVersion'] < $version ) {
                // Max version is too high, look for another route
                continue;
            }
            if ( $route['score'] > $bestScore ) {
                $bestRoute = $route;
                $bestScore = $route['score'];
            }
        }
        
        return $bestRoute;
    }

    protected function matchModule( $pathElement ) {
        return isset($GLOBALS['beanList'][$pathElement]);
    }

    public function preRegisterEndpoints() {
        $this->endpointBuffer = array();
    }
    
    public function registerEndpoints($newEndpoints, $file, $fileClass, $platform, $isCustom ) {
        if ( ! is_array($newEndpoints) ) {
            return;
        }
        
        foreach ( $newEndpoints as $endpoint ) {
            // We use the path length, platform, and request type as the first three keys to search by
            $path = $endpoint['path'];
            array_unshift($path,count($endpoint['path']),$platform,$endpoint['reqType']);
            
            $endpointScore = 0.0;
            if ( isset($endpoint['extraScore']) ) {
                $endpointScore += $endpoint['extraScore'];
            }
            if ( $isCustom ) {
                // Give some extra weight to custom endpoints so they can override built in endpoints
                $endpointScore += 0.5;
            }

            $endpoint['file'] = $file;
            $endpoint['className'] = $fileClass;

            $this->addToPathArray($this->endpointBuffer,$path,$endpoint,$endpointScore);
        }
    }

    protected function addToPathArray(&$parent,$path,$endpoint,$score) {
        if ( !isset($path[0])) {
            // We are out of elements, no need to go any further
            $endpoint['score'] = $score;
            $parent[] = $endpoint;
            
            return;
        }
        
        $currPath = array_shift($path);
        
        if ( $currPath == '?' ) {
            // This matches anything
            $myScore = 0.75;
        } else if ( $currPath[0] == '<' ) {
            // This is looking for a specfic data type
            $myScore = 1.0;
        } else {
            // This is looking for a specific string
            $myScore = 1.75;
        }


        if ( ! isset($parent[$currPath]) ) {
            $parent[$currPath] = array();
        }
        
        $this->addToPathArray($parent[$currPath],$path,$endpoint,($score+$myScore));
    }

    public function getRegisteredEndpoints() {
        return $this->endpointBuffer;
    }
}