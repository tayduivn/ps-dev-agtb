<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
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

        // History was ommitted so check the resulting array is the data-set plus history (which was automatically added).
        $this->assertEquals(array_merge($customSubpanelOrder, array('history')), $layout, 'SubPanel returned is not correct');
    }

    public static function dataProviderCustomSubpanelOrder()
    {
        return array(
            array(
                array(
                    0 => 'contacts',
                    1 => 'users',
                    2 => 'leads',
                ),
            ),
            array(
                array(
                    0 => 'users',
                    1 => 'leads',
                    2 => 'contacts',
                ),
            ),
        );
    }
}
