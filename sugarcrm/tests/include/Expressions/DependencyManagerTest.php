<?php
//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("include/Expressions/DependencyManager.php");

class DependencyManagerTest extends Sugar_PHPUnit_Framework_TestCase {
    var $removeCustomDir = false;
    var $cf_test_field = array(
        'name' => 'cf_field',
        'type' => 'varchar',
        'calculated' => true,
        'formula' => 'strlen($name)'
    );
    var $cf_enforced_field = array(
        'name' => 'cf_enforced_field',
        'type' => 'varchar',
        'calculated' => true,
        'formula' => 'strlen($name)',
        'enforced' => true
    );
    var $dep_field = array(
        'name' => 'dep_field',
        'type' => 'varchar',
        'dependency' => 'strlen($name)',
    );
    var $dd_field = array(
        'name' => 'dd_field',
        'type' => 'enum',
        'options' => 'dd_options',
        'visibility_grid' => array(
            'trigger' => 'dd_trigger',
            'values' => array(
                'one' => array('foo'),
                'two' => array('foo', 'bar')
            )
        ),
    );
    var $dd_trigger = array(
        'name' => 'dd_trigger',
        'type' => 'enum',
        'options' => 'dd_trigger_options',
    );
    var $dd_options = array(
        "foo" => "Foo",
        "bar" => "Bar",
    );
    var $dd_trigger_options = array(
        "one" => "One",
        "two" => "Two",
    );


//Final order for these should be cf1, cf2, cf5, cf3, cf4
    var $reliantCalcFields = array(
        'cf1' => array(
            'name' => "cf1",
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add(1,1)'
        ),
        'cf2' => array(
            'name' => "cf2",
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf1,1)'
        ),
        'cf3' => array(
            'name' => "cf3",
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf5, $cf2)'
        ),
        'cf4' => array(
            'name' => "cf4",
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf2, $cf5, $cf3)'
        ),
        'cf5' => array(
            'name' => "cf5",
            'type' => 'int',
            'calculated' => true,
            'formula' => 'add($cf2, 1)'
        ),
    );



    public function testCFDeps() {
        $fields = array(
            $this->cf_test_field['name'] => $this->cf_test_field,
        );
        $deps = DependencyManager::getCalculatedFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertFalse($dep->getFireOnLoad());

        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];
        $this->assertEquals("SetValue", $aDef['action']);
        $this->assertEquals($this->cf_test_field['name'], $aDef['target']);
        $this->assertEquals($this->cf_test_field['formula'], $aDef['value']);

    }

    public function testCFEnforced() {
        $fields = array(
            $this->cf_enforced_field['name'] => $this->cf_enforced_field,
        );
        $deps = DependencyManager::getCalculatedFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        $this->assertTrue($dep->getFireOnLoad());
    }

    public function testDepFieldDeps() {
        $fields = array(
            $this->dep_field['name'] => $this->dep_field,
        );
        $deps = DependencyManager::getDependentFieldDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertTrue($dep->getFireOnLoad());

        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];
        $this->assertEquals("SetVisibility", $aDef['action']);
        $this->assertEquals($this->dep_field['name'], $aDef['params']['target']);
        $this->assertEquals($this->dep_field['dependency'], $aDef['params']['value']);

    }

    public function testDropDownDeps() {
        global $app_list_strings;
        $app_list_strings['dd_trigger_options'] = $this->dd_trigger_options;
        $app_list_strings['dd_options'] = $this->dd_options;
        $fields = array(
            $this->dd_field['name'] => $this->dd_field,
            $this->dd_trigger['name'] => $this->dd_trigger,
        );

        $deps = DependencyManager::getDropDownDependencies($fields);
        $this->assertFalse(empty($deps));
        $dep = $deps[0];
        //Assert instance of seems to not be definied for the current phpunit version in sugar
        //$this->assertInstanceOf("Dependency", $dep);
        $this->assertTrue($dep->getFireOnLoad());
        $def = $dep->getDefinition();
        $this->assertFalse(empty($def['actions']));
        $aDef = $def['actions'][0];

        $this->assertEquals("SetOptions", $aDef['action']);

        $expectedKeys = 'cond(equal(indexOf($dd_trigger, getDD("dd_trigger_options")), -1), enum(""), valueAt(indexOf($dd_trigger,getDD("dd_trigger_options")),enum(enum("foo"),enum("foo","bar"))))';
        $expectedLabels = '"dd_options"';
        $this->assertEquals($this->dd_field['name'], $aDef['params']['target']);
        $this->assertEquals($expectedKeys, $aDef['params']['keys']);
        $this->assertEquals($expectedLabels, $aDef['params']['labels']);
    }

    public function testCalculatedOrdering()
    {
        $deps = DependencyManager::getCalculatedFieldDependencies($this->reliantCalcFields, false, true);
        $expectedOrder = array("cf1", "cf2", "cf5","cf3", "cf4");
        foreach($deps as $i => $dep)
        {
            $def = $dep->getDefinition();
            $this->assertEquals($def['name'], $expectedOrder[$i]);
        }
    }
}