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
     * @var User
     */
    protected $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    protected function setUp()
    {
        $this->user = new User('test', 'test');
        $this->sugarUser = $this->createMock(\User::class);
        $this->user->setSugarUser($this->sugarUser);
    }

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
}
