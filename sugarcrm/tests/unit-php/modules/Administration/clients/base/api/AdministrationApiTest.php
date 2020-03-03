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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarApiExceptionNotAuthorized;

/**
 * Class AdministrationApiTest
 * @coversDefaultClass \AdministrationApi
 */
class AdministrationApiTest extends TestCase
{
    /**
     * @var \Configurator|MockObject
     */
    private $configurator;

    /**
     * @var \User|MockObject
     */
    private $currentUser;

    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->currentUser = $this->createPartialMock(\User::class, ['isAdmin']);

        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->apiService->user = $this->currentUser;

        $this->configurator = $this->createPartialMock(\Configurator::class, ['handleOverride']);
        $this->configurator->config = [];

        $this->api = $this->createPartialMock(\AdministrationApi::class, ['getConfigurator', 'clearCache']);
        $this->api->method('getConfigurator')->willReturn($this->configurator);

        $GLOBALS['current_user'] = $this->currentUser;
        $GLOBALS['app_strings'] = ['EXCEPTION_NOT_AUTHORIZED' => 'EXCEPTION_NOT_AUTHORIZED'];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown() : void
    {
        unset($GLOBALS['current_user'], $GLOBALS['app_strings']);

        parent::tearDown();
    }

    /**
     * @covers ::enableIdmMigration
     */
    public function testEnableIdmMigrationUserNotAuthorized(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->enableIdmMigration($this->apiService, []);
    }

    /**
     * @covers ::disableIdmMigration
     */
    public function testDisableIdmMigrationUserNotAuthorized(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->disableIdmMigration($this->apiService, []);
    }

    /**
     * @covers ::enableIdmMigration
     */
    public function testEnableIdmMigration(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(true);

        $this->configurator->expects($this->once())->method('handleOverride');

        $result = $this->api->enableIdmMigration($this->apiService, []);

        $this->assertTrue($this->configurator->config['maintenanceMode']);
        $this->assertTrue($this->configurator->config['idmMigration']);
        $this->assertEquals(['success' => 'true'], $result);
    }

    /**
     * @covers ::disableIdmMigration
     */
    public function testDisableIdmMigration(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(true);

        $this->configurator->expects($this->once())->method('handleOverride');
        $this->api->expects($this->once())->method('clearCache');

        $result = $this->api->disableIdmMigration($this->apiService, []);

        $this->assertFalse($this->configurator->config['maintenanceMode']);
        $this->assertFalse($this->configurator->config['idmMigration']);
        $this->assertEquals(['success' => 'true'], $result);
    }
}
