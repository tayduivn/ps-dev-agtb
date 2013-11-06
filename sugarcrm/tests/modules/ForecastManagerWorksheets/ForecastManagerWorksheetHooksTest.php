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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/ForecastManagerWorksheets/ForecastManagerWorksheetHooks.php';

class ForecastManagerWorksheetHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderSetManagerSavedFlag
     * @param array $data
     * @param boolean $expected
     */
    public function testSetManagerSavedFlag($data, $expected)
    {
        /** @var ForecastManagerWorksheet $worksheet */
        $worksheet = $this->getMock('ForecastManagerWorksheet', array('save'));

        foreach ($data as $key => $value) {
            $worksheet->$key = $value;
        }

        ForecastManagerWorksheetHooks::setManagerSavedFlag($worksheet, 'before_save');

        $this->assertEquals($expected, $worksheet->manager_saved);
    }

    public static function dataProviderSetManagerSavedFlag()
    {
        return array(
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                true
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => true
                ),
                true
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user_1',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false
                ),
                false
            )
        );
    }
}
