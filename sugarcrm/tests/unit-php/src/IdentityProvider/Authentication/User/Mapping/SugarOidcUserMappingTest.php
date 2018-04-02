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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\User\Mapping;

use PHPUnit\Framework\TestCase;
use Sugarcrm\IdentityProvider\Authentication\User as IdmUser;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarOidcUserMapping;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\Mapping\SugarOidcUserMapping
 */
class SugarOidcUserMappingTest extends TestCase
{
    /**
     * @var SugarOidcUserMapping
     */
    protected $userMapper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->userMapper = new SugarOidcUserMapping();
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $result = $this->userMapper->map([
            'preferred_username' => 'username',
            'status' => 0,
            'address' => [
                'street_address' => 'street',
            ],
        ]);
        $this->assertEquals('username', $result['user_name']);
        $this->assertEquals('street', $result['address_street']);
        $this->assertArrayNotHasKey('address_city', $result);
        $this->assertEquals(User::USER_STATUS_ACTIVE, $result['status']);

        $result = $this->userMapper->map(['status' => 1]);
        $this->assertEquals(User::USER_STATUS_INACTIVE, $result['status']);

        $result = $this->userMapper->map(['preferred_username' => 'username']);
        $this->assertArrayNotHasKey('status', $result);
    }

    public function providerMapIdentityException()
    {
        return [
            [''],
            [[]],
            [['sub' => 'srn:cluster:iam:eu:0000000001:tenant']],
            [['sub' => 'srn:cluster:iam:eu:0000000001:user:']],
        ];
    }

    /**
     * @dataProvider providerMapIdentityException
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @param array $response
     * @covers ::mapIdentity
     */
    public function testMapIdentityException($response)
    {
        $this->userMapper->mapIdentity($response);
    }

    /**
     * @covers ::mapIdentity
     */
    public function testMapIdentity()
    {
        $identity = $this->userMapper->mapIdentity(['sub' => 'srn:cluster:iam:eu:0000000001:user:seed_sally_id']);
        $this->assertEquals('id', $identity['field']);
        $this->assertEquals('seed_sally_id', $identity['value']);
    }

    /**
     * @covers ::getIdentityValue
     */
    public function testGetIdentityValue()
    {
        $user = new IdmUser();
        $user->setSrn('srn:cluster:iam:eu:0000000001:user:seed_sally_id');
        $identity = $this->userMapper->getIdentityValue($user);
        $this->assertEquals('seed_sally_id', $identity);
    }
}
