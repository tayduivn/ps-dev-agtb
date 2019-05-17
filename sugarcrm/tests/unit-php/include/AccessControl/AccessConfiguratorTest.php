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
use Sugarcrm\Sugarcrm\AccessControl\AccessConfigurator;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;

/**
 * Class AccessConfiguratorTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\AccessConfigurator
 */
class AccessConfiguratorTest extends TestCase
{
    /**
     * @covers ::getAccessControlledList
     *
     * @dataProvider getAccessControlledListProvider
     */
    public function testGetAccessControlledList($key, $expected)
    {
        $access_config = [
            'MODULES' => [
                'BusinessCenters' => ['SUGAR_SERVE'],
                'CampaignLog' => ['CURRENT'],
                'CampaignTrackers' => ['CURRENT'],
            ],
            'DASHLETS' => [
                'workbench' => ['SUGAR_SERVE'],
            ],
            'RECORDS' => [
                ['Dashboards' => ['c108bb4a-775a-11e9-b570-f218983a1c3e' => 'SUGAR_SERVE']],
                [
                    'Reports' => [
                        ['protected_report_name1_id' => 'SUGAR_SERVE'],
                        ['protected_report_name2_id' => 'SUGAR_SERVE'],
                    ],
                ],
            ],
            'FIELDS' => [
                'Accounts' => ['field1' => 'SUGAR_SERVE'],
            ],
        ];

        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($access_config));

        $this->assertSame($expected, $configuratorMock->getAccessControlledList($key, false));
    }

    public function getAccessControlledListProvider()
    {
        return [
            [
                'MODULES',
                [
                    'BusinessCenters' => ['SUGAR_SERVE'],
                    'CampaignLog' => ['CURRENT'],
                    'CampaignTrackers' => ['CURRENT'],
                ],
            ],
            [
                'DASHLETS',
                [
                    'workbench' => ['SUGAR_SERVE'],
                ],
            ],
            [    'RECORDS',
                [
                    [
                        'Dashboards' => ['c108bb4a-775a-11e9-b570-f218983a1c3e' => 'SUGAR_SERVE']
                    ],
                    [
                        'Reports' => [
                            ['protected_report_name1_id' => 'SUGAR_SERVE'],
                            ['protected_report_name2_id' => 'SUGAR_SERVE'],
                        ],
                    ],
                ],
            ],
            [
                'NOT_EXIST',
                []
            ],
        ];
    }

    /**
     * @covers ::getNotAcceccibleModuleListByLicenseTypes
     *
     * @dataProvider getAccessControlledModuleListProvider
     */
    public function testGetAccessControlledModuleListByTypes($access_config, $types, $expected)
    {
        $configuratorMock = $this->getMockBuilder(AccessConfigurator::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAccessConfig'])
            ->getMock();

        $configuratorMock->expects($this->any())
            ->method('loadAccessConfig')
            ->will($this->returnValue($access_config));

        $this->assertSame($expected, $configuratorMock->getNotAcceccibleModuleListByLicenseTypes($types, false));
    }

    public function getAccessControlledModuleListProvider()
    {
        return [
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SALE', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SERVE'],
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                    'Opportunities' => true,
                ],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SALE', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SERVE', 'CURRENT'],
                [],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SALE', 'CURRENT'],
                    ],
                ],
                ['SUGAR_SALE'],
                [
                    'BusinessCenters' => true,
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
            ],
            [
                [
                    'MODULES' => [
                        'BusinessCenters' => ['SUGAR_SERVE'],
                        'CampaignLog' => ['CURRENT'],
                        'CampaignTrackers' => ['CURRENT'],
                        'Opportunities' => ['SUGAR_SALE', 'CURRENT'],
                    ],
                ],
                ['NOT_IN_THE_LIST'],
                [
                    'BusinessCenters' => true,
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                    'Opportunities' => true,
                ],
            ],
        ];
    }

    /**
     * @covers ::instance
     * @covers ::loadAccessConfig
     */
    public function testLoadAccessConfig()
    {
        $this->assertNotEmpty(
            AccessConfigurator::instance()->getAccessControlledList(AccessControlManager::FIELDS_KEY, false)
        );
    }

    /**
     * @covers ::getNotAcceccibleModuleListByLicenseTypes
     */
    public function testGetNotAcceccibleModuleListByLicenseTypes()
    {
        $this->assertNotEmpty(
            AccessConfigurator::instance()->getNotAcceccibleModuleListByLicenseTypes(['SUGAR_SERVE'], false)
        );
    }
}
