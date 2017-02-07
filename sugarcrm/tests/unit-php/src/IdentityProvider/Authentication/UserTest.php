<?php
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User
 */
class IdMUserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setSugarUser
     * @covers ::getSugarUser
     */
    public function testSetGetSugarUser()
    {
        $idmUser = new User('test', 'test');
        /** @var \User $sugarUser */
        $sugarUser = $this->createMock(\User::class);

        $idmUser->setSugarUser($sugarUser);
        $this->assertInstanceOf(\User::class, $idmUser->getSugarUser());
    }
}
