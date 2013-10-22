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
            'getCurrentUser' => array(
                'reqType' => 'GET',
                'path' => array('connector','twitter', 'currentUser'),
                'pathVars' => array('connector','module', 'twitterId'),
                'method' => 'getCurrentUser',
                'shortHelp' => 'Gets current tweets for a user',
                'longHelp' => 'include/api/help/twitter_get_help.html',
            ),
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
        // ignore auth and load to just check if connector configured
        $twitterEAPM = ExternalAPIFactory::loadAPI('Twitter', true);

        if (!$twitterEAPM) {
            $source = SourceFactory::getSource('ext_rest_twitter');
            if ($source && $source->hasTestingEnabled()) {
                try {
                    if (!$source->test()) {
                        return array('error' =>'ERROR_NEED_OAUTH');
                    }
                } catch (Exception $e) {
                    return array('error' =>'ERROR_NEED_OAUTH');
                }
            }
            return array('error' =>'ERROR_NEED_OAUTH');
        }

        $twitterEAPM->getConnector();

        $eapmBean = EAPM::getLoginInfo('Twitter');

        if (empty($eapmBean->id)) {
            return array('error' =>'ERROR_NEED_AUTHORIZE');
        }

        //return a fully authed EAPM
        $twitterEAPM = ExternalAPIFactory::loadAPI('Twitter');
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

        $extApi = $this->getEAPM();

        if (is_array($extApi) && isset($extApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extApi['error']);
        }

        if ($extApi === false) {
           throw new SugarApiExceptionRequestMethodFailure($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], $args);
        }

        $result = $extApi->getUserTweets($args['twitterId'], 0, $args['count']);
        if (isset($result['errors'])) {
            $errorString = '';
            foreach($result['errors'] as $errorKey => $error) {
                if ($error['code'] === 34) {
                    throw new SugarApiExceptionNotFound('errors_from_twitter: '.$errorString, $args);
                }
                $errorString .= $error['code'].str_replace(' ', '_', $error['message']);
            }
            throw new SugarApiExceptionRequestMethodFailure('errors_from_twitter: '.$errorString, $args);
        }
        return $result;
    }

    /**
     * Gets Tweets for a user via proxy call to twitter
     * @param $api
     * @param $args
     * @return mixed
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function getCurrentUser($api, $args)
    {
        $extApi = $this->getEAPM();
        if (is_array($extApi) && isset($extApi['error'])) {
            throw new SugarApiExceptionRequestMethodFailure(null, $args, null, 424, $extApi['error']);
        }

        if ($extApi === false) {
            throw new SugarApiExceptionRequestMethodFailure($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], $args);
        }

        $result = $extApi->getCurrentUserInfo();
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
