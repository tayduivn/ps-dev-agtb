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
     * @var \Administration
     */
    protected $ldapSettings;

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
        return array_replace_recursive($defaultConfig, $this->get('SAML', [])); //update with values from config
    }

    /**
     * Get default config for php-saml library
     *
     * @return array
     */
    protected function getSAMLDefaultConfig()
    {
        $siteUrl = rtrim($this->get('site_url'), '/');
        $acsUrl = sprintf('%s/index.php?%s', $siteUrl, htmlentities('module=Users&action=Authenticate', ENT_XML1));
        $sloUrl = sprintf('%s/index.php?%s', $siteUrl, htmlentities('module=Users&action=Logout', ENT_XML1));
        return [
            'strict' => false,
            'debug' => false,
            'sp' => [
                'entityId' => $this->get('SAML_issuer', 'php-saml'), // BC mode, this should be an URL to metadata
                'assertionConsumerService' => [
                    'url' => $acsUrl,
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => $sloUrl,
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

    /**
     * returns mapped mango ldap config
     * @return array
     */
    public function getLdapConfig()
    {
        if (!$this->isLdapEnabled()) {
            return [];
        }

        $ldap = [
            'adapter_config' => [
                'host' => $this->getLdapSetting('ldap_hostname', '127.0.0.1'),
                'port' => $this->getLdapSetting('ldap_port', 389),
            ],
            'adapter_connection_protocol_version' => 3,
            'baseDn' => $this->getLdapSetting('ldap_base_dn', ''),
            'uidKey' => $this->getLdapSetting('ldap_login_attr', ''),
            'filter' => '({uid_key}={username})' . $this->getLdapSetting('ldap_login_filter', ''),
            'dnString' => null,
            'entryAttribute' => null,
            'autoCreateUser' => $this->getLdapSetting('ldap_auto_create_users', false),
        ];
        if (!empty($this->getLdapSetting('ldap_authentication'))) {
            $ldap['searchDn'] = $this->getLdapSetting('ldap_admin_user');
            $ldap['searchPassword'] = $this->getLdapSetting('ldap_admin_password');
        }

        if (!empty($this->getLdapSetting('ldap_group'))) {
            $ldap['groupMembership'] = true;
            $ldap['groupDn'] = sprintf(
                '%s,%s',
                $this->getLdapSetting('ldap_group_name'),
                $this->getLdapSetting('ldap_group_dn')
            );
            $ldap['groupAttribute'] = $this->getLdapSetting('ldap_group_attr');
            $ldap['includeUserDN'] = $this->getLdapSetting('ldap_group_attr_req_dn', false);
        }

        return array_merge($this->getLdapDefaultConfig(), $ldap);
    }

    /**
     * Is LDAP enabled?
     * @return bool
     */
    protected function isLdapEnabled()
    {
        $system = \Administration::getSettings('system');
        return !empty($system->settings['system_ldap_enabled']);
    }

    /**
     * return settings value from mango ldap settings
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function getLdapSetting($key, $default = null)
    {
        if (!$this->ldapSettings) {
            $this->ldapSettings = \Administration::getSettings('ldap');
        }
        return !empty($this->ldapSettings->settings[$key]) ? $this->ldapSettings->settings[$key] : $default;
    }

    /**
     * return default config for ldap
     * @see modules/Users/authentication/LDAPAuthenticate/LDAPConfigs/default.php
     * @return array
     */
    protected function getLdapDefaultConfig()
    {
        return [
            'user' => [
                'mapping' => [
                    'givenName' => 'first_name',
                    'sn' => 'last_name',
                    'mail' => 'email1',
                    'telephoneNumber' => 'phone_work',
                    'facsimileTelephoneNumber' => 'phone_fax',
                    'mobile' => 'phone_mobile',
                    'street' => 'address_street',
                    'l' => 'address_city',
                    'st' => 'address_state',
                    'postalCode' => 'address_postalcode',
                    'c' => 'address_country',
                ],
            ],
        ];
    }
}
