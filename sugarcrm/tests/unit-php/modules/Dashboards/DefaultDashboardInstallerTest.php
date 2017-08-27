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

require_once 'modules/Dashboards/DefaultDashboardInstaller.php';
/**
 * @coversDefaultClass \DefaultDashboardInstaller
 */
class DefaultDashboardInstallerTest extends \PHPUnit_Framework_TestCase
{
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
            ))
            ->getMock();

        $defaultDashboardInstaller->method('storeDashboard');

        $defaultDashboardInstaller->method('getNewDashboardBean')
            ->willReturn('beanStub');

        $expectTestModule = array(
            'name' => 'Dashboard Name',
            'dashboard_module' => 'TestModule',
            'view_name' => 'records',
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
}
