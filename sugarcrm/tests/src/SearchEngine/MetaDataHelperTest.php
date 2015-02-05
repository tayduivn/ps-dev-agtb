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

namespace Sugarcrm\SugarcrmTest\SearchEngine\MetaDataHelper;

/**
 * Test for the generic Logger.
 */
class MetaDataHelperTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test getting full time search fields.
     * @param array $module : the name of modules
     * @param array $vardef : the fields from getModuleVardefs()
     * @param array $result : the expected fields
     * @dataProvider providerGetFtsFields
     */
    public function testGetFtsFields($module, $vardef, $result)
    {
        $helper = $this->getMock(
            'Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper',
            array('getModuleVardefs')
        );
        $helper->expects($this->any())
            ->method('getModuleVardefs')
            ->will($this->returnValue($vardef));

        $fields = $helper->getFtsFields($module);
        $this->assertEquals($result, $fields);
    }

    /**
     * Data provider to test getFtsFields().
     * @return array
     */
    public function providerGetFtsFields()
    {
        return array(
            array(
                'Tasks',
                array(
                'fields' => array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'description' => array(
                        'name' => 'description',
                        'type' => 'text',
                    ),
                    'work_log' => array(
                        'name' => 'work_log',
                        'type' => 'text',
                        'full_text_search' => array('enabled' => false),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
                'indices' => array(),
                'relationship' => array(),
                ),
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'varchar',
                        'full_text_search' => array('enabled' => true, 'searchable' => false, 'type' => 'varchar'),
                    ),
                ),
            ),
        );
    }

}
