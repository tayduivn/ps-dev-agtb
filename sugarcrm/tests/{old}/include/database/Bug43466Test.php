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

class Bug43466 extends TestCase
{
    /**
     * @var DBManager
     */
    private $_db;

    protected function setUp()
    {
        $this->_db = DBManagerFactory::getInstance();
    }

    /**
     * @dataProvider matchingIndexProvider
     */
    public function testMatchingIndexDoesNotGenerateSql($indices)
    {
        $sql = $this->_db->repairTableParams('calls', array(
            'name' => array(),
        ), $indices, false);

        $this->assertEquals('', $sql);
    }

    public static function matchingIndexProvider()
    {
        return array(
            array(
                array(
                    array(
                        'name' => 'idx_call_name',
                        'type' => 'index',
                        'fields'=> array('name'),
                    ),
                    array(
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields'=> array('status'),
                    ),
                    array(
                        'name' => 'idx_CALLS_date_Start',
                        'type' => 'index',
                        'fields' => array('date_start'),
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'name' => 'idx_call_name2',
                        'type' => 'index',
                        'fields' => array('name'),
                    ),
                    array(
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => array('status'),
                    ),
                    array(
                        'name' => 'idx_CALLS_date_Start',
                        'type' => 'index',
                        'fields' => array('date_start'),
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'name' => 'iDX_cAll_NAMe',
                        'type' => 'index',
                        'fields' => array('name'),
                    ),
                    array(
                        'name' => 'idx_STAtus',
                        'type' => 'index',
                        'fields' => array('status'),
                    ),
                    array(
                        'name' => 'idx_CALLS_date_Start',
                        'type' => 'index',
                        'fields' => array('date_start'),
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'name' => 'idx_call_name',
                        'type' => 'index',
                        'fields' => array('name'),
                    ),
                    array(
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => array('status'),
                    ),
                    array(
                        'name' => 'idx_calls_date_start2',
                        'type' => 'index',
                        'fields' => array('date_start'),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider nonMatchingIndexProvider
     */
    public function testNonMatchingIndexGeneratesSql($indices)
    {
        $sql = $this->_db->repairTableParams('calls', array(
            'name' => array(),
        ), $indices, false);

        $this->assertNotEquals('', $sql);
    }

    public static function nonMatchingIndexProvider()
    {
        return array(
            array(
                array(
                    array(
                        'name' => 'idx_call_name2',
                        'type' => 'index',
                        'fields'=> array('name', 'status'),
                    ),
                    array(
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields'=> array('status'),
                    ),
                    array(
                        'name' => 'idx_calls_date_start',
                        'type' => 'index',
                        'fields' => array('date_start'),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider matchingVarDefProvider
     */
    public function testMatchingVarDefs(array $a, array $b)
    {
        $this->assertTrue($this->_db->compareVarDefs($a, $b));
    }

    public static function matchingVarDefProvider()
    {
        return array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                array(
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                ),
            ),
        );
    }

    /**
     * @dataProvider nonMatchingVarDefProvider
     */
    public function testNonMatchingVarDefs(array $a, array $b)
    {
        $this->assertFalse($this->_db->compareVarDefs($a, $b));
    }

    public static function nonMatchingVarDefProvider()
    {
        return array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                array(
                    'name' => 'foo2',
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                ),
                array(
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
        );
    }
}
