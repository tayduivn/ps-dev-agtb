<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'modules/Relationships/Relationship.php';
require_once 'data/Relationships/RelationshipFactory.php';

/*
 * What is the DrPhilTest?
 * It's a test that runs through the metadata of the system and
 * verifies that the metadata is correct.
 * It was named DrPhilTest becase while it may find problems in your relationships
 * it does not attempt to fix them.
 *
 * If this test fails you are on the honor system to view this image: http://i.imgur.com/fMpZ4Rb.jpg
 */
class DrPhilTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    protected function getValidModules()
    {
        static $validModules;

        if (!isset($validModules)) {
            SugarTestHelper::setUp('beanList');
            SugarTestHelper::setUp('app_list_strings');

            $validModules = array();

            $invalidModules = array(
                'DynamicFields',
                'Connectors',
                'CustomFields',
                'TeamHierarchy',
                'Empty',
                'Audit',
                'MergeRecords',
            );


            foreach ( array_keys($GLOBALS['beanList']) as $moduleName ) {
                if ( in_array($moduleName,$invalidModules) ) {
                    continue;
                }
                $validModules[] = $moduleName;
            }
        }

        return $validModules;
    }

    protected function getSeedBean( $moduleName )
    {
        static $seedBeans = array();

        if (!isset($seedBeans[$moduleName])) {
            $seedBeans[$moduleName] = BeanFactory::newBean($moduleName);
        }

        return $seedBeans[$moduleName];
    }

    public function provideValidModules()
    {
        static $validModulesDataSet;

        if (!isset($validModulesDataSet)) {
            $validModulesDataSet = array();

            $validModules = $this->getValidModules();
            foreach ( $validModules as $module ) {
                $validModulesDataSet[] = array($module);
            }
        }

        return $validModulesDataSet;
    }

    /**
     * @group SanityCheck
     * @dataProvider provideValidModules
     */
    public function testCanLoadModules( $moduleName )
    {

        $bean = BeanFactory::newBean($moduleName);
        $this->assertNotNull($bean,"Could not load bean: $moduleName");
    }

    /**
     * @group SanityCheck
     * @dataProvider provideValidModules
     */
    public function testFieldDefs($moduleName)
    {
        $bean = $this->getSeedBean($moduleName);
        $this->assertTrue(is_array($bean->field_defs), "No field defs for {$bean->module_dir}");

        foreach ($bean->field_defs as $key => $def) {
            $this->assertArrayHasKey('name', $def, "Def for {$bean->module_dir}/$key is missing a name attribute");
            $this->assertEquals($key, $def['name'], "Def's name for {$bean->module_dir}/$key doesn't match the key");

            $this->assertArrayHasKey('type', $def, "Def for {$bean->module_dir}/$key is missing a type");

            // Teams operate in their own weird way
            if ($key == 'team_name') {
                continue;
            }

            if ($def['type'] == 'relate'
                || (isset($def['source'])
                    && $def['source'] == 'non-db'
                    && !empty($def['link'])) ) {
                // These are related items, they get checked differently
                continue;
            }

            if (!empty($def['rname'])
                && $def['type'] != 'link'
                && !empty($def['source'])
                && $def['source'] == 'non-db' ) {
                $this->assertTrue(!empty($def['link']), "Def for {$bean->module_dir}/{$key} has an rname, but no link");
            }

            if (isset($def['sort_on'])) {
                // Sort on can be either a string or an array... make it an array
                // for testing
                if (is_string($def['sort_on'])) {
                    $def['sort_on'] = array($def['sort_on']);
                }

                // Loop and test
                foreach ($def['sort_on'] as $sortField) {
                    $this->assertArrayHasKey($sortField, $bean->field_defs, "Sort on for {$bean->module_dir}/$key points to an invalid field.");
                }
            }

            if (isset($def['fields'])) {
                foreach ($def['fields'] as $subField) {
                    $this->assertArrayHasKey($subField, $bean->field_defs, "Sub field $subField for {$bean->module_dir}/$key points to an invalid field.");
                }
            }

            if (isset($def['db_concat_fields'])) {
                foreach ($def['db_concat_fields'] as $subField) {
                    $this->assertArrayHasKey($subField, $bean->field_defs, "DB concat field $subField for {$bean->module_dir}/$key points to an invalid field.");
                }
            }
        }
    }

    public function provideLinkFields()
    {
        $moduleList = $this->getValidModules();

        $linkFields = array();

        $oneWayRelationships = array(
            'activities',
            'created_by_link',
            'modified_user_link',
            'assigned_user_link',
            'teams',
            'emails',
            'email_addresses_primary',
            'email_addresses',
            'team_link',
            'team_count_link',
            'favorite_link',
            'following_link',
        );

        foreach ( $moduleList as $module ) {
            $bean = $this->getSeedBean($module);
            if (!is_array($bean->field_defs)) {
                continue;
            }

            foreach ($bean->field_defs as $linkName => $def) {
                if ($def['type'] != 'link') {
                    continue;
                }

                // There are some relationships that don't link both ways
                if (in_array($linkName,$oneWayRelationships)) {
                    continue;
                }

                $linkFields[] = array($module, $linkName);
            }
        }

        return $linkFields;
    }

    /**
     * @group SanityCheck
     * @dataProvider provideLinkFields
     */
    public function testLinkFields($moduleName, $linkName)
    {
        $bean = $this->getSeedBean($moduleName);

        $bean->load_relationship($linkName);
        $this->assertNotNull($bean->$linkName,"Could not load link {$bean->module_dir}/{$linkName}");

        $relatedModuleName = $bean->$linkName->getRelatedModuleName();
        $this->assertNotNull($relatedModuleName,"Could not figure out the related module name for link {$bean->module_dir}/{$linkName}");

        $relatedBean = $this->getSeedBean($relatedModuleName);
        $this->assertNotNull($relatedBean,"Could not load related module ({$relatedModuleName}) for link {$bean->module_dir}/{$linkName}");

        return;

        // The following tests make sure that the relationship has both ends.
        // the world is too cruel for these tests right now.
        static $allowedOneWay = array(
            'Emails' => 'Emails',
            'Users' => 'Users',
            'Activities' => 'Activities',
        );

        if (isset($allowedOneWay[$relatedModuleName])) {
            return;
        }

        $relatedLinkName = $bean->$linkName->getRelatedModuleLinkName();
        $this->assertNotNull($relatedLinkName,"Could not load related module's link record for link {$bean->module_dir}/{$linkName}");

        $relatedBean->load_relationship($relatedLinkName);
        $this->assertNotNull($relatedBean->$relatedLinkName,"Could not load related module link {$relatedBean->module_dir}/${relatedLinkName}");

    }

    /**
     * Test that moduleList and moduleListSingular are in sync
     */
    public function testModuleList()
    {
        $diff = array_diff(array_keys($GLOBALS['app_list_strings']['moduleList']), array_keys($GLOBALS['app_list_strings']['moduleListSingular']));
        $this->assertEquals(array(), $diff, "Key lists do not match");
    }
}
