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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \DefaultDashboardInstaller
 */
class DefaultDashboardInstallerTest extends TestCase
{
    protected function setUp()
    {
        \BeanFactory::setBeanClass('Reports', 'ReportMock');
    }

    protected function tearDown()
    {
        \BeanFactory::unsetBeanClass('Reports');
    }

    /**
     * @covers ::buildDashboardsFromFiles
     */
    public function testBuildDashboardsFromFiles()
    {
        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods(array(
                'getFileContents',
                'storeDashboard',
                'getNewDashboardBean',
                'getSubDirs',
                'getPhpFiles',
            ))
            ->getMock();

        $defaultDashboardInstaller->method('getFileContents')
            ->willReturn(array(
                'name' => 'Dashboard Name',
                'metadata' => array('metadata' => 'dashboard metadata'),
            ));

        $defaultDashboardInstaller->method('storeDashboard');

        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn('beanStub');

        $defaultDashboardInstaller->expects($this->exactly(2))
            ->method('getSubDirs')
            ->will($this->returnCallback(function ($input) {
                return array($input . 'testview');
            }))
            ->withConsecutive(
                array('modules/TestModule/dashboards/'),
                array('modules/Home/dashboards/')
            );

        $defaultDashboardInstaller->expects($this->exactly(2))
            ->method('getPhpFiles')
            ->will($this->returnCallback(function ($input) {
                return array($input . '/test-dashboard.php');
            }))
            ->withConsecutive(
                array('modules/TestModule/dashboards/testview'),
                array('modules/Home/dashboards/testview')
            );

        $expectTestModule = array(
            'name' => 'Dashboard Name',
            'dashboard_module' => 'TestModule',
            'view_name' => 'testview',
            'metadata' => '{"metadata":"dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
        );
        $expectHomeModule = array(
            'name' => 'Dashboard Name',
            'dashboard_module' => 'Home',
            'view_name' => null,
            'metadata' => '{"metadata":"dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
        );

        $defaultDashboardInstaller->expects($this->exactly(2))
            ->method('storeDashboard')
            ->withConsecutive(
                array($this->equalTo('beanStub'), $this->equalTo($expectTestModule)),
                array($this->equalTo('beanStub'), $this->equalTo($expectHomeModule))
            );

        $defaultDashboardInstaller->buildDashboardsFromFiles(array('TestModule', 'Home'));
    }

    /**
     * @covers ::storeDashboard
     */
    public function testStoreDashboard()
    {
        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods()
            ->getMock();

        $dashboardBeanMock = $this->getMockBuilder('Dashboard')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();

        $dashboardBeanMock->method('save');

        $properties = array(
            'key1' => 'value1',
            'key2' => 'value2',
        );

        $dashboardBeanMock->expects($this->once())
            ->method('save');

        $defaultDashboardInstaller->storeDashboard($dashboardBeanMock, $properties);

        $this->assertEquals($dashboardBeanMock->key1, 'value1');
        $this->assertEquals($dashboardBeanMock->key2, 'value2');
    }

    /**
     * @covers ::buildDashboardsFromMetadata
     */
    public function testBuildDashboardsFromMetadata()
    {
        $defaultDashboardInstaller = $this->getMockBuilder('DefaultDashboardInstaller')
            ->setMethods(array(
                'storeDashboard',
                'getNewDashboardBean',
                'getAdminUser',
            ))
            ->getMock();

        $defaultDashboardInstaller->method('storeDashboard');

        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn('beanStub');

        $adminUserMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $adminUserMock->id = 'adminId';

        $defaultDashboardInstaller->method('getAdminUser')
            ->willReturn($adminUserMock);

        $expectTestModule = array(
            'name' => 'Dashboard Name',
            'dashboard_module' => 'TestModule',
            'view_name' => 'records',
            'metadata' => '{"metadata":"dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
            'assigned_user_id' => 'adminId',
            'set_created_by' => false,
            'created_by' => 'adminId',
            'update_modified_by' => false,
            'modified_user_id' => 'adminId',
        );
        $expectHomeModule = array(
            'name' => 'Dashboard Name',
            'dashboard_module' => 'Home',
            'view_name' => null,
            'metadata' => '{"metadata":"dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
            'assigned_user_id' => 'adminId',
            'set_created_by' => false,
            'created_by' => 'adminId',
            'update_modified_by' => false,
            'modified_user_id' => 'adminId',
        );

        $defaultDashboardInstaller->expects($this->exactly(2))
            ->method('storeDashboard')
            ->withConsecutive(
                array($this->equalTo('beanStub'), $this->equalTo($expectTestModule)),
                array($this->equalTo('beanStub'), $this->equalTo($expectHomeModule))
            );

        $testMetadata = array(
            'modules' => array(
                'TestModule' => array(
                    'layouts' => array(
                        'list-dashboard' => array(
                            'meta' => array(
                                'name' => 'Dashboard Name',
                                'metadata' => array('metadata' => 'dashboard metadata'),
                            ),
                        ),
                    ),
                ),
                'Home' => array(
                    'layouts' => array(
                        'record-dashboard' => array(
                            'meta' => array(
                                'name' => 'Dashboard Name',
                                'metadata' => array('metadata' => 'dashboard metadata'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $defaultDashboardInstaller->buildDashboardsFromMetadata($testMetadata);
    }

    /**
     * Data provider for testSetupSavedReportDashlets
     */
    public function setupSavedReportDashletsProvider()
    {
        return array(
            array(
                array(
                    'components' => array(
                        array(
                            'rows' => array(
                                array(
                                    array(
                                        'view' => array(
                                            'type' => 'saved-reports-chart',
                                            'saved_report_key' => 'DEFAULT_REPORT_TITLE_16',
                                            'chart_type' => 'horizontal group by chart',
                                        ),
                                        'width' => 12,
                                    ),
                                ),
                                array(
                                    array(
                                        'view' => array(
                                            'type' => 'opportunity-metrics',
                                            'label' => 'LBL_DASHLET_OPPORTUNITY_NAME',
                                        ),
                                        'width' => 12,
                                    ),
                                ),
                            ),
                            'width' => 12,
                        ),
                    ),
                ),
                array(
                    'components' => array(
                        array(
                            'rows' => array(
                                array(
                                    array(
                                        'view' => array(
                                            'type' => 'saved-reports-chart',
                                            'saved_report_key' => 'DEFAULT_REPORT_TITLE_16',
                                            'chart_type' => 'horizontal group by chart',
                                            'label' => 'saved report',
                                            'saved_report' => 'saved report',
                                            'saved_report_id' => 'report id',
                                        ),
                                        'width' => 12,
                                    ),
                                ),
                                array(
                                    array(
                                        'view' => array(
                                            'type' => 'opportunity-metrics',
                                            'label' => 'LBL_DASHLET_OPPORTUNITY_NAME',
                                        ),
                                        'width' => 12,
                                    ),
                                ),
                            ),
                            'width' => 12,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::setupSavedReportDashlets
     * @dataProvider setupSavedReportDashletsProvider
     */
    public function testSetupSavedReportDashlets($metadata, $expected)
    {
        $defaultDashboardInstaller = $this->createPartialMock(
            'DefaultDashboardInstaller',
            ['translateSavedReportTitle']
        );
        $defaultDashboardInstaller->method('translateSavedReportTitle')->willReturn('saved report');
        $defaultDashboardInstaller->setupSavedReportDashlets($metadata);
        $this->assertSame($expected, $metadata);
    }
}

class ReportMock
{
    public function retrieveReportIdByName($name)
    {
        return 'report id';
    }
}
