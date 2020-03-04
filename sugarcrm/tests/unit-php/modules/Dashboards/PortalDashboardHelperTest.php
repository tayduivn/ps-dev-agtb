<?php
//FILE SUGARCRM flav=ent ONLY
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
namespace Sugarcrm\SugarcrmTestsUnit\modules\Dashboards;

use PHPUnit\Framework\TestCase;
use PortalDashboardHelper;
use SugarApiExceptionNotAuthorized;

/**
 * @coversDefaultClass \PortalDashboardHelper
 */
class PortalDashboardHelperTest extends TestCase
{
    private $helper;
    private $bean;
    private $platform;

    protected function setUp()
    {
        $this->helper = new PortalDashboardHelper();
        $this->bean = $this->createMock('Dashboard');

        if (isset($_SESSION['platform'])) {
            $this->platform = $_SESSION['platform'];
        }
    }
    
    protected function tearDown()
    {
        if (isset($this->platform)) {
            $_SESSION['platform'] = $this->platform;
        } elseif (isset($_SESSION['platform'])) {
            unset($_SESSION['platform']);
        }
    }

    /**
     * @covers ::removePortalDashboards
     */
    public function testRemovePortalDashboards()
    {
        $_SESSION['platform'] = 'base';
        $sugarQuery = $this->createMock('SugarQuery');
        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);
        $sugarQueryWhere->expects($this->once())
            ->method('notIn')
            ->with($this->equalTo('id'), $this->equalTo(PortalDashboardHelper::$portalDashboards));
        $this->helper->removePortalDashboards($this->bean, 'before_filter', [$sugarQuery]);

        $_SESSION['platform'] = 'portal';
        $sugarQuery = $this->createMock('SugarQuery');
        $sugarQuery->expects($this->never())
            ->method($this->anything());
        $this->helper->removePortalDashboards($this->bean, 'before_filter', [$sugarQuery]);
    }

    /**
     * @covers ::checkPortalDashboard
     */
    public function testCheckPortalDashboard()
    {
        $_SESSION['platform'] = 'portal';
        $this->helper->checkPortalDashboard(
            $this->bean,
            'before_retrieve',
            ['id' => '0ca2d773-0bb3-4bf3-ae43-68569968af57']
        );
        $this->assertTrue(true);

        $_SESSION['platform'] = 'base';
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->helper->checkPortalDashboard(
            $this->bean,
            'before_retrieve',
            ['id' => '0ca2d773-0bb3-4bf3-ae43-68569968af57']
        );
    }
}
