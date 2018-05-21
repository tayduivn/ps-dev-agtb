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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication;

require_once 'modules/Administration/clients/base/api/AuthSettingsApi.php';

/**
 * @coversDefaultClass \AuthSettingsApi
 * Class AuthSettingsApiTest
 * @package Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api
 */
class AuthSettingsApiTest extends TestCase
{
    /**
     * @var \User | MockObject
     */
    private $currentUser;

    /**
     * @var \RestService | MockObject
     */
    private $service;

    /**
     * @var Authentication\Config | MockObject
     */
    private $config;

    /**
     * @see testResultAuthSettings
     * @return array
     */
    public function expectedAuthSettings() : array
    {
        $ldapConfig = ['some' => 'ldap', 'config' => 'value'];
        $samlConfig = ['some' => 'saml', 'config' => 'value'];
        $adminSettingsMap = [
            'captcha_on' => ['captcha_on', false, true],
            'captcha_public_key' => ['captcha_public_key', '', 'captcha_public_key'],
            'captcha_private_key' => ['captcha_private_key', '', 'captcha_private_key',],
            'honeypot_on' => ['honeypot_on', false, true],
        ];
        $passwordSetting = [
            'minpwdlength' => 6,
            'maxpwdlength' => 0,
            'oneupper' => true,
            'onelower' => true,
            'onenumber' => true,
            'onespecial' => false,
            'SystemGeneratedPasswordON' => true,
            'generatepasswordtmpl' => 'c4d8d2d2-5ccb-11e8-ac41-685b35ac4c74',
            'lostpasswordtmpl' => 'c4e093be-5ccb-11e8-b7fb-685b35ac4c74',
            'customregex' => '',
            'regexcomment' => '',
            'forgotpasswordON' => true,
            'linkexpiration' => true,
            'linkexpirationtime' => 24,
            'linkexpirationtype' => 60,
            'userexpiration' => '0',
            'userexpirationtime' => '',
            'userexpirationtype' => '1',
            'userexpirationlogin' => '',
            'systexpiration' => 1,
            'systexpirationtime' => 7,
            'systexpirationtype' => '0',
            'systexpirationlogin' => '',
            'lockoutexpiration' => '0',
            'lockoutexpirationtime' => '',
            'lockoutexpirationtype' => '1',
            'lockoutexpirationlogin' => '',
        ];
        $localConfExpPassReq = [
            'minimum_length' => $passwordSetting['minpwdlength'],
            'maximum_length' => $passwordSetting['maxpwdlength'],
            'require_upper' => $passwordSetting['oneupper'],
            'require_lower' => $passwordSetting['onelower'],
            'require_number' => $passwordSetting['onenumber'],
            'require_special' => $passwordSetting['onespecial'],
            'password_regex' => $passwordSetting['customregex'],
            'regex_description' => $passwordSetting['regexcomment'],
        ];
        $localConfExpReset = [
            'enable' => $passwordSetting['forgotpasswordON'],
            'expiration' => $passwordSetting['linkexpirationtime'] * $passwordSetting['linkexpirationtype'] * 60,
            'require_recaptcha' => $adminSettingsMap['captcha_on'][2],
            'recaptcha_public' => $adminSettingsMap['captcha_public_key'][2],
            'recaptcha_private' => $adminSettingsMap['captcha_private_key'][2],
            'require_honeypot' => $adminSettingsMap['honeypot_on'][2],
        ];
        $localConfExpExpirDisabled = [
            'time' => 0,
            'attempt' => 0,
        ];

        $localConfExpExpirTime = [
            'time' => 2 * 7 * 3600 * 24,
            'attempt' => 0,
        ];

        $localConfExpExpirAttempt = [
            'time' => 0,
            'attempt' => 100,
        ];

        return [
            'localDisabledExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirDisabled,
                    ],
                ],
            ],
            'localTimeExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '1',
                            'userexpirationtime' => '2',
                            'userexpirationtype' => '7',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirTime,
                    ],
                ],
            ],
            'localAttemptExpiration' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => [
                            'userexpiration' => '2',
                            'userexpirationlogin' => '100',
                        ] + $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirAttempt,
                    ],
                ],
            ],
            'ldap' => [
                'in' => [
                    'ldapConfig' => $ldapConfig,
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'ldap'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirDisabled,
                    ],
                    'ldap' => $ldapConfig,
                ],
            ],
            'saml' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => $samlConfig,
                    'authenticationClass' => 'IdMSAMLAuthenticate',
                    'passwordSetting' => $passwordSetting,
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local', 'saml'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirDisabled,
                    ],
                    'saml' => $samlConfig,
                ],
            ],
        ];
    }

    /**
     * @covers ::authSettings
     * @dataProvider expectedAuthSettings
     * @param array $in
     * @param array $expected
     */
    public function testResultAuthSettings(array $in, array $expected) :void
    {

        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->method('getLdapConfig')->willReturn($in['ldapConfig']);
        $this->config->method('getSAMLConfig')->willReturn($in['samlConfig']);
        $this->config->method('get')
            ->will($this->returnValueMap([
                ['passwordsetting', [], $in['passwordSetting']],
                ['authenticationClass', 'IdMSugarAuthenticate', $in['authenticationClass']],
            ]));
        $this->api->method('get')
            ->will($this->returnValueMap($in['adminSettingsMap']));

        $result = $this->api->authSettings($this->service, []);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::authSettings
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testNoAdminRequest() :void
    {
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->api->authSettings($this->service, []);
    }

    /**
     * @covers ::authSettings
     * @expectedException \SugarApiExceptionNotAuthorized
     */
    public function testAuthorizedRequest() :void
    {
        unset($GLOBALS['current_user']);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->api->authSettings($this->service, []);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->createMock(\RestService::class);
        $this->currentUser = $this->createMock(\User::class);
        $this->config = $this->createMock(Authentication\Config::class);
        $this->api = $this->createPartialMock(\AuthSettingsApi::class, ['getAuthConfig', 'get']);
        $this->api->method('getAuthConfig')->willReturn($this->config);
        $GLOBALS['current_user'] = $this->currentUser;
        $GLOBALS['app_strings'] = ['EXCEPTION_NOT_AUTHORIZED' => 'EXCEPTION_NOT_AUTHORIZED'];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        parent::tearDown();
    }
}
