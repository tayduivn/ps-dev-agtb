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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Lockout
 */
class LockoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $originAppStrings;

    /**
     * @var array
     */
    protected $appStrings = array(
        'LBL_LOGIN_ATTEMPTS_OVERRUN' => 'Too many failed login attempts.',
        'LBL_LOGIN_LOGIN_TIME_ALLOWED' => 'You can try logging in again in ',
        'LBL_LOGIN_LOGIN_TIME_DAYS' => 'days.',
        'LBL_LOGIN_LOGIN_TIME_HOURS' => 'h.',
        'LBL_LOGIN_LOGIN_TIME_MINUTES' => 'min.',
        'LBL_LOGIN_LOGIN_TIME_SECONDS' => 'sec.',
        'EXCEPTION_UNKNOWN_EXCEPTION' => 'Your request failed due to an unknown exception.'
    );

    /**
     * @var User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $user = null;

    /**
     * @var \User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser = null;

    /**
     * @var Lockout
     */
    protected $lockout = null;

    /**
     * @var \TimeDate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timeDate = null;

    /**
     * @var \SugarDateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarDateTime = null;

    /**
     * @see testLockedMessage
     * @return array
     */
    public function lockedMessageProvider()
    {
        return [
            'loggingIn2days' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1440],
                'expireTime' => '2017-02-15 01:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2days.',
            ],
            'loggingIn1day' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 1, 'lockoutexpirationtype' => 1440],
                'expireTime' => '2017-02-14 01:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 1days.',
            ],
            'loggingIn2hours' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 60],
                'expireTime' => '2017-02-13 03:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2h.',
            ],
            'loggingIn1hours' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 1, 'lockoutexpirationtype' => 60],
                'expireTime' => '2017-02-13 02:01:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 1h.',
            ],
            'loggingIn2minutes' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:03:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 2min.',
            ],
            'loggingIn1minute' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 1, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:02:01',
                'nowTime' => '2017-02-13 01:01:01',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 1min.',
            ],
            'loggingIn20seconds' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:03:01',
                'nowTime' => '2017-02-13 01:02:41',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 20sec.',
            ],
            'loggingIn10seconds' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:03:01',
                'nowTime' => '2017-02-13 01:02:51',
                'expectsMessage' => 'Too many failed login attempts. You can try logging in again in 10sec.',
            ],
            'unknownException' => [
                'logoutTime' => '',
                'config' => ['lockoutexpirationtime' => 2, 'lockoutexpirationtype' => 1],
                'expireTime' => false,
                'nowTime' => '2017-02-13 01:02:51',
                'expectsMessage' => 'Your request failed due to an unknown exception.',
            ],
        ];
    }

    /**
     * @see testIsUserStillLocked
     * @return array
     */
    public function isUserStillLockedProvider()
    {
        return [
            'lockedBy5Hours' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 5, 'lockoutexpirationtype' => 60],
                'expireTime' => '2017-02-13 06:01:01',
                'nowTime' => '2017-02-13 05:01:01',
                'expectsLocked' => true,
            ],
            'locked10Minutes' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 10, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:11:01',
                'nowTime' => '2017-02-13 01:10:01',
                'expectsLocked' => true,
            ],
            'unLocked5Hours' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 5, 'lockoutexpirationtype' => 60],
                'expireTime' => '2017-02-13 06:01:01',
                'nowTime' => '2017-02-13 07:01:01',
                'expectsLocked' => false,
            ],
            'unLocked10Minutes' => [
                'logoutTime' => '2017-02-13 01:01:01',
                'config' => ['lockoutexpirationtime' => 10, 'lockoutexpirationtype' => 1],
                'expireTime' => '2017-02-13 01:11:01',
                'nowTime' => '2017-02-13 01:11:01',
                'expectsLocked' => false,
            ],
        ];
    }

    /**
     * @see testGetLockoutExpirationLogin
     * @return array
     */
    public function lockoutExpirationLoginProvider()
    {
        return [
            'issetValid' => ['config' => ['lockoutexpirationlogin' => 2], 'expected' => 2],
            'notConfigured' => ['config' => [], 'expected' => 0],
            'empty' => ['config' => ['lockoutexpirationlogin' => 0], 'expected' => 0],
            'null' => ['config' => ['lockoutexpirationlogin' => null], 'expected' => 0],
            'string' => ['config' => ['lockoutexpirationlogin' => '3'], 'expected' => 3],
        ];
    }

    /**
     * @see testIsEnabled
     * @return array
     */
    public function isEnabledProvider()
    {
        return [
            'enabled' => ['config' => ['lockoutexpiration' => Lockout::ENABLED], 'expected' => true],
            'disabled' => ['config' => ['lockoutexpiration' => 1], 'expected' => false],
            'notConfigured' => ['config' => [], 'expected' => false],
        ];
    }

    /**
     * @covers       ::getLockedMessage
     * @dataProvider lockedMessageProvider
     * @param $logoutTime
     * @param $config
     * @param $expireTime
     * @param $nowTime
     * @param $expectsMessage
     */
    public function testLockedMessage($logoutTime, $config, $expireTime, $nowTime, $expectsMessage)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('logout_time')
            ->willReturn($logoutTime);

        $this->lockout
            ->method('getPasswordSettings')
            ->willReturn($config);

        $this->timeDate->method('nowDb')
            ->willReturn($nowTime);
        $this->timeDate->method('fromDb')
            ->with($logoutTime)
            ->willReturn($this->sugarDateTime);

        $this->sugarDateTime->method('modify')
            ->with($this->callback(function ($modify) use ($logoutTime, $expireTime) {
                $expireTimeCalculated = (new \DateTime($logoutTime))->modify($modify)->format('Y-m-d H:i:s');
                $this->assertEquals($expireTime, $expireTimeCalculated, "Expire date doesn't equal modified date.");
                return true;
            }))
            ->willReturnSelf();

        $this->sugarDateTime->method('asDb')
            ->willReturn($expireTime);

        $message = $this->lockout->getLockedMessage($this->user);
        $this->assertEquals($expectsMessage, $message);
    }

    /**
     * @covers       ::isUserStillLocked
     * @dataProvider isUserStillLockedProvider
     * @param $logoutTime
     * @param $config
     * @param $expireTime
     * @param $nowTime
     * @param $expectsLocked
     */
    public function testIsUserStillLocked($logoutTime, $config, $expireTime, $nowTime, $expectsLocked)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('logout_time')
            ->willReturn($logoutTime);

        $this->lockout
            ->method('getPasswordSettings')
            ->willReturn($config);

        $this->timeDate->method('nowDb')
            ->willReturn($nowTime);
        $this->timeDate->method('fromDb')
            ->with($logoutTime)
            ->willReturn($this->sugarDateTime);

        $this->sugarDateTime->method('modify')
            ->with($this->callback(function ($modify) use ($logoutTime, $expireTime) {
                $expireTimeCalculated = (new \DateTime($logoutTime))
                    ->modify($modify)
                    ->format('Y-m-d H:i:s');
                $this->assertEquals($expireTime, $expireTimeCalculated, "Expire date doesn't equal modified date.");
                return true;
            }))
            ->willReturnSelf();

        $this->sugarDateTime->method('asDb')
            ->willReturn($expireTime);

        $result = $this->lockout->isUserStillLocked($this->user);
        $this->assertEquals($expectsLocked, $result);
    }

    /**
     * @covers       ::getFailedLoginsCount
     * @dataProvider lockoutExpirationLoginProvider
     * @param array $config
     * @param bool $expected
     */
    public function testGetLockoutExpirationLogin($config, $expected)
    {
        $this->lockout
            ->method('getPasswordSettings')
            ->willReturn($config);

        $result = $this->lockout->getFailedLoginsCount();

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers       ::isEnabled
     * @dataProvider isEnabledProvider
     * @param array $config
     * @param bool $expected
     */
    public function testIsEnabled($config, $expected)
    {
        $this->lockout
            ->method('getPasswordSettings')
            ->willReturn($config);

        $result = $this->lockout->isEnabled();

        $this->assertEquals($expected, $result);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->timeDate = $this->getMockBuilder(\TimeDate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sugarDateTime = $this->getMockBuilder(\SugarDateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockout = $this->getMockBuilder(Lockout::class)
            ->setMethods(['getPasswordSettings', 'getTimeDate'])
            ->getMock();
        $this->lockout->method('getTimeDate')->willReturn($this->timeDate);

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sugarUser = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user->method('getSugarUser')->willReturn($this->sugarUser);

        if (!empty($GLOBALS['app_strings'])) {
            $this->originAppStrings = $GLOBALS['app_strings'];
        }

        $GLOBALS['app_strings'] = $this->appStrings;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        unset($GLOBALS['app_strings']);
        if ($this->originAppStrings) {
            $GLOBALS['app_strings'] = $this->originAppStrings;
        }
    }
}
