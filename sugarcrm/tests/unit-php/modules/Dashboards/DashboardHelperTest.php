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

require_once 'modules/Dashboards/DashboardHelper.php';
/**
 * @coversDefaultClass \DashboardHelper
 */
class DashboardHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::prepareDashboardModules
     */
    public function testPrepareDashboardModules()
    {
        $dashboardHelperMock = $this->getMockBuilder('DashboardHelper')
            ->setMethods(array('checkModuleAccess'))
            ->disableOriginalConstructor()
            ->getMock();

        $dashboardHelperMock->expects($this->exactly(3))
            ->method('checkModuleAccess')
            ->will($this->returnCallback(
                function ($module) {
                    return $module === 'AllowedModule' ||
                        $module === 'SecondAllowedModule';
                }
            ));

        $moduleList = array(
            'SecondAllowedModule',
            'AllowedModule',
            'DeniedModule',
        );

        $translations = array(
            'SecondAllowedModule' => 'Second Allowed Module',
            'AllowedModule' => 'Allowed Module',
            'DeniedModule' => 'Denied Module',
        );

        $expected = array(
            'AllowedModule' => 'Allowed Module',
            'SecondAllowedModule' => 'Second Allowed Module',
        );

        $preparedModules = $dashboardHelperMock->prepareDashboardModules($moduleList, $translations);

        $this->assertTrue($expected === $preparedModules);
    }
}
