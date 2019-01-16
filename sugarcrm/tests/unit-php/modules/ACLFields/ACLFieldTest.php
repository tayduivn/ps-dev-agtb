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

namespace Sugarcrm\SugarcrmTestUnit\modules\ACLFields;

use PHPUnit\Framework\TestCase;

/**
 * Class ACLFieldTest
 * @coversDefaultClass \ACLField
 */
class ACLFieldTest extends TestCase
{
    private $oldDictionary;

    public function setUp()
    {
        $this->oldDictionary = $GLOBALS['dictionary'] ?? null;
    }

    public function tearDown()
    {
        if (!empty($this->oldDictionary)) {
            $GLOBALS['dictionary'] = $this->oldDictionary;
        }
    }

    /**
     * @param string $moduleName Module name.
     * @param array $fieldDefs Field definitions.
     * @param array $expected Expected ACL fields.
     * @covers ::getAvailableFields
     * @dataProvider providerGetAvailableFields
     */
    public function testGetAvailableFields(string $moduleName, array $fieldDefs, array $expected)
    {
        $GLOBALS['dictionary'] = array(
            $moduleName => array(
                'fields' => $fieldDefs,
            ),
        );

        $objectName = $moduleName;
        $actual = \ACLField::getAvailableFields($moduleName, $objectName);
        $this->assertEquals($expected, $actual);
    }

    public function providerGetAvailableFields(): array
    {
        // general field
        $myfield = array(
            'name' => 'myfield',
            'type' => 'varchar',
            'vname' => 'LBL_MYFIELD',
        );

        // testing that name fields get required => true
        $nameField = array(
            'name' => 'name',
            'type' => 'name',
            'dbType' => 'varchar',
            'vname' => 'LBL_NAME',
        );

        // testing that fields without vname's get a blank label assigned
        $noVname = array(
            'name' => 'novname',
            'type' => 'varchar',
        );

        // testing that required fields are marked as such
        $required = array(
            'name' => 'myrequiredfield',
            'type' => 'varchar',
            'vname' => 'LBL_MY_REQUIRED_FIELD',
            'required' => true,
        );

        // testing that collection fields are allowed
        $collection = array(
            'name' => 'mycollectionfield',
            'type' => 'collection',
            'vname' => 'LBL_MY_COLLECTION_FIELD',
        );

        // testing that explicitly excluded fields are excluded
        $excluded = array(
            'name' => 'aclexcluded',
            'type' => 'varchar',
            'vname' => 'LBL_ACL_EXCLUDED_FIELD',
            'hideacl' => true,
        );

        // testing that custom_fields are included
        $custom = array(
            'name' => 'mycustomfield',
            'type' => 'varchar',
            'source' => 'custom_fields',
            'vname' => 'LBL_MY_CUSTOM_FIELD',
        );

        // testing that custom ID fields are not included
        $customId = array(
            'name' => 'mycustomid',
            'type' => 'id',
            'source' => 'custom_fields',
            'vname' => 'LBL_MY_CUSTOM_ID',
        );

        // testing that groups are included
        $grouped = array(
            'name' => 'mygroupedfield',
            'type' => 'varchar',
            'vname' => 'LBL_MY_GROUPED_FIELD',
            'group' => 'mygroupedfield',
        );

        // testing that custom fields without type or dbType are included
        $customTypelessField = array(
            'name' => 'customtypelessfield',
            'source' => 'custom_fields',
            'vname' => 'LBL_MY_CUSTOM_TYPELESS_FIELD',
        );

        // testing that if the array index is uppercase, it gets lowercased in the output
        // (important part is in the array argument, not here)
        $upperCaseField = array(
            'name' => 'uppercasefield',
            'type' => 'varchar',
            'vname' => 'LBL_UPPER_CASE_FIELD',
        );

        return [
            [
                // module name
                'mymod',

                // input
                array(
                    'myfield' => $myfield,
                    'name' => $nameField,
                    'novname' => $noVname,
                    'myrequiredfield' => $required,
                    'mycollectionfield' => $collection,
                    'aclexcluded' => $excluded,
                    'mycustomfield' => $custom,
                    'mycustomid' => $customId,
                    'mygroupedfield' => $grouped,
                    'customtypelessfield' => $customTypelessField,
                    'UPPERCASEARRAYINDEX' => $upperCaseField,
                ),

                // output
                array(
                    'myfield' => array(
                        'name' => 'myfield',
                        'id' => 'myfield',
                        'required' => false,
                        'key' => 'myfield',
                        'label' => 'LBL_MYFIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'myfield' => 'LBL_MYFIELD',
                        ),
                    ),
                    'name' => array(
                        'name' => 'name',
                        'id' => 'name',
                        'required' => true,
                        'key' => 'name',
                        'label' => 'LBL_NAME',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'name' => 'LBL_NAME',
                        ),
                    ),
                    'novname' => array(
                        'name' => 'novname',
                        'id' => 'novname',
                        'required' => false,
                        'key' => 'novname',
                        'label' => '',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'novname' => '',
                        ),
                    ),
                    'myrequiredfield' => array(
                        'name' => 'myrequiredfield',
                        'id' => 'myrequiredfield',
                        'required' => true,
                        'key' => 'myrequiredfield',
                        'label' => 'LBL_MY_REQUIRED_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'myrequiredfield' => 'LBL_MY_REQUIRED_FIELD',
                        ),
                    ),
                    'mycollectionfield' => array(
                        'name' => 'mycollectionfield',
                        'id' => 'mycollectionfield',
                        'required' => false,
                        'key' => 'mycollectionfield',
                        'label' => 'LBL_MY_COLLECTION_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'mycollectionfield' => 'LBL_MY_COLLECTION_FIELD',
                        ),
                    ),
                    'mycustomfield' => array(
                        'name' => 'mycustomfield',
                        'id' => 'mycustomfield',
                        'required' => false,
                        'key' => 'mycustomfield',
                        'label' => 'LBL_MY_CUSTOM_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'mycustomfield' => 'LBL_MY_CUSTOM_FIELD',
                        ),
                    ),
                    'mygroupedfield' => array(
                        'name' => 'mygroupedfield',
                        'id' => 'mygroupedfield',
                        'required' => false,
                        'key' => 'mygroupedfield',
                        'label' => 'LBL_MY_GROUPED_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'mygroupedfield' => 'LBL_MY_GROUPED_FIELD',
                        ),
                    ),
                    'customtypelessfield' => array(
                        'name' => 'customtypelessfield',
                        'id' => 'customtypelessfield',
                        'required' => false,
                        'key' => 'customtypelessfield',
                        'label' => 'LBL_MY_CUSTOM_TYPELESS_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'customtypelessfield' => 'LBL_MY_CUSTOM_TYPELESS_FIELD',
                        ),
                    ),
                    'uppercasearrayindex' => array(
                        'name' => 'uppercasearrayindex',
                        'id' => 'uppercasearrayindex',
                        'required' => false,
                        'key' => 'uppercasearrayindex',
                        'label' => 'LBL_UPPER_CASE_FIELD',
                        'category' => 'mymod',
                        'role_id' => '',
                        'aclaccess' => 0,
                        'fields' => array(
                            'uppercasearrayindex' => 'LBL_UPPER_CASE_FIELD',
                        ),
                    ),
                ),
            ],
        ];
    }
}
