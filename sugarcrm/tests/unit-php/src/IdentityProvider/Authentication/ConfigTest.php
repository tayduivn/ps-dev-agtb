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

        $config->set('key', 'idm_value');
        $this->assertEquals('idm_value', $config->get('key'), 'Overridden value');
    }

    public function getSAMLConfigDataProvider()
    {
        return [
            'no override in config' => [
                [
                    'default' => 'config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' =>
                                'config_site_url/index.php?platform%3Dbase%26module%3DUsers%26action%3DAuthenticate',
                        ],
                    ],
                ],
                ['default' => 'config'],
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
                ['default' => 'overridden config'],
            ],
        ];
    }

    /**
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigDataProvider
     */
    public function testGetSAMLConfig($expectedConfig, $defaultConfig, $configValues)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSAMLDefaultConfig'])
            ->getMock();
        $config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                ['SAML_returnQueryVars'],
                ['SAML_SAME_WINDOW'],
                ['site_url'],
                ['SAML']
            )
            ->willReturnOnConsecutiveCalls(
                ['platform' => 'base'],
                'config_SAML_SAME_WINDOW',
                'config_site_url',
                $configValues
            );
        $config->expects($this->once())
            ->method('getSAMLDefaultConfig')
            ->willReturn($defaultConfig);
        $samlConfig = $config->getSAMLConfig();
        $this->assertEquals($expectedConfig, $samlConfig);
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
