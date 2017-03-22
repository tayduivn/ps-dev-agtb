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
 * @coversDefaultClass Config
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
                    'url' => 'site_url/index.php?module=Users&amp;action=Authenticate',
                    'binding' => \OneLogin_Saml2_Constants::BINDING_HTTP_POST,
                ],
                'singleLogoutService' => [
                    'url' => 'site_url/index.php?module=Users&amp;action=Logout',
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
                   ]
               );
        $this->assertEquals($expectedConfig, $config->getSAMLConfig());
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
    /**
     * @covers ::getLdapConfig
     */
    public function testGetLdapConfig()
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLdapEnabled', 'getLdapSetting'])
            ->getMock();
        $config->expects($this->once())
            ->method('isLdapEnabled')
            ->willReturn(true);
        $config->expects($this->exactly(14))
            ->method('getLdapSetting')
            ->withConsecutive(
                [$this->equalTo('ldap_hostname'), $this->equalTo('127.0.0.1')],
                [$this->equalTo('ldap_port'), $this->equalTo(389)],
                [$this->equalTo('ldap_base_dn'), $this->identicalTo('')],
                [$this->equalTo('ldap_login_attr'), $this->identicalTo('')],
                [$this->equalTo('ldap_login_filter'), $this->identicalTo('')],
                [$this->equalTo('ldap_auto_create_users'), $this->isFalse()],
                [$this->equalTo('ldap_authentication')],
                [$this->equalTo('ldap_admin_user')],
                [$this->equalTo('ldap_admin_password')],
                [$this->equalTo('ldap_group')],
                [$this->equalTo('ldap_group_name')],
                [$this->equalTo('ldap_group_dn')],
                [$this->equalTo('ldap_group_attr')],
                [$this->equalTo('ldap_group_attr_req_dn'), $this->isFalse()]
            )
            ->willReturnOnConsecutiveCalls(
                '127.0.0.1',
                '389',
                'dn',
                'uidKey',
                '',
                true,
                true,
                'admin',
                'test',
                true,
                'group',
                'group_dn',
                'group_attr',
                false
            );

        $expected = [
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
            ],
            'adapter_connection_protocol_version' => 3,
            'baseDn' => 'dn',
            'uidKey' => 'uidKey',
            'filter' => '({uid_key}={username})',
            'dnString' => NULL,
            'entryAttribute' => NULL,
            'autoCreateUser' => true,
            'searchDn' => 'admin',
            'searchPassword' => 'test',
            'groupMembership' => true,
            'groupDn' => 'group,group_dn',
            'groupAttribute' => 'group_attr',
            'includeUserDN' => false,
        ];
        $this->assertEquals($expected, $config->getLdapConfig());
    }
}
