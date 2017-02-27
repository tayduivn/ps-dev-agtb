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

use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Sugarcrm\IdentityProvider\Encoder\EncoderBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LdapUserChecker;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;

use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLdapUserProvider;
use Sugarcrm\IdentityProvider\Authentication\Provider\LdapAuthenticationProvider;

use Sugarcrm\IdentityProvider\Authentication\Provider\SAMLAuthenticationProvider;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarSAMLUserProvider;

class AuthProviderBasicManagerBuilder
{
    const PROVIDER_KEY_LOCAL = 'SugarLocalProvider';
    const PROVIDER_KEY_LDAP = 'SugarLdapProvider';
    /**
     * Encoders config
     * @var array|null
     */
    protected $encoderConfig;

    /**
     * ldap config
     * @var array|null
     */
    protected $ldapConfig;

    /**
     * saml config
     * @var array|null
     */
    protected $samlConfig;

    /**
     * __construct
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->encoderConfig = $config->get('passwordHash', []);
        if (!empty($this->encoderConfig)) {
            $this->encoderConfig = ['passwordHash' => $this->encoderConfig];
        }
        $this->ldapConfig = $config->getLdapConfig();
        $this->samlConfig = $config->getSAMLConfig();
    }

    /**
     * build all available providers
     *
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return AuthenticationProviderManager
     */
    public function buildAuthProviders(EventDispatcherInterface $eventDispatcher = null)
    {
        $manager = new AuthenticationProviderManager(array_filter([
            $this->getLocalAuthProvider(),
            $this->getLdapAuthProvider(),
            $this->getSamlAuthIDP(),
        ]));

        if (!$eventDispatcher) {
            $eventDispatcher = $this->getAuthenticationEventDispatcher();
        }
        if ($eventDispatcher) {
            $manager->setEventDispatcher($eventDispatcher);
        }
        return $manager;
    }

    /**
     * return local provider
     * @return DaoAuthenticationProvider
     */
    protected function getLocalAuthProvider()
    {
        $encoderFactory = new EncoderFactory([
            User::class => (new EncoderBuilder())->buildEncoder($this->encoderConfig),
        ]);

        return new DaoAuthenticationProvider(
            new SugarLocalUserProvider(),
            new SugarUserChecker(new Lockout()),
            self::PROVIDER_KEY_LOCAL,
            $encoderFactory
        );
    }

    /**
     * retun ldap provider
     * @return null|LdapAuthenticationProvider
     */
    protected function getLdapAuthProvider()
    {
        if (empty($this->ldapConfig)) {
            return null;
        }

        $userChecker =new LdapUserChecker(
            new Lockout(),
            new SugarLocalUserProvider(),
            $this->ldapConfig
        );

        $adapter = new Adapter($this->ldapConfig['adapter_config']);
        if (!empty($this->ldapConfig['adapter_connection_protocol_version'])) {
            $adapter->getConnection()
                ->setOption('PROTOCOL_VERSION', $this->ldapConfig['adapter_connection_protocol_version']);
        }

        $ldap = new Ldap($adapter);

        $userProvider = new SugarLdapUserProvider(
            $ldap,
            $this->ldapConfig['baseDn'],
            $this->ldapConfig['searchDn'],
            $this->ldapConfig['searchPassword'],
            User::getDefaultRoles(),
            $this->ldapConfig['uidKey'],
            $this->ldapConfig['filter']
        );

        return new LdapAuthenticationProvider(
            $userProvider,
            $userChecker,
            self::PROVIDER_KEY_LDAP,
            $ldap,
            $this->ldapConfig['dnString'],
            true,
            $this->ldapConfig
        );
    }

    /**
     * return saml auth
     * @return SAMLAuthenticationProvider|null
     */
    protected function getSamlAuthIDP()
    {
        if (empty($this->samlConfig)) {
            return null;
        }

        return new SAMLAuthenticationProvider(
            $this->samlConfig,
            new SugarSAMLUserProvider(new SugarLocalUserProvider())
        );
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getAuthenticationEventDispatcher()
    {
    }
}
