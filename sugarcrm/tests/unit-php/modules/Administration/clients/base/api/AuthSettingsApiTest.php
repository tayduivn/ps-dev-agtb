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
     * @var Authentication\Lockout | MockObject
     */
    private $lockout;

    /**
     * @var array
     */
    private $sugar_config_bak;

    /**
     * @see testResultAuthSettings
     * @return array
     */
    public function expectedAuthSettings(): array
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
            'LockoutDurationMins' => 0,
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

        $loginLockoutDisabled = [
            'type' => Authentication\Lockout::LOCKOUT_DISABLED,
            'attempt' => 0,
            'time' => 0,
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
                        'login_lockout' => $loginLockoutDisabled,
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
                        'login_lockout' => $loginLockoutDisabled,
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
                        'login_lockout' => $loginLockoutDisabled,
                    ],
                ],
            ],
            'localTimeLockout' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => array_replace(
                        $passwordSetting,
                        [
                            'lockoutexpiration' => Authentication\Lockout::LOCK_TYPE_TIME,
                            'lockoutexpirationlogin' => 3,
                            'LockoutDurationMins' => 60,
                        ]
                    ),
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => [
                            'type' => Authentication\Lockout::LOCK_TYPE_TIME,
                            'attempt' => 3,
                            'time' => 3600,
                        ],
                    ],
                ],
            ],
            'localPermanentLockout' => [
                'in' => [
                    'ldapConfig' => [],
                    'samlConfig' => [],
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'passwordSetting' => array_replace(
                        $passwordSetting,
                        [
                            'lockoutexpiration' => Authentication\Lockout::LOCK_TYPE_PERMANENT,
                            'lockoutexpirationlogin' => 4,
                            'LockoutDurationMins' => 2,
                        ]
                    ),
                    'adminSettingsMap' => $adminSettingsMap,
                ],
                'expected' => [
                    'enabledProviders' => ['local'],
                    'local' => [
                        'password_requirements' => $localConfExpPassReq,
                        'password_reset_policy' => $localConfExpReset,
                        'password_expiration' => $localConfExpExpirDisabled,
                        'login_lockout' => [
                            'type' => Authentication\Lockout::LOCK_TYPE_PERMANENT,
                            'attempt' => 4,
                            'time' => 120,
                        ],
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
                        'login_lockout' => $loginLockoutDisabled,
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
                        'login_lockout' => $loginLockoutDisabled,
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
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);
        $this->lockout->method('getLockType')->willReturn($in['passwordSetting']['lockoutexpiration']);
        $this->lockout->method('getFailedLoginsCount')->willReturn($in['passwordSetting']['lockoutexpirationlogin']);
        $this->lockout->method('getLockoutDurationMins')->willReturn($in['passwordSetting']['LockoutDurationMins']);

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
        $GLOBALS['sugar_config']['idmMigration'] = true;
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
        $GLOBALS['sugar_config']['idmMigration'] = true;
        unset($GLOBALS['current_user']);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');

        $this->api->authSettings($this->service, []);
    }

    /**
     * @covers ::authSettings
     * @expectedException \SugarApiExceptionNotFound
     */
    public function testAuthSettingsMigrationDisabled() :void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())->method('getLdapConfig');
        $this->config->expects($this->never())->method('getSAMLConfig');
        $this->config->expects($this->never())->method('get');
        
        $this->api->authSettings($this->service, []);
    }

    /**
     * @return array
     */
    public function switchOnIdmModeExceptionDataProvider() : array
    {
        return [
            [[]],
            [['idmMode' => false]],
            'empty config' => [['idmMode' => []]],
            'config without enabled parameter' => [['idmMode' => ['some_idm_config']]],
        ];
    }

    /**
     * @covers ::switchOnIdmMode
     */
    public function testSwitchOnIdmMode() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->once())
            ->method('setIDMMode')
            ->with($this->equalTo(['enabled' => true]));
        $this->config->expects($this->once())
            ->method('getIDMModeConfig')
            ->willReturn([]);
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOnIdmMode
     * @expectedException SugarApiExceptionMissingParameter
     * @dataProvider switchOnIdmModeExceptionDataProvider
     */
    public function testSwitchOnIdmModeException($args) : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->api->switchOnIdmMode($this->service, $args);
    }

    /**
     * @covers ::switchOnIdmMode
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSwitchOnIdmModeUnauthorized() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOnIdmMode
     * @expectedException SugarApiExceptionNotFound
     */
    public function testSwitchOnIdmModeMigrationDisabled() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->api->switchOnIdmMode($this->service, ['idmMode' => ['enabled' => true]]);
    }

    /**
     * @covers ::switchOffIdmMode
     */
    public function testSwitchOffIdmMode() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->once())
            ->method('setIDMMode')
            ->with($this->equalTo(false));
        $this->config->expects($this->once())
            ->method('getIDMModeConfig')
            ->willReturn([]);
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @covers ::switchOffIdmMode
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSwitchOffIdmModeUnauthorized() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = true;
        $this->currentUser->method('isAdmin')->willReturn(false);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @covers ::switchOffIdmMode
     * @expectedException SugarApiExceptionNotFound
     */
    public function testSwitchOffIdmModeMigrationDisabled() : void
    {
        $GLOBALS['sugar_config']['idmMigration'] = false;
        $this->currentUser->method('isAdmin')->willReturn(true);

        $this->config->expects($this->never())
            ->method('setIDMMode');
        $this->config->expects($this->never())
            ->method('getIDMModeConfig');
        $this->api->switchOffIdmMode($this->service, []);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->sugar_config_bak = $GLOBALS['sugar_config'];
        $this->service = $this->createMock(\RestService::class);
        $this->currentUser = $this->createMock(\User::class);
        $this->config = $this->createMock(Authentication\Config::class);
        $this->lockout = $this->createMock(Authentication\Lockout::class);
        $this->api = $this->createPartialMock(\AuthSettingsApi::class, ['getAuthConfig', 'get', 'getLockout']);
        $this->api->method('getAuthConfig')->willReturn($this->config);
        $this->api->method('getLockout')->willReturn($this->lockout);
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
        $GLOBALS['sugar_config'] = $this->sugar_config_bak;
        parent::tearDown();
    }
}