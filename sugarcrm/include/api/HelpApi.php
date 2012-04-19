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
            $endpointList[$idx]['fullPath'] = '/'.implode('/',$endpoint['path']);
        }
        // Sort the endpoint list
        usort($endpointList,array('HelpApi','cmpEndpoints'));

        ob_start();
        require('include/api/help/extras/helpList.php');
        $endpointHtml = ob_get_clean();
/*
        $endpointHtml = file_get_contents('include/api/help/extras/header.html');
        $endpointHtml .= '<table id="endpointList" border=1 cellspacing=0 cellpadding=2>';
        foreach ( $endpointList as $i => $endpoint ) {
            if ( !isset($endpoint['shortHelp']) ) {
                // Hidden, for some reason
                continue;
            }
            $endpointHtml .= '<tr id="endpoint_'.$i.'"><td class="showHide">[+]</td><td class="reqType">'.htmlspecialchars($endpoint['reqType']).'</td><td class="fullPath">'.htmlspecialchars($endpoint['fullPath']).'</td><td class="shortHelp">'.htmlspecialchars($endpoint['shortHelp']).'</td><td class="score">'.sprintf("%.02f",$endpoint['score']).'</td></tr>';
            $endpointHtml .= '<tr id="endpoint_'.$i.'_full" class="hidden"><td class="filler">&nbsp;</td><td colspan=3>';
            $endpointHtml .=
            $endpointHtml .= '</td></tr>';
        }
        $endpointHtml .= '</table>';
        $endpointHtml .= file_get_contents('include/api/help/extras/footer.html');
*/
        header('Content Type: text/html');
        return $endpointHtml;
    }

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

    public static function cmpEndpoints($endpoint1, $endpoint2) {
        return ( $endpoint1['fullPath'] > $endpoint2['fullPath'] ) ? +1 : -1;
    }
}