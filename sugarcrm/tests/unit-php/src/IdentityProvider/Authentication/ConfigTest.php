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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $sugarConfig->expects($this->any())
            ->method('get')
            ->willReturn('sugar_config_value');
        $config = new Config($sugarConfig);
        $this->assertEquals('sugar_config_value', $config->get('some_key'), 'Proxying to sugar config');
    }

    public function getSAMLConfigDataProvider()
    {
        return [
            'no override in config' => [
                [
                    'default' => 'config',
                ],
                ['default' => 'config'],
                [],
                [],
            ],
            'saml config provided' => [
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                ['default' => 'config'],
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                [],
            ],
            'saml config and sugar custom settings provided' => [
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
                ['default' => 'config'],
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'foo' => 'bar',
                    ],
                ],
                [
                    'sp' => [
                        'sugarCustom' => [
                            'useXML' => true,
                            'id' => 'first_name',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $expectedConfig
     * @param array $defaultConfig
     * @param array $configValues
     * @param array $customSettings
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigDataProvider
     */
    public function testGetSAMLConfig(
        array $expectedConfig,
        array $defaultConfig,
        array $configValues,
        array $customSettings
    ) {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSAMLDefaultConfig', 'getSugarCustomSAMLSettings'])
            ->getMock();
        $config->method('getSugarCustomSAMLSettings')->willReturn($customSettings);
        $config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                ['SAML', []]
            )
            ->willReturnOnConsecutiveCalls(
                $configValues
            );
        $config->expects($this->once())
            ->method('getSAMLDefaultConfig')
            ->willReturn($defaultConfig);
        $samlConfig = $config->getSAMLConfig();
        $this->assertEquals($expectedConfig, $samlConfig);
    }

    /**
     * Checks default config when it created from SugarCRM config values.
     *
     * @covers ::getSAMLConfig
     */
    public function testGetSAMLDefaultConfig()
    {
        $expectedConfig = [
            'strict' => false,
            'debug' => false,
            'sp' => [
                'entityId' => 'SAML_issuer',
                'assertionConsumerService' => [
                    'url' => 'site_url/index.php?module=Users&action=Authenticate',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => 'site_url/index.php?module=Users&action=Logout',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'NameIDFormat' => \OneLogin_Saml2_Constants::NAMEID_EMAIL_ADDRESS,
                'x509cert' => 'SAML_REQUEST_SIGNING_X509',
                'privateKey' => 'SAML_REQUEST_SIGNING_PKEY',
                'provisionUser' => 'SAML_provisionUser',
            ],

            'idp' => [
                'entityId' => 'SAML_idp_entityId',
                'singleSignOnService' => [
                    'url' => 'SAML_loginurl',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'singleLogoutService' => [
                    'url' => 'SAML_SLO',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_REDIRECT,
                ],
                'x509cert' => 'SAML_X509Cert',
            ],

            'security' => [
                'authnRequestsSigned' => true,
                'logoutRequestSigned' => true,
                'logoutResponseSigned' => true,
                'signatureAlgorithm' => 'SAML_REQUEST_SIGNING_METHOD',
                'validateRequestId' => true,
            ],
        ];
        $config = $this->getMockBuilder(Config::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['get', 'getSugarCustomSAMLSettings'])
                       ->getMock();
        $config->method('getSugarCustomSAMLSettings')->willReturn([]);
        $config->method('get')
               ->willReturnMap(
                   [
                       ['SAML_request_signing_pkey', null, 'SAML_REQUEST_SIGNING_PKEY'],
                       ['site_url', null, 'site_url'],
                       ['SAML_loginurl', null, 'SAML_loginurl'],
                       ['SAML_issuer', 'php-saml', 'SAML_issuer'],
                       ['SAML_request_signing_x509', '', 'SAML_REQUEST_SIGNING_X509'],
                       ['SAML_request_signing_x509', null, 'SAML_REQUEST_SIGNING_X509'],
                       ['SAML_request_signing_pkey', '', 'SAML_REQUEST_SIGNING_PKEY'],
                       ['SAML_provisionUser', true, 'SAML_provisionUser'],
                       ['SAML_idp_entityId', 'SAML_loginurl', 'SAML_idp_entityId'],
                       ['SAML_SLO', null, 'SAML_SLO'],
                       ['SAML_X509Cert', null, 'SAML_X509Cert'],
                       [
                           'SAML_request_signing_method',
                           \XMLSecurityKey::RSA_SHA256,
                           'SAML_REQUEST_SIGNING_METHOD',
                       ],
                       ['SAML', [], []],
                       ['SAML_sign_authn', false, true],
                       ['SAML_sign_logout_request', false, true],
                       ['SAML_sign_logout_response', false, true],
                       ['saml.validate_request_id', false, true],
                   ]
               );
        $this->assertEquals($expectedConfig, $config->getSAMLConfig());
    }

    public function getSAMLConfigIdpStoredValuesProperlyEscapeProvider()
    {
        return [
            ['https://test.local', 'https://test.local'],
            ['https://test.local?idp1=test', 'https://test.local?idp1=test'],
            ['https://test.local/idp=test&idp1=test', 'https://test.local/idp=test&idp1=test'],
            ['https://test.local/idp=test&amp;idp1=test', 'https://test.local/idp=test&idp1=test'],
        ];
    }

    /**
     * @param string $storedValue
     * @param string $expectedValue
     *
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigIdpStoredValuesProperlyEscapeProvider
     */
    public function testGetSAMLConfigIdpStoredValuesProperlyEscape($storedValue, $expectedValue)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSugarCustomSAMLSettings'])
            ->getMock();

        $config->expects($this->any())->method('get')
            ->will($this->returnValueMap(
                [
                    ['SAML_loginurl', null, $storedValue],
                    ['SAML_SLO', null, $storedValue],
                    ['SAML_issuer', 'php-saml', $storedValue],
                    ['SAML_idp_entityId', $expectedValue, $storedValue],
                    ['SAML', [], []],
                ]
            ));

        $config->expects($this->any())->method('getSugarCustomSAMLSettings')->willReturn([]);

        $samlConfig = $config->getSAMLConfig();

        $this->assertArrayHasKey('idp', $samlConfig);
        $this->assertArrayHasKey('singleSignOnService', $samlConfig['idp']);
        $this->assertArrayHasKey('singleLogoutService', $samlConfig['idp']);
        $this->assertArrayHasKey('entityId', $samlConfig['idp']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleSignOnService']);
        $this->assertArrayHasKey('url', $samlConfig['idp']['singleLogoutService']);

        $this->assertArrayHasKey('sp', $samlConfig);
        $this->assertArrayHasKey('entityId', $samlConfig['sp']);

        $this->assertEquals($expectedValue, $samlConfig['idp']['singleSignOnService']['url'], 'SSO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['singleLogoutService']['url'], 'SLO url invalid');
        $this->assertEquals($expectedValue, $samlConfig['idp']['entityId'], 'IdP Entity ID invalid');
        $this->assertEquals($expectedValue, $samlConfig['sp']['entityId'], 'SugarCRM Entity ID invalid');
    }

    /**
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfigNoLdap()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(false);

        $this->assertEmpty($config->getLdapConfig());
    }

    public function getLdapConfigDataProvider()
    {
        return [
            'regular LDAP' => [
                [
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
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => '389',
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => 'none',
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_hostname', '127.0.0.1', '127.0.0.1'],
                    ['ldap_port', 389, 389],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
            'LDAP over SSL' => [
                [
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
                    'adapter_config' => [
                        'host' => '127.0.0.1',
                        'port' => 636,
                        'options' => [
                            'network_timeout' => 60,
                            'timelimit' => 60,
                        ],
                        'encryption' => 'ssl',
                    ],
                    'adapter_connection_protocol_version' => 3,
                    'baseDn' => 'dn',
                    'uidKey' => 'uidKey',
                    'filter' => '({uid_key}={username})',
                    'dnString' => null,
                    'entryAttribute' => 'ldap_bind_attr',
                    'autoCreateUser' => true,
                    'searchDn' => 'admin',
                    'searchPassword' => 'test',
                    'groupMembership' => true,
                    'groupDn' => 'group,group_dn',
                    'groupAttribute' => 'group_attr',
                    'userUniqueAttribute' => 'ldap_group_user_attr',
                    'includeUserDN' => true,
                ],
                [
                    ['ldap_hostname', '127.0.0.1', 'ldaps://127.0.0.1'],
                    ['ldap_port', 389, 636],
                    ['ldap_base_dn', '', 'dn'],
                    ['ldap_login_attr', '', 'uidKey'],
                    ['ldap_login_filter', '', ''],
                    ['ldap_bind_attr', null, 'ldap_bind_attr'],
                    ['ldap_auto_create_users', false, true],
                    ['ldap_authentication', null, true],
                    ['ldap_admin_user', null, 'admin'],
                    ['ldap_admin_password', null, 'test'],
                    ['ldap_group', null, true],
                    ['ldap_group_name', null, 'group'],
                    ['ldap_group_dn', null, 'group_dn'],
                    ['ldap_group_attr', null, 'group_attr'],
                    ['ldap_group_user_attr', null, 'ldap_group_user_attr'],
                    ['ldap_group_attr_req_dn', false, '1'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getLdapConfigDataProvider
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfig($expected, $returnValueMap)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled', 'getLdapSetting'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(true);
        $config->expects($this->exactly(16))
            ->method('getLdapSetting')
            ->willReturnMap($returnValueMap);

        $this->assertEquals($expected, $config->getLdapConfig());
    }

    /**
     * Provides data for testGetLdapConfigWithDifferentFilters.
     * @return array
     */
    public function getLdapConfigWithDifferentFiltersProvider()
    {
        return [
            'emptyConfigFilter' => [
                'configFilter' => '',
                'expectedFilter' => '({uid_key}={username})',
            ],
            'notEmptyConfigFilterWithBrackets' => [
                'configFilter' => '(objectClass=person)',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithoutBrackets' => [
                'configFilter' => 'objectClass=person',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpaces' => [
                'configFilter' => '  objectClass=person) ',
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
            'notEmptyConfigFilterWithOneBracketsAndSpecCharacters' => [
                'configFilter' => "\n\x0B" . '    (objectClass=person' . "\t\n\r\0",
                'expectedFilter' => '(&({uid_key}={username})(objectClass=person))',
            ],
        ];
    }

    /**
     * @param string $configFilter
     * @param string $expectedFilter
     *
     * @covers ::getLdapConfig
     * @dataProvider getLdapConfigWithDifferentFiltersProvider
     */
    public function testGetLdapConfigWithDifferentFilters($configFilter, $expectedFilter)
    {
        /** @var \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config $config */
        $config = $this->getMockBuilder(Config::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['isLdapEnabled', 'getLdapSetting'])
                       ->getMock();
        $config->expects($this->once())
               ->method('isLdapEnabled')
               ->willReturn(true);

        $config->method('getLdapSetting')->willReturnMap([['ldap_login_filter', '', $configFilter]]);
        $result = $config->getLdapConfig();
        $this->assertEquals($expectedFilter, $result['filter']);
    }

    /**
     * Provides data for testGetOidcConfig
     *
     * @return array
     */
    public function getOidcConfigProvider()
    {
        return [
            'sugarConfigEmpty' => [
                'configSugar' => null,
                'siteUrl' => 'http://site.url/',
                'expectedConfig' => [],
            ],
            'httpClientEmpty' => [
                'configSugar' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'oidcKeySetId' => 'keySetId',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                ],
                'siteUrl' => 'http://site.url/',
                'expectedConfig' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => '',
                    'cloudConsoleRoutes' => [],
                ],
            ],
            'httpClientNotEmpty' => [
                'configSugar' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'oidcKeySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                    ],
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                ],
                'siteUrl' => 'http://site.url/',
                'expectedConfig' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [],
                ],
            ],
            'cloudConsoleRoutesAreNotEmpty' => [
                'configSugar' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'oidcKeySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                    ],
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'userManagement' => 'management/users',
                        'passwordManagement' => 'management/password',
                    ],
                ],
                'siteUrl' => 'http://site.url/',
                'expectedConfig' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'redirectUri' => 'http://site.url',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/oauth2/introspect',
                    'urlUserInfo' => 'http://sts.sugarcrm.local/userinfo',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/keySetId',
                    'keySetId' => 'keySetId',
                    'http_client' => [
                        'retry_count' => 5,
                        'delay_strategy' => 'exponential',
                    ],
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'userManagement' => 'management/users',
                        'passwordManagement' => 'management/password',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $configSugar
     * @param $siteUrl
     * @param $expectedConfig
     *
     * @dataProvider getOidcConfigProvider
     *
     * @covers ::getOIDCConfig
     */
    public function testGetOidcConfig($configSugar, $siteUrl, $expectedConfig)
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $config = new Config($sugarConfig);
        $sugarConfig->method('get')->willReturnMap(
            [
                ['oidc_oauth', null, $configSugar],
                ['site_url', null, $siteUrl],
            ]
        );

        $this->assertEquals($expectedConfig, $config->getOIDCConfig());
    }

    /**
     * Provides data for testIsOIDCEnabled
     *
     * @return array
     */
    public function isOIDCEnabledProvider()
    {
        return [
            'sugarConfigEmpty' => [
                'configSugar' => null,
                'siteUrl' => 'http://site.url/',
                'expectedResult' => false,
            ],
            'sugarConfigNotEmpty' => [
                'configSugar' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://sts.sugarcrm.local',
                    'idpUrl' => 'http://login.sugarcrm.local',
                    'oidcKeySetId' => 'keySetId',
                ],
                'siteUrl' => 'http://site.url/',
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @param $configSugar
     * @param $siteUrl
     * @param $expectedResult
     *
     * @dataProvider isOIDCEnabledProvider
     * @covers ::isOIDCEnabled
     */
    public function testIsOIDCEnabled($configSugar, $siteUrl, $expectedResult)
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $config = new Config($sugarConfig);
        $sugarConfig->method('get')->willReturnMap(
            [
                ['oidc_oauth', null, $configSugar],
                ['site_url', null, $siteUrl],
            ]
        );

        $this->assertEquals($expectedResult, $config->isOIDCEnabled());
    }

    /**
     * @covers ::getOidcDisabledModules
     */
    public function testGetOidcDisabledModules()
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $config = new Config($sugarConfig);
        $this->assertEquals(['Users', 'Employees'], $config->getOidcDisabledModules());
    }

    /**
     * Provides data for testIsOIDCEnabled
     *
     * @return array
     */
    public function buildCloudConsoleUrlProvider()
    {
        return [
            'path-key-found' => [
                'userManagement',
                [],
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [
                        'userManagement' => '/management/users/',
                    ],
                ],
                'http://console.sugarcrm.local/management/users',
            ],
            'path-key-not-found' => [
                'some-unknown-route',
                [],
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                    'cloudConsoleRoutes' => [],
                ],
                'http://console.sugarcrm.local',
            ],
            'path-key-found-and-3-parts-exist' => [
                'userManagement',
                [
                    'a',
                    'some-id',
                    'policies',
                ],
                [
                    'cloudConsoleUrl' => 'http://foo.bar',
                    'cloudConsoleRoutes' => [
                        'userManagement' => 'management/users',
                    ],
                ],
                'http://foo.bar/management/users/a/some-id/policies',
            ],
            'no-parts-url-has-slashes' => [
                'userManagement',
                [],
                [
                    'cloudConsoleUrl' => 'http://console.sugarcrm.local//',
                    'cloudConsoleRoutes' => [],
                ],
                'http://console.sugarcrm.local',
            ],
            'parts-with-non-url-characters' => [
                'userManagement',
                [
                    'user',
                    'Имя',
                ],
                [
                    'cloudConsoleUrl' => 'http://foo.bar',
                    'cloudConsoleRoutes' => [],
                ],
                'http://foo.bar/user/%D0%98%D0%BC%D1%8F',
            ],
        ];
    }

    /**
     * @param string $pathKey
     * @param array|null $parts
     * @param array $oidcConfig
     * @param string $result
     *
     * @dataProvider buildCloudConsoleUrlProvider
     * @covers ::buildCloudConsoleUrl
     */
    public function testBuildCloudConsoleUrl($pathKey, $parts, $oidcConfig, $result)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOIDCConfig'])
            ->getMock();
        $config->method('getOIDCConfig')->willReturn($oidcConfig);

        $this->assertEquals($result, $config->buildCloudConsoleUrl($pathKey, $parts));
    }

     /**
     * @covers ::getOidcDisabledFields
     */
    public function testGetOidcDisabledFields()
    {
        $varDefFields = [
            'pwd_last_changed' => [
                'name' => 'pwd_last_changed',
            ],
            'user_name' => [
                'name' => 'user_name',
                'oidc_disabled' => true,
            ],
            'id' => [
                'name' => 'id',
            ],
            'first_name' => [
                'name' => 'first_name',
                'oidc_disabled' => true,
            ],
            'sugar_login' => [
                'name' => 'sugar_login',
            ],
        ];
        $expectedList = [
            'user_name' => $varDefFields['user_name'],
            'first_name' => $varDefFields['first_name'],
        ];

        $config = $this->getMockBuilder(Config::class)
            ->setMethods(['getUserVardef'])
            ->disableOriginalConstructor()
            ->getMock();
        $config->method('getUserVardef')->willReturn($varDefFields);

        $this->assertEquals($expectedList, $config->getOidcDisabledFields());
    }
}
