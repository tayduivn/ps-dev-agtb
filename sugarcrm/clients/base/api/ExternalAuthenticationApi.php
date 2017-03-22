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

/**
 * Provides api methods to work with external authentication.
 *
 * Class ExternalAuthenticationApi
 */
class ExternalAuthenticationApi extends SugarApi
{
    public function registerApiRest()
    {
        return [
            'getLoginUrl' => [
                'reqType' => 'GET',
                'path' => ['externalAuthentication', 'getLoginUrl'],
                'pathVars' => [],
                'method' => 'getLoginUrl',
                'shortHelp' => 'Gets a valid login URL for external auth sources',
                'longHelp' => 'include/api/help/external_authentication_get_login_url_help.html',
                'noLoginRequired' => true,
                'noEtag' => true,
                'ignoreMetaHash' => true,
                'exceptions' => [
                    'SugarApiExceptionError',
                    'SugarApiExceptionMissingParameter',
                ],
            ],
        ];
    }

    /**
     * Returns a valid login url for external authentication resource.
     *
     * @param ServiceBase $api
     * @param array $args
     * @return string
     * @throws SugarApiExceptionError
     * @throws SugarApiExceptionMissingParameter
     */
    public function getLoginUrl(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, ['platform']);

        $config = $this->getSugarConfig();
        if (!empty($config->get('authenticationClass'))) {
            $auth = $this->getAuthenticationController($config->get('authenticationClass'));

            if ($auth->isExternal()) {
                return [
                    'url' => $auth->getLoginUrl(['platform' => $args['platform']]),
                ];
            }
        }
        throw new \SugarApiExceptionError('Cannot generate a valid login url for this authentication type');
    }

    /**
     * Gets AuthenticationController instance.
     *
     * @param $authenticationClass
     * @return AuthenticationController
     */
    protected function getAuthenticationController($authenticationClass)
    {
        return new AuthenticationController($authenticationClass);
    }

    /**
     * Gets SugarConfig instance.
     *
     * @return null|SugarConfig
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }
}
