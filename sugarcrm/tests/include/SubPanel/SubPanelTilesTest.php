<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/SubPanel/SubPanelTiles.php';

class SubPanelTilesBase extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();

        SugarTestHelper::tearDown();
    }

    /**
     * Set a custom subpanel order for a user, and check if it's returned properly
     *
     * @dataProvider dataProviderCustomSubpanelOrder
     */
    public function testCustomSubpanelOrder($customSubpanelOrder)
    {
        $bean = SugarTestCallUtilities::createCall();

        $GLOBALS['current_user']->setPreference('subpanelLayout', $customSubpanelOrder, 0, $bean->module_dir);

        $tiles = new SubPanelTiles($bean);

        $layout = $tiles->getTabs();

        $this->assertEquals($customSubpanelOrder, $layout, 'SubPanel returned is not correct');
    }

    public static function dataProviderCustomSubpanelOrder()
    {
        return array(
            array(
                array(
                    0 => 'contacts',
                    1 => 'users',
                    2 => 'leads',
                    3 => 'history',
                ),
            ),
            array(
                array(
                    0 => 'users',
                    1 => 'history',
                    2 => 'leads',
                    3 => 'contacts',
                ),
            ),
        );
    }
}
