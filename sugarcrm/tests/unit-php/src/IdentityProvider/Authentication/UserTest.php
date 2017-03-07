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
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User
 */
class IdMUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TimeDate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timeDate = null;

    /**
     * @var User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $user = null;

    /**
     * @var \User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser = null;

    /**
     * @covers ::setSugarUser
     * @covers ::getSugarUser
     */
    public function testSetGetSugarUser()
    {
        $this->assertInstanceOf(\User::class, $this->user->getSugarUser());
    }

    /**
     * @covers ::setPasswordExpired
     * @covers ::isCredentialsNonExpired
     */
    public function testPasswordExpired()
    {
        $this->user->setPasswordExpired(false);
        $this->assertTrue($this->user->isCredentialsNonExpired());
    }

    /**
     * @covers ::getPasswordType
     */
    public function testGetPasswordTypeSystem()
    {
        $this->sugarUser->system_generated_password = 1;
        $this->assertEquals(User::PASSWORD_TYPE_SYSTEM, $this->user->getPasswordType());
    }

    /**
     * @covers ::getPasswordType
     */
    public function testGetPasswordTypeUser()
    {
        $this->sugarUser->system_generated_password = null;
        $this->assertEquals(User::PASSWORD_TYPE_USER, $this->user->getPasswordType());
    }

    /**
     * @covers ::setPasswordLastChangeDate
     * @covers ::getPasswordLastChangeDate
     */
    public function testPasswordLastChangeDate()
    {
        $this->user->setPasswordLastChangeDate('test');
        $this->assertEquals('test', $this->user->getPasswordLastChangeDate());
    }

    /**
     * @covers allowUpdateDateModified
     */
    public function testAllowUpdateDateModified()
    {
        $this->user->allowUpdateDateModified(false);
        $this->assertFalse($this->sugarUser->update_date_modified);
    }

    /**
     * @see testGetLoginFailed
     * @see testIncrementLoginFailed
     * @return array
     */
    public function userLoginFailedProvider()
    {
        return [
            'integer' => ['value' => 1, 'expected' => 1, 'incremented' => 2],
            'string' => ['value' => '2', 'expected' => 2, 'incremented' => 3],
            'empty' => ['value' => null, 'expected' => 0, 'incremented' => 1],
        ];
    }

    /**
     * @covers       ::getLoginFailed
     * @param mixed $value
     * @param int $expected
     * @dataProvider userLoginFailedProvider
     */
    public function testGetLoginFailed($value, $expected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('loginfailed')
            ->willReturn($value);

        $result = $this->user->getLoginFailed();
        $this->assertEquals($expected, $result);
    }

    /**
     * @see testGetLockout
     * @return array
     */
    public function userLockoutProvider()
    {
        return [
            'empty' => ['value' => null, 'expected' => false],
            'false' => ['value' => false, 'expected' => false],
            'zero' => ['value' => 0, 'expected' => false],
            'true' => ['value' => true, 'expected' => true],
            'one' => ['value' => 1, 'expected' => true],
        ];
    }

    /**
     * @covers ::getLockout
     * @param mixed $value
     * @param int $expected
     * @dataProvider userLockoutProvider
     */
    public function testGetLockout($value, $expected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('lockout')
            ->willReturn($value);

        $result = $this->user->getLockout();

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::clearLockout
     */
    public function testClearLockout()
    {
        $this->sugarUser
            ->expects($this->exactly(2))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '', 0, 'global'],
                ['loginfailed', 0, 0, 'global']
            );

        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->clearLockout();
    }

    /**
     * @covers ::lockout
     */
    public function testLockout()
    {
        $this->sugarUser
            ->expects($this->exactly(3))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '1', 0, 'global'],
                ['logout_time', $logoutTime = '2017-02-13 01:01:01', 0, 'global'],
                ['loginfailed', 0, 0, 'global']
            );

        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->lockout($logoutTime);
    }

    /**
     * @covers       ::incrementLoginFailed
     * @dataProvider userLoginFailedProvider
     * @param $value
     * @param $unused
     * @param $incrementExpected
     */
    public function testIncrementLoginFailed($value, $unused, $incrementExpected)
    {
        $this->sugarUser
            ->method('getPreference')
            ->with('loginfailed')
            ->willReturn($value);

        $this->sugarUser
            ->expects($this->exactly(2))
            ->method('setPreference')
            ->withConsecutive(
                ['lockout', '', 0, 'global'],
                ['loginfailed', $incrementExpected, 0, 'global']
            );
        $this->sugarUser
            ->expects($this->once())
            ->method('savePreferencesToDB');

        $this->user->incrementLoginFailed();
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

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTimeDate'])
            ->getMock();

        $this->sugarUser = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->setSugarUser($this->sugarUser);
        $this->user->method('getTimeDate')->willReturn($this->timeDate);
    }
}
