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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

/**
 * Configuration glue for IdM
 */
class Config
{
    /**
     * @var \SugarConfig
     */
    protected $sugarConfig;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param \SugarConfig $sugarConfig
     */
    public function __construct(\SugarConfig $sugarConfig)
    {
        $this->sugarConfig = $sugarConfig;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $this->sugarConfig->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Builds proper configuration suitable for SAMLAuthenticationProvider
     *
     * @return array
     */
    public function getSAMLConfig()
    {
        $defaultConfig = $this->getSAMLDefaultConfig();

        $returnQueryVars = $this->get('SAML_returnQueryVars', []);
        $returnPath = '/index.php';
        $returnQueryVars['module'] = 'Users';
        $returnQueryVars['action'] = 'Authenticate';
        $returnQueryVars['dataOnly'] = 1;
        if (!empty($returnQueryVars['platform'])
            && ($returnQueryVars['platform'] == 'base')
            && !empty($this->get('SAML_SAME_WINDOW'))
        ) {
            unset($returnQueryVars['dataOnly']);
        }
        if (!empty($returnQueryVars) && is_array($returnQueryVars)) {
            $returnPath .= '?'.urlencode(http_build_query($returnQueryVars));
        }
        $defaultConfig['sp']['assertionConsumerService']['url'] = rtrim($this->get('site_url'), '/') . $returnPath;

        return array_replace_recursive($defaultConfig, $this->get('SAML', [])); //update with values from config
    }

    /**
     * Get default config for php-saml library
     *
     * @return array
     */
    protected function getSAMLDefaultConfig()
    {
        return [
            'strict' => false,
            'debug' => false,
            'sp' => [
                'entityId' => $this->get('SAML_issuer', 'php-saml'), // BC mode, this should be an URL to metadata
                'assertionConsumerService' => [
                    // url - see below
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => rtrim($this->get('site_url'), '/') . '/index.php?module=Users&action=Logout',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'NameIDFormat' =>\OneLogin_Saml2_Constants::NAMEID_EMAIL_ADDRESS,
                'x509cert' => $this->get('SAML_REQUEST_SIGNING_X509', ''),
                'privateKey' => $this->get('SAML_REQUEST_SIGNING_PKEY', ''),
            ],

            'idp' => array (
                'entityId' => $this->get('SAML_loginurl'), // BC mode, this should be an URL to metadata
                'singleSignOnService' => [
                    'url' => $this->get('SAML_loginurl'),
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'singleLogoutService' => [
                    'url' => $this->get('SAML_SLO'),
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'x509cert' => $this->get('SAML_X509Cert'),
            ),

            'security' => [
                'authnRequestsSigned' => (bool)$this->get('SAML_REQUEST_SIGNING_PKEY'),
                'signatureAlgorithm' => $this->get('SAML_REQUEST_SIGNING_METHOD', \XMLSecurityKey::RSA_SHA256),
            ],
        ];
    }
}
