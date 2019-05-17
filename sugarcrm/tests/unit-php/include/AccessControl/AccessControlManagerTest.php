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

namespace Sugarcrm\SugarcrmTestUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SugarFieldVoter;
use Sugarcrm\Sugarcrm\AccessControl\SugarRecordVoter;
use Sugarcrm\Sugarcrm\AccessControl\SugarVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class AccessControlManagerTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\AccessControlManager
 */
class AccessControlManagerTest extends TestCase
{
    /**
     * @covers ::registerVoters
     * @covers ::registerVoter
     * @covers ::init
     * @covers ::instance
     * @covers ::getRegisteredVoter
     * @covers ::__construct
     *
     */
    public function testRegisterVoters()
    {
        $acm = AccessControlManager::instance();
        $voter = TestReflection::callProtectedMethod($acm, 'getRegisteredVoter', [AccessControlManager::MODULES_KEY]);
        $this->assertTrue($voter instanceof SugarVoter);

        $voter = TestReflection::callProtectedMethod($acm, 'getRegisteredVoter', ['DASHLETS']);
        $this->assertTrue($voter instanceof SugarVoter);

        $voter = TestReflection::callProtectedMethod($acm, 'getRegisteredVoter', ['FIELDS']);
        $this->assertTrue($voter instanceof SugarFieldVoter);

        $voter = TestReflection::callProtectedMethod($acm, 'getRegisteredVoter', ['RECORDS']);
        $this->assertTrue($voter instanceof SugarRecordVoter);

    }

    /**
     * @covers ::allowModuleAccess
     * @covers ::allowAccess
     * @covers ::isAccessControlled
     *
     * @dataProvider allowModuleAccessProvider
     */
    public function testAllowModuleAccess($access_config, $notAccessibleList, $module, $entitled, $expected)
    {
        $sugarVoterMock = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getNotAccessibleModuleListByLicenseTypes'])
            ->getMock();

        $sugarVoterMock->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoterMock->expects($this->any())
            ->method('getNotAccessibleModuleListByLicenseTypes')
            ->will($this->returnValue($notAccessibleList));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoter', 'getAccessControlledList'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoter')
            ->will($this->returnValue($sugarVoterMock));

        $acmMock->expects($this->any())
            ->method('getAccessControlledList')
            ->will($this->returnValue($access_config));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowModuleAccess($module));
    }

    public function allowModuleAccessProvider()
    {
        return [
            // entitled has access
            [
                ['BusinessCenters' => ['SUGAR_SERVE']],
                ['not_BusinessCenters' => true],
                'BusinessCenters',
                ['SUGAR_SERVE'],
                true,
            ],
            // no entitlement
            [
                ['BusinessCenters' => ['SUGAR_SERVE']],
                ['BusinessCenters' => ['SUGAR_SERVE']],
                'BusinessCenters',
                [],
                false,
            ],
            // multiple entitlement
            [
                ['BusinessCenters' => ['SUGAR_SERVE']],
                ['BusinessCenters' => true],
                'BusinessCenters',
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                false,
            ],
            // not accessible list is empty
            [
                ['BusinessCenters' => ['SUGAR_SERVE']],
                [],
                'MODULE_NAME_NOT_ON_THE_LIST',
                ['NOT_SERVICE_CLOUD'],
                true,
            ],
            // null paramenter
            [
                ['BusinessCenters' => ['SUGAR_SERVE']],
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                null,
                ['INVLIAD_SERVICE_CLOUD'],
                true,
            ],
        ];
    }

    /**
     * @covers ::allowDashletAccess
     * @covers ::allowAccess
     *
     * @dataProvider allowDashletAccessProvider
     */
    public function testAllowDashletccess($access_config, $label, $entitled, $expected)
    {
        $sugarVoterMock = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoterMock->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoterMock->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoter', 'getAccessControlledList'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoter')
            ->will($this->returnValue($sugarVoterMock));

        $acmMock->expects($this->any())
            ->method('getAccessControlledList')
            ->will($this->returnValue($access_config));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowDashletAccess($label));
    }

    public function allowDashletAccessProvider()
    {
        return [
            // entitled has access
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                'activity-timeline',
                ['SUGAR_SERVE'],
                true,
            ],
            // no entitlement
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                'activity-timeline',
                [],
                false,
            ],
            // multiple entitlement
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                'commentlog-dashlet',
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            // other entitlement
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                'commentlog-dashlet',
                ['SUGAR_SALE'],
                false,
            ],
            // not on controlled list
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                'not_controlled_dashlet',
                ['NOT_SERVICE_CLOUD'],
                true,
            ],
            // null paramenter
            [
                [
                    'activity-timeline' => ['SUGAR_SERVE'],
                    'commentlog-dashlet' => ['SUGAR_SERVE'],
                    'dashablerecord' => ['SUGAR_SERVE']
                ],
                null,
                ['SERVICE_CLOUD'],
                true,
            ],
        ];
    }

    /**
     * @covers ::allowFieldAccess
     * @covers ::allowAccess
     * @covers ::isAccessControlled
     *
     * @dataProvider allowFieldAccessProvider
     */
    public function testAllowFieldAccess($access_config, $module, $field, $entitled, $expected)
    {
        $sugarVoterMock = $this->getMockBuilder(SugarFieldVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoterMock->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoterMock->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoter', 'getAccessControlledList'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoter')
            ->will($this->returnValue($sugarVoterMock));

        $acmMock->expects($this->any())
            ->method('getAccessControlledList')
            ->will($this->returnValue($access_config));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowFieldAccess($module, $field));
    }

    public function allowFieldAccessProvider()
    {
        return [
            [
                ['BusinessCenters' => ['field1' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'field1',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['field1' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'field1_no_in_the_list',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['field1' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'field1',
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
            [
                ['BusinessCenters' => ['field1' => ['SUGAR_SERVE']]],
                null,
                'field1',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['field1' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                null,
                ['SUGAR_SERVE'],
                true,
            ],
        ];
    }

    /**
     * @covers ::allowRecordAccess
     * @covers ::allowAccess
     * @covers ::isAccessControlled
     *
     * @dataProvider allowRecordAccessProvider
     */
    public function testAllowRecordAccess($access_config, $module, $id, $entitled, $expected)
    {
        $sugarVoterMock = $this->getMockBuilder(SugarRecordVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoterMock->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoterMock->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoter', 'getAccessControlledList'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoter')
            ->will($this->returnValue($sugarVoterMock));

        $acmMock->expects($this->any())
            ->method('getAccessControlledList')
            ->will($this->returnValue($access_config));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowRecordAccess($module, $id));
    }

    public function allowRecordAccessProvider()
    {
        return [
            [
                ['BusinessCenters' => ['random_id' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'random_id',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['random_id' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'random_id_no_in_the_list',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['random_id' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                'random_id',
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
            [
                ['BusinessCenters' => ['random_id' => ['SUGAR_SERVE']]],
                null,
                'random_id',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BusinessCenters' => ['random_id' => ['SUGAR_SERVE']]],
                'BusinessCenters',
                null,
                ['SUGAR_SERVE'],
                true,
            ],
        ];
    }
}
