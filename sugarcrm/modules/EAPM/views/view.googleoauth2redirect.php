<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class EAPMViewGoogleOauth2Redirect extends SugarView
{
    /**
     * @var string $context the context in which this redirect URL was called
     */
    private $context;

    /**
     * @var ExternalAPIBase $api the API object used to communicate with Google
     */
    private $api;

    /**
     * {@inheritDoc}
     *
     * @param array $params Ignored
     */
    public function process($params = array())
    {
        global $sugar_config;
        $this->context = $_GET['state'] ?? '';

        $tokenData = $this->authenticate();
        $response = $this->buildResponse($tokenData);

        $this->ss->assign('response', $response);
        $this->ss->assign('siteUrl', $sugar_config['site_url']);
        $this->ss->display('modules/EAPM/tpls/GoogleOauth2Redirect.tpl');
    }

    /**
     * Authenticates a Google authorization code with Google servers, storing
     * any resulting token information in the EAPM table
     *
     * @return bool|string
     */
    protected function authenticate()
    {
        if (!isset($_GET['code'])) {
            return false;
        }

        switch ($this->context) {
            case 'email':
                $this->api = new ExtAPIGoogleEmail();
                break;
            default:
                $this->api = new ExtAPIGoogle();
                break;
        }
        return $this->api->authenticate($_GET['code']);
    }

    /**
     * Parses the authentication token data received from Google and builds a
     * response object that will be sent to the frontend
     *
     * @param $tokenData
     * @return array
     */
    protected function buildResponse($tokenData) : array
    {
        switch ($this->context) {
            case 'email':
                $response = $this->buildEmailContextResponse($tokenData);
                break;
            default:
                $response = $this->buildBasicResponse($tokenData);
                break;
        }
        return $response;
    }

    /**
     * Constructs a basic response object that indicates the success status of
     * the token authentication
     *
     * @param string $tokenJSON the JSON token string received from Google
     * @return array
     */
    protected function buildBasicResponse($tokenJSON)
    {
        if (empty($tokenJSON)) {
            return array(
                'result' => false,
            );
        }

        // Build a basic response object indicating authentication success
        $token = json_decode($tokenJSON, true);
        $response = array(
            'result' => true,
            'hasRefreshToken' => isset($token['refresh_token']),
            'dataSource' => 'googleOauthRedirect',
        );

        return $response;
    }

    /**
     * Constructs a response object that includes additional information about
     * the EAPM bean created and the email address of the authorized account
     *
     * @param $authResult
     * @return array
     */
    protected function buildEmailContextResponse($authResult)
    {
        $response = $this->buildBasicResponse($authResult['token'] ?? null);
        $response['dataSource'] = 'googleEmailRedirect';
        if (!empty($response['result'])) {
            $eapmId = $authResult['eapmId'] ?? null;
            $response['eapmId'] = $eapmId;
            $emailAddress = $this->api->getEmailAddress($eapmId);
            if (!empty($emailAddress)) {
                $response['emailAddress'] = $emailAddress;
            }
        }
        return $response;
    }
}
