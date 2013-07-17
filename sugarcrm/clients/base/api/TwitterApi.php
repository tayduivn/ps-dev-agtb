<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
include('include/externalAPI/Twitter/ExtAPITwitter.php');
// A simple example class
class TwitterApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'getTweets' => array(
                'reqType' => 'GET',
                'path' => array('connector','twitter', '?'),
                'pathVars' => array('connector','module', 'twitterId'),
                'method' => 'getTweets',
                'shortHelp' => 'Gets current tweets for a user',
                'longHelp' => 'include/api/help/twitter_get_help.html',
            ),
        );
    }

    /**
     * gets twitter EAPM
     * @return array|bool|ExternalAPIBase
     */
    public function getEAPM()
    {
        $externalAPIList = ExternalAPIFactory::getModuleDropDown('SugarFeed',true);
        if (!isset($externalAPIList['Twitter'])) {
            return false;
        }
        $twitterEAPM = ExternalAPIFactory::loadAPI('Twitter');

        if (!$twitterEAPM) {
            return array('error' =>'oAuth not configured');
        }
        $twitterEAPM->getConnector();

        return $twitterEAPM;
    }

    /**
     * Gets Tweets for a user via proxy call to twitter
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function getTweets($api, $args)
    {
        $args2params = array(
            'twitterId' => 'screen_name',
            'count' => 'count'
        );
        $params = array();
        foreach ($args2params as $argKey => $paramKey) {
            if (isset($args[$argKey])) {
                $params[] = $args[$argKey];
            }
        }

        if (count($params) === 0) {
            throw new SugarApiExceptionMissingParameter('Error: Missing argument.', $args);
        }

        $api = $this->getEAPM();
        if (is_array($api) && isset($api['error'])) {
            throw new SugarApiExceptionRequestMethodFailure('need OAuth', $args);
        }

        if ($api === false) {
           throw new SugarApiExceptionRequestMethodFailure($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], $args);
        }

        $result = $api->getUserTweets($args['twitterId'], 0, $args['count']);
        if (isset($result['errors'])) {
            $errorString = '';
            foreach($result['errors'] as $errorKey => $error) {
                $errorString .= $error['code'].str_replace(' ', '_', $error['message']);
            }
            throw new SugarApiExceptionRequestMethodFailure('errors_from_twitter: '.$errorString, $args);
        }
        return $result;
    }
}
