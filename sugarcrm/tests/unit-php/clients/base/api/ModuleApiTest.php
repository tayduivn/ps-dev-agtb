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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\TestCase;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \ModuleApi
 */
class ModuleApiTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \ServiceBase
     */
    protected $api;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \ModuleApi
     */
    protected $moduleApi;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->api = $this->createMock(\ServiceBase::class);
        $this->moduleApi = $this->getMockBuilder(\ModuleApi::class)
            ->setMethods(['isIDMModeEnabled'])
            ->getMock();
        $this->moduleApi->expects($this->any())->method('isIDMModeEnabled')->willReturn(true);
    }

    /**
     * module provider
     * @return array
     */
    public function moduleProvider()
    {
        return [
            ['Users'],
            ['Employees'],
        ];
    }

    /**
     * @expectedException \SugarApiExceptionNotAuthorized
     * @dataProvider moduleProvider
     * @param string $module
     * @covers ::createBean
     */
    public function testCreateBeanException($module)
    {
        $this->moduleApi->createBean($this->api, ['module' => $module]);
    }

    /**
     * @expectedException \SugarApiExceptionNotAuthorized
     * @dataProvider moduleProvider
     * @param string $module
     * @covers ::deleteRecord
     */
    public function testDeleteRecordException($module)
    {
        $this->moduleApi->deleteRecord($this->api, ['module' => $module, 'record' => 'not_exist']);
    }
}
