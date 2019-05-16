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
     * @dataProvider providerBuildDashboardsFromFiles
     * @param array $fileContents Contents of the default dashboard file.
     * @param string $dashboardsDir Dashboards directory (relative path).
     * @param string $dashboardDir Dashboard directory (relative path,
     *   including $dashboardsDir as a prefix).
     * @param string $module Module name.
     * @param array $expected Expected bean properties for the dashboard.
     */
    public function testBuildDashboardsFromFiles(
        array $fileContents,
        string $dashboardsDir,
        string $dashboardDir,
        string $module,
        array $expected
    ) {
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
            ->willReturn($fileContents);

        $defaultDashboardInstaller->method('storeDashboard');

        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn('beanStub');

        $defaultDashboardInstaller->expects($this->once())
            ->method('getSubDirs')
            ->will($this->returnCallback(function ($input) {
                return [$input . 'testview'];
            }))
            ->with($dashboardsDir);

        $defaultDashboardInstaller->expects($this->once())
            ->method('getPhpFiles')
            ->will($this->returnCallback(function ($input) {
                return [$input . '/test-dashboard.php'];
            }))
            ->with($dashboardDir);

        $defaultDashboardInstaller->expects($this->once())
            ->method('storeDashboard')
            ->with($this->equalTo('beanStub'), $this->equalTo($expected));

        $defaultDashboardInstaller->buildDashboardsFromFiles([$module]);
    }

    public function providerBuildDashboardsFromFiles(): array
    {
        // basic dashboard
        $basicDashboardFileContents = [
            'name' => 'Test Module Dashboard Name',
            'metadata' => ['metadata' => 'test module dashboard metadata'],
        ];
        $basicDashboardsDir = 'modules/TestModule/dashboards/';
        $basicDashboardDir = 'modules/TestModule/dashboards/testview';
        $basicExpected = [
            'name' => 'Test Module Dashboard Name',
            'dashboard_module' => 'TestModule',
            'view_name' => 'testview',
            'metadata' => '{"metadata":"test module dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
        ];

        // home dashboard
        $homeDashboardFileContents = [
            'name' => 'Home Module Dashboard Name',
            'metadata' => ['metadata' => 'Home module dashboard metadata'],
        ];
        $homeDashboardsDir = 'modules/Home/dashboards/';
        $homeDashboardDir = 'modules/Home/dashboards/testview';
        $homeExpected = [
            'name' => 'Home Module Dashboard Name',
            'dashboard_module' => 'Home',
            'view_name' => null,
            'metadata' => '{"metadata":"Home module dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
        ];

        // dashboard with predefined ID
        $dashboardWithIDFileContents = [
            'id' => 'i-am-a-predefined-id',
            'name' => 'Test Module 2 Dashboard Name',
            'metadata' => ['metadata' => 'test module 2 dashboard metadata'],
        ];
        $dashboardsWithIDsDirectory = 'modules/TestModule2/dashboards/';
        $dashboardWithIDDirectory = 'modules/TestModule2/dashboards/testview';
        $idExpected = [
            'id' => 'i-am-a-predefined-id',
            'new_with_id' => true,
            'name' => 'Test Module 2 Dashboard Name',
            'dashboard_module' => 'TestModule2',
            'view_name' => 'testview',
            'metadata' => '{"metadata":"test module 2 dashboard metadata"}',
            'default_dashboard' => true,
            'team_id' => '1',
        ];

        return [
            [$basicDashboardFileContents, $basicDashboardsDir, $basicDashboardDir, 'TestModule', $basicExpected],
            [$homeDashboardFileContents, $homeDashboardsDir, $homeDashboardDir, 'Home', $homeExpected],
            [
                $dashboardWithIDFileContents,
                $dashboardsWithIDsDirectory,
                $dashboardWithIDDirectory,
                'TestModule2',
                $idExpected,
            ],
        ];
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
