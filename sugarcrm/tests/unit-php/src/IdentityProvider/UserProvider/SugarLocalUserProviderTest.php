<?php

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider
 */
class SugarLocalUserProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->user = $this->createMock(\User::class);

        $this->userProvider = $this->getMockBuilder(SugarLocalUserProvider::class)
            ->setMethods(['createUserBean'])
            ->getMock();

        $this->userProvider->expects($this->any())
            ->method('createUserBean')
            ->willReturn($this->user);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @covers loadUserByUsername
     */
    public function testLoadUserByUsernameNoId()
    {
        $this->user->expects($this->once())
            ->method('retrieve_user_id')
            ->with($this->equalTo($name = 'test'))
            ->willReturn(null);

        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @return array
     */
    public function providerLoadUserByUsernameNotAllowed()
    {
        return [
            [1, 0, 'Active'],
            [0, true, 'Active'],
            [0, 0, 'Inactive'],
        ];
    }

    /**
     * @dataProvider providerLoadUserByUsernameNotAllowed
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @covers loadUserByUsername
     * @param $isGroup
     * @param $portalOnly
     * @param $status
     */
    public function testLoadUserByUsernameNotAllowed($isGroup, $portalOnly, $status)
    {
        $this->user->expects($this->once())
            ->method('retrieve_user_id')
            ->with($this->equalTo($name = 'test'))
            ->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->willReturn(null);

        $this->user->is_group = $isGroup;
        $this->user->portal_only = $portalOnly;
        $this->user->status = $status;

        $this->userProvider->loadUserByUsername($name);
    }

    /**
     * @covers loadUserByUsername
     */
    public function testLoadUserByUsername()
    {
        $this->user->expects($this->once())
            ->method('retrieve_user_id')
            ->with($this->equalTo($name = 'test'))
            ->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->willReturn(null);

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = 0;
        $this->user->user_hash = 'user_hash';

        /** @var User $newUser */
        $newUser = $this->userProvider->loadUserByUsername($name);

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertEquals($this->user->user_hash, $newUser->getPassword());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @covers refreshUser
     */
    public function testRefreshUserNonSupported()
    {
        $userMock = $this->createMock(UserInterface::class);
        $this->userProvider->refreshUser($userMock);
    }

    /**
     * @covers refreshUser
     */
    public function testRefreshUser()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn($name = 'user_name');

        $this->user->expects($this->once())
            ->method('retrieve_user_id')
            ->with($this->equalTo($name))
            ->willReturn($id = 'idtest');

        $this->user->expects($this->once())
            ->method('retrieve')
            ->with($this->equalTo($id), $this->isTrue(), $this->isFalse())
            ->willReturn(null);

        $this->user->is_group = 0;
        $this->user->portal_only = 0;
        $this->user->status = 0;
        $this->user->user_hash = 'user_hash';

        $this->userProvider->refreshUser($user);
    }

    /**
     * @covers supportsClass
     */
    public function testSupportsClass()
    {
        $this->assertTrue($this->userProvider->supportsClass(User::class));
    }
}
