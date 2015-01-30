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

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarFilterMetaDataUpgrader.php';
require_once 'tests/modules/UpgradeWizard/SidecarMetaDataUpgraderTest.php';

/**
 * Test for SidecarFilterMetaDataUpgrader.
 */
class SidecarFilterMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test for not removing extra fields from defs while conversion.
     *
     * @param $viewdef
     * @param $vardef
     * @param $result
     * @dataProvider provider
     */
    public function testConvert($viewdef, $vardef, $result)
    {
        $upgrader = new SidecarMetaDataUpgraderForTest();
        $mock = $this->getMock(
            'SidecarFilterMetaDataUpgrader',
            array('loadSearchFields', 'getFieldDefs'),
            array($upgrader, array())
        );
        $mock->expects($this->any())
            ->method('loadSearchFields')
            ->will($this->returnValue($viewdef));
        $mock->expects($this->any())
            ->method('getFieldDefs')
            ->will($this->returnValue($vardef));
        $searchFields = array();
        $searchFields['layout']['basic_search'] = $viewdef;
        SugarTestReflection::setProtectedValue($mock, 'legacyViewdefs', $searchFields);
        $mock->convertLegacyViewDefsToSidecar();

        $fields = $mock->getSidecarViewDefs();
        $this->assertEquals($result, $fields['fields']);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array(
                    'name' => array(
                        'query_type' => 'default',
                        'label' => 'LBL_NAME'
                    ),
                    'account_name' => array(
                        'query_type' => 'default',
                        'label' => 'LBL_ACC',
                        'db_field' => array(
                            'accounts.name',
                        ),
                    ),
                    'wrong_field' => array(
                        'label' => 'LBL_FLD',
                        'db_field' => array()
                    ),
                    'another_field' => array(
                        'label' => 'LBL_FLD2',
                        'db_field' => array('name'),
                        'type' => 'bool',
                    ),
                ),
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'varchar',
                    ),
                    'account_name' => array(
                        'name' => 'account_name',
                        'type' => 'relate',
                    ),
                ),
                array(
                    'name' => array(),
                    'account_name' => array(
                        'dbFields' => array(),
                        'vname' => 'LBL_ACC',
                        'type' => 'text',
                    ),
                    'another_field' => array(
                        'dbFields' => array('name'),
                        'vname' => 'LBL_FLD2',
                        'type' => 'bool',
                    ),
                    '$owner' => array(
                        'predefined_filter' => 1,
                        'vname' => 'LBL_CURRENT_USER_FILTER',
                    ),
                    '$favorite' => array(
                        'predefined_filter' => 1,
                        'vname' => 'LBL_FAVORITES_FILTER',
                    )
                ),
            ),
        );
    }
}
