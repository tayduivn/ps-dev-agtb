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

class HelpApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'getHelp' => array(
                'reqType' => 'GET',
                'path' => array('help'),
                'pathVars' => array(''),
                'method' => 'getHelp',
                'shortHelp' => 'Shows Help information',
                'longHelp' => 'include/api/help/getHelp.html',
                'rawReply' => true,
                // Everyone needs some help sometimes.
                'noLoginRequired' => true,
            ),
        );
    }

    public function getHelp($api, $args) {
        // This function needs to peer into the deep, twisted soul of the RestServiceDictionary
        $dir = $api->dict->dict;
        
        $endpointList = array();
        foreach ( $dir as $startDepth => $dirPart ) {
            $endpointList = array_merge($endpointList, $this->getEndpoints($dirPart,$startDepth));
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
        }
        // Sort the endpoint list
        usort($endpointList,array('HelpApi','cmpEndpoints'));

        ob_start();
        require('include/api/help/extras/helpList.php');
        $endpointHtml = ob_get_clean();

        header('Content Type: text/html');
        return $endpointHtml;
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
}