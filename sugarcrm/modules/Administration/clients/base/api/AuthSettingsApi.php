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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

class AuthSettingsApi extends SugarApi
{
    /**
     * @var Authentication\Config
     */
    private $authConfig;

    /**
     * @var array
     */
    private $administrationSettings;

    /**
     * Register endpoints
     * @return array
     */
    public function registerApiRest()
    {
        return [
            'authSettings' => [
                'reqType' => ['GET'],
                'path' => ['Administration', 'settings', 'auth'],
                'pathVars' => [''],
                'method' => 'authSettings',
                'shortHelp' => 'Fetch auth settings',
                'longHelp' => 'include/api/help/administration_idm_auth_settings.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                ],
                'minVersion' => '11.2',
            ],
            'switchOnIdmMode' => [
                'reqType' => ['POST'],
                'path' => ['Administration', 'settings', 'idmMode'],
                'pathVars' => [''],
                'method' => 'switchOnIdmMode',
                'shortHelp' => 'Turns IDM mode on',
                'longHelp' => 'include/api/help/administration_settings_post_idm_mode_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionMissingParameter',
                ],
                'minVersion' => '11.2',
            ],
            'switchOffIdmMode' => [
                'reqType' => ['DELETE'],
                'path' => ['Administration', 'settings', 'idmMode'],
                'pathVars' => [''],
                'method' => 'switchOffIdmMode',
                'shortHelp' => 'Turns IDM mode off',
                'longHelp' => 'include/api/help/administration_settings_delete_idm_mode_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                ],
                'minVersion' => '11.2',
            ],
        ];
    }

    /**
     * Fetch auth settings
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     * @throws SugarApiExceptionNotAuthorized
     */
    public function authSettings(ServiceBase $api, array $args) :array
    {
        $this->ensureMigrationEnabled();
        $this->ensureAdminUser();
        $settings = [
            'enabledProviders' => ['local'],
            'local' => $this->getLocalSettings(),
        ];

        $ldapConfig = $this->getAuthConfig()->getLdapConfig();
        if (!empty($ldapConfig)) {
            $settings['enabledProviders'][] = 'ldap';
            $settings['ldap'] = $ldapConfig;
        }
        if ('IdMSAMLAuthenticate' == $this->getAuthConfig()->get('authenticationClass', 'IdMSugarAuthenticate')) {
            $settings['enabledProviders'][] = 'saml';
            $settings['saml'] = $this->getAuthConfig()->getSAMLConfig();
        }

        return $settings;
    }

    /**
     * Turns IDM-mode on
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     * @throws SugarApiExceptionMissingParameter
     */
    public function switchOnIdmMode(ServiceBase $api, array $args) : array
    {
        $this->ensureMigrationEnabled();
        $this->ensureAdminUser();
        if (empty($args['idmMode']) || empty($args['idmMode']['enabled'])) {
            throw new SugarApiExceptionMissingParameter('IDM mode config is not provided');
        }
        $this->getAuthConfig()->setIDMMode($args['idmMode']);
        return $this->getAuthConfig()->getIDMModeConfig();
    }

    /**
     * Turns IDM-mode off
     *
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function switchOffIdmMode(ServiceBase $api, array $args) : array
    {
        $this->ensureMigrationEnabled();
        $this->ensureAdminUser();
        $this->getAuthConfig()->setIDMMode(false);
        return $this->getAuthConfig()->getIDMModeConfig();
    }

    /**
     * Configuration of local auth provider
     * @return array
     */
    private function getLocalSettings() : array
    {
        $passConfig = $this->getAuthConfig()->get('passwordsetting', []);
        $lockout = $this->getLockout();
        return [
            'password_requirements' => [
                'minimum_length' => intval($passConfig['minpwdlength']),
                'maximum_length' => intval($passConfig['maxpwdlength']),
                'require_upper' => boolval($passConfig['oneupper']),
                'require_lower' => boolval($passConfig['onelower']),
                'require_number' => boolval($passConfig['onenumber']),
                'require_special' => boolval($passConfig['onespecial']),
                'password_regex' => (string)$passConfig['customregex'],
                'regex_description' => (string)$passConfig['regexcomment'],
            ],
            'password_reset_policy' => [
                'enable' => boolVal($passConfig['forgotpasswordON']),
                'expiration' => intval($passConfig['linkexpirationtime'])
                    * intval($passConfig['linkexpirationtype'])
                    * 60,
                'require_recaptcha' => boolval($this->get('captcha_on', false)),
                'recaptcha_public' => $this->get('captcha_public_key', ''),
                'recaptcha_private' => $this->get('captcha_private_key', ''),
                'require_honeypot' => boolval($this->get('honeypot_on', false)),
            ],
            'password_expiration' => $this->getPasswordExpiration($passConfig),
            'login_lockout' => [
                'type' => $lockout->getLockType(),
                'attempt' => (int)$lockout->getFailedLoginsCount(),
                'time' => $lockout->getLockoutDurationMins() * 60,
            ],
        ];
    }

    /**
     * Format password expiration
     * @param array $passConfig
     * @return array formatted representation
     */
    private function getPasswordExpiration(array $passConfig):array
    {
        $expectedModes = [
            0 => 'disabled',
            1 => 'time',
            2 => 'upon_logins',
        ];
        $mode = $expectedModes[intval($passConfig['userexpiration'])];
        switch ($mode) {
            case 'time':
                return [
                    'time' => $passConfig['userexpirationtime'] * $passConfig['userexpirationtype'] * 3600 * 24,
                    'attempt' => 0,
                ];
            case 'upon_logins':
                return [
                    'time' => 0,
                    'attempt' => intval($passConfig['userexpirationlogin']),
                ];
            default:
            case 'disabled':
                return [
                    'time' => 0,
                    'attempt' => 0,
                ];
        }
    }

    /**
     * Returns Authentication Lockout
     *
     * @return Authentication\Lockout
     */
    protected function getLockout(): Authentication\Lockout
    {
        return new Authentication\Lockout();
    }

    /**
     * Returns Authentication Config
     *
     * @return Authentication\Config
     */
    protected function getAuthConfig() : Authentication\Config
    {
        if (is_null($this->authConfig)) {
            $this->authConfig = new Authentication\Config(SugarConfig::getInstance());
        }
        return $this->authConfig;
    }

    /**
     * Ensure current user has admin permissions
     * @throws SugarApiExceptionNotAuthorized
     */
    private function ensureAdminUser() :void
    {
        if (empty($GLOBALS['current_user']) || !$GLOBALS['current_user']->isAdmin()) {
            throw new SugarApiExceptionNotAuthorized(
                $GLOBALS['app_strings']['EXCEPTION_NOT_AUTHORIZED']
            );
        }
    }

    /**
     * @throws SugarApiExceptionNotFound
     */
    private function ensureMigrationEnabled(): void
    {
        if (empty($GLOBALS['sugar_config']['idmMigration'])) {
            throw new SugarApiExceptionNotFound();
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    protected function get(string $key, $default = null)
    {
        if (is_null($this->administrationSettings)) {
            $administration = new Administration();
            $administration->retrieveSettings();
            $this->administrationSettings = $administration->settings;
        }

        if (array_key_exists($key, $this->administrationSettings)) {
            return $this->administrationSettings[$key];
        } else {
            return $default;
        }
    }
}
