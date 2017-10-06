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
use GuzzleHttp;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GenericProvider extends BasicGenericProvider
{
    /**
     * Adds HttpClient with retry policy.
     *
     * @inheritdoc
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (!array_key_exists('httpClient', $collaborators)) {
            $collaborators['httpClient'] = $this->createHttpClient();
        }
        parent::__construct($options, $collaborators);
    }

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
     * Allow to use specific handler.
     *
     * @inheritdoc
     */
    protected function getAllowedClientOptions(array $options)
    {
        return array_merge(parent::getAllowedClientOptions($options), ['handler']);
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

    /**
     * Specifies conditions of how should retry of sending the request should be performed.
     *
     * @param int $maxRetries Maximum number of retries to get the response.
     * @return \Closure
     */
    public function retryDecider($maxRetries)
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) use ($maxRetries) {
            if ($retries >= $maxRetries) {
                return false;
            }

            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            return false;
        };
    }

    /**
     * Get retry delay strategy based on config value.
     *
     * @param array $config OIDC http_client config.
     * @return \Closure
     */
    public function getDelayStrategy($config)
    {
        $value = (isset($config['http_client']['delay_strategy'])) ?
            $config['http_client']['delay_strategy'] : 'linear';

        switch ($value) {
            case 'exponential':
                return $this->retryDelayExponential();

            case 'linear':
            default:
                return $this->retryDelayLinear();
        }
    }

    /**
     * Increases delay time between http request retries by 1 second.
     *
     * @return \Closure that returns milliseconds of delay.
     */
    public function retryDelayLinear()
    {
        return function ($retries) {
            return 1000 * $retries;
        };
    }

    /**
     * Increases delay time between http request retries by 2^n-1 where n is the retry attempt counter.
     *
     * @return \Closure that returns milliseconds of delay.
     */
    public function retryDelayExponential()
    {
        return function ($retries) {
            return (int) pow(2, $retries - 1) * 1000;
        };
    }

    /**
     * Creates HttpClient with retry policy.
     * @return HttpClient
     */
    protected function createHttpClient()
    {
        $config = $this->getSugarConfig()->get('oidc_oauth', []);

        $retryCount = (isset($config['http_client']['retry_count'])) ? (int) $config['http_client']['retry_count'] : 0;

        $handlerStack = HandlerStack::create(GuzzleHttp\choose_handler());
        $handlerStack->push(
            Middleware::retry($this->retryDecider($retryCount), $this->getDelayStrategy($config)),
            'retryDecider'
        );

        $options['handler'] = $handlerStack;

        return new HttpClient(
            array_intersect_key($options, array_flip($this->getAllowedClientOptions($options)))
        );
    }

    /**
     * Get Sugar config.
     * @return \SugarConfig
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }
}
