<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/MetaDataManager/MetaDataHacks.php';

class MetaDataHacksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The metadata hacks class
     * @var MetaDataHacks
     */
    protected $mdh;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->mdh = new MetaDataHacks();
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testFixRelateFields()
    {
        $fieldDefs = array(
            'name' => array(
                'type' => 'string',
                'dbType' => 'varchar',
            ),
            'myawesome_id' => array(
                'type' => 'relate',
                'dbType' => 'id',
            ),
        );

        $fieldDefsNew = $this->mdh->fixRelateFields($fieldDefs);
        $this->assertEquals(
            $fieldDefs['name']['type'],
            $fieldDefsNew['name']['type'],
            "Name changed, it shouldn't have."
        );
        $this->assertNotEquals(
            $fieldDefs['myawesome_id']['type'],
            $fieldDefsNew['myawesome_id']['type'],
            "the id field didn't change, it should have."
        );
        $this->assertEquals('id', $fieldDefsNew['myawesome_id']['type'], "Type field of ID is not correct.");

    }

}

