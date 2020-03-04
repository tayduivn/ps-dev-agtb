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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\ServiceAccount;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\ServiceAccount
 */
class ServiceAccountTest extends TestCase
{
    /**
     * @var ServiceAccount|MockObject
     */
    protected $serviceAccount;

    /**
     * @var \User|MockObject
     */
    protected $userBean;

    /**
     * @var \User|MockObject
     */
    protected $systemUser;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->serviceAccount = $this->getMockBuilder(ServiceAccount::class)
            ->setMethods(['getUserBean'])
            ->getMock();
        $this->userBean = $this->createMock(\User::class);
        $this->serviceAccount->method('getUserBean')->willReturn($this->userBean);

        $this->systemUser = $this->createMock(\User::class);
        $this->systemUser->id = 'systemId';
    }

    /**
     * @covers ::isServiceAccount
     */
    public function testIsServiceAccount(): void
    {
        $this->assertTrue($this->serviceAccount->isServiceAccount());
    }

    /**
     * @covers ::getSugarUser
     */
    public function testGetSugarUser(): void
    {
        $this->userBean->expects($this->once())->method('getSystemUser')->willReturn($this->systemUser);
        $sugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $sugarUser->id);
        $repeatedSugarUser = $this->serviceAccount->getSugarUser();
        $this->assertEquals('systemId', $repeatedSugarUser->id);
    }
}
