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

class AuthApi extends SugarApi
{
    public function registerApiRest()
    {
        return [
            'getAuthInfo' => [
                'reqType'   => 'GET',
                'path' => array('EAPM', 'auth'),
                'pathVars' => array('module', ''),
                'method'    => 'getAuthInfo',
                'shortHelp' => 'Get auth info for an application',
                'longHelp'  => 'include/api/help/module_get_help.html',
            ],
        ];
    }

    /**
     * Gets auth url for an application.
     *
     * @param ServiceBase $api The API class of the request
     * @param array $args The arguments array passed in from the API
     * @return string Auth URL
     * @throws SugarApiExceptionNotFound
     */
    public function getAuthInfo(ServiceBase $api, array $args): array
    {
        if (!isset($args['application'])) {
            throw new SugarApiExceptionNotFound('Application not found');
        }
        $api = $this->getExternalApi($args['application']);
        if (empty($api)) {
            throw new SugarApiExceptionNotFound('External API not found');
        }
        $client = $api->getClient();
        $authUrl = $client->createAuthUrl();
        $data = [
            'auth_url' => $authUrl,
        ];
        return $data;
    }

    /**
     * Gets external api object for an application.
     *
     * @param string $application
     * @return ExternalAPIBase|NULL
     */
    public function getExternalApi(string $application): ?ExternalAPIBase
    {
        return ExternalAPIFactory::loadAPI($application, true);
    }
}
