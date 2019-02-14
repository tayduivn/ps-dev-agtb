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

class BusinessCenterTest extends TestCase
{
    private $manager;

    protected static $fieldDefs = [];

    public static function setupBeforeClass()
    {
        static::$fieldDefs = \VardefManager::getFieldDefs('BusinessCenters');
    }

    public function setUp()
    {
        $this->manager = \MetaDataManager::getManager();
    }

    public function tearDown()
    {
        \MetaDataManager::resetManagers();
    }

    /**
     * @dataProvider providerHasFields
     * @param array $fields Field definitions.
     */
    public function testHasFields(array $fields)
    {
        $fieldKey = array_keys($fields)[0];
        $this->assertArrayHasKey($fieldKey, static::$fieldDefs);

        $fieldDef = static::$fieldDefs[$fieldKey];
        $this->assertEquals($fields[$fieldKey]['name'], $fieldDef['name']);
        $this->assertEquals($fields[$fieldKey]['type'], $fieldDef['type']);
    }

    public function providerHasFields(): array
    {
        return [
            [
                array(
                    'timezone' => array(
                        'name' => 'timezone',
                        'type' => 'enum',
                    ),
                ),
            ],
            [
                array(
                    'address_street' => array(
                        'name' => 'address_street',
                        'type' => 'text',
                    ),
                ),
            ],
            [
                array(
                    'address_city' => array(
                        'name' => 'address_city',
                        'type' => 'varchar',
                    ),
                ),
            ],
            [
                array(
                    'address_state' => array(
                        'name' => 'address_state',
                        'type' => 'varchar',
                    ),
                ),
            ],
            [
                array(
                    'address_postalcode' => array(
                        'name' => 'address_postalcode',
                        'type' => 'varchar',
                    ),
                ),
            ],
            [
                array(
                    'address_country' => array(
                        'name' => 'address_country',
                        'type' => 'varchar',
                    ),
                ),
            ],
        ];
    }

    /**
     * Checks that the desired fields are on the record view.
     *
     * @param string $fieldName Name of the field which we would like to
     *   confirm is on the record view.
     * @dataProvider hasFieldOnRecordViewProvider
     */
    public function testCheckHasFieldOnRecordView(string $fieldName)
    {
        $this->assertContains($fieldName, $this->manager->getModuleViewFields('BusinessCenters', 'record'));
    }

    public function hasFieldOnRecordViewProvider(): array
    {
        return [
            ['timezone'],
            ['address_street'],
            ['address_city'],
            ['address_state'],
            ['address_postalcode'],
            ['address_country'],
        ];
    }
}
