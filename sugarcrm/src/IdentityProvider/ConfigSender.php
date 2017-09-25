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

namespace Sugarcrm\Sugarcrm\IdentityProvider;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Guzzle\Http\ClientInterface as HttpClientInterface;

/**
 * Configuration sender to Sugar IdP
 */
class ConfigSender
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient = null;

    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    /**
     * Send config.
     * @throws \Exception
     */
    public function send()
    {
        if (empty($this->config->get('oidc_oauth'))) {
            return;
        }

        $headers = [
            //TODO need add Authorization Bearer oauth-token-value if oauth-token-value is present
            'Authorization' => 'Basic ' . base64_encode($this->config->get('oidc_oauth')['clientid:clientSecrect']),
        ];
        $request = $this->httpClient->post($this->generateUrl(), $headers, json_encode($this->generateMessage()));
        $response = $request->send();
        if (!$response->isSuccessful()) {
            throw new \RuntimeException('Config was not sent to IdP');
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function generateUrl()
    {
        if (empty($this->config->get('oidc_oauth')['idpUrl'])) {
            throw new \RuntimeException('Identity Provider URL is not set');
        }
        return rtrim($this->config->get('oidc_oauth')['idpUrl'], '/') . '/config';
    }

    /**
     * Generate config.
     * @return array
     */
    protected function generateConfig()
    {
        $config = [
            'enabledProviders' => [],
            'local' => [],
            'saml' => $this->config->getSAMLConfig(),
            'ldap' => $this->config->getLdapConfig(),
        ];
        $config['enabledProviders'][] = 'local';
        if (!empty($config['saml'])) {
            $config['enabledProviders'][] = 'saml';
        }
        if (!empty($config['ldap'])) {
            $config['enabledProviders'][] = 'ldap';
        }
        return $config;
    }

    /**
     * Generate sent message array.
     * @return array
     */
    protected function generateMessage()
    {
        $data = [
            'instance' => $this->config->get('site_url'),
            'config' => $this->generateConfig(),
        ];

        $message = [
            'data' => $data,
            'signature' => '', //TODO need make signature or mack other way to check sender
        ];
        return $message;
    }
}
