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

namespace Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth;

use League\OAuth2\Client\Provider\GenericProvider as BasicGenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class GenericProvider extends BasicGenericProvider
{
    /**
     * @inheritdoc
     */
    protected function getAccessTokenOptions(array $params)
    {
        unset($params['client_id'], $params['client_secret']);

        $options = parent::getAccessTokenOptions($params);
        $options['headers']['Authorization'] = $this->getHttpBasicAuthHeader();

        return $options;
    }

    /**
     * Create HTTP Basic auth string
     * @return string
     */
    protected function getHttpBasicAuthHeader()
    {
        return 'Basic ' . base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret));
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredOptions()
    {
        return array_merge(parent::getRequiredOptions(), ['clientId', 'clientSecret']);
    }

    /**
     * Introspect token and return resource owner details
     * @param AccessToken $token
     * @throws \RuntimeException
     * @return string
     */
    public function introspectToken(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);
        $options = [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'Authorization' => $this->getHttpBasicAuthHeader(),
            ],
            'body' => $this->buildQueryString(['token' => $token->getToken()]),
        ];

        $request = $this->getRequestFactory()->getRequestWithOptions(self::METHOD_POST, $url, $options);
        return $this->getParsedResponse($request);
    }
}
