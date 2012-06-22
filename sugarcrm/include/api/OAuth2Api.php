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

require_once('include/SugarOAuth2Server.php');

class OAuth2Api extends SugarApi {
    public function registerApiRest() {
        return array(
            'token' => array(
                'reqType' => 'POST',
                'path' => array('oauth2','token'),
                'pathVars' => array('',''),
                'method' => 'token',
                'shortHelp' => 'OAuth2 token requests.',
                'longHelp' => 'include/api/help/oauth2_token.html',
                'rawReply' => true, // The OAuth server sets specific headers and outputs in the exact format requested by the spec, so we don't want to go around messing with it.
                'noLoginRequired' => true,
            ),
            'oauth_logout' => array(
                'reqType' => 'POST',
                'path' => array('oauth2','logout'),
                'pathVars' => array('',''),
                'method' => 'logout',
                'shortHelp' => 'OAuth2 logout.',
                'longHelp' => 'include/api/help/oauth2_logout.html',
            ),
        );
    }

    public function token($api, $args) {
        $oauth2Server = SugarOAuth2Server::getOAuth2Server();

        $oauth2Server->grantAccessToken($args);
        
        // grantAccessToken directly echo's (BAD), but it's a 3rd party library, so what are you going to do?
        return '';
    }

    public function logout($api, $args) {
        $oauth2Server = SugarOAuth2Server::getOAuth2Server();

        if ( isset($args['refresh_token']) ) {
            // Nuke the refresh token as well.
            // No security checks needed here to make sure the refresh token is theirs, 
            // because if someone else has your refresh token logging out is the nicest possible thing they could do.
            $oauth2Server->storage->unsetRefreshToken($args['refresh_token']);
        }

        // The OAuth access token is actually just a session, so we can nuke that here.
        $_SESSION = array();
        session_regenerate_id(true);

        return array('success'=>true);
    }

}