<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once ('modules/DynamicFields/FieldCases.php');

class Bug46152_P5Test extends Sugar_PHPUnit_Framework_TestCase
{

    static private $moduleBuilder;

    static private $packageName = 'pnb46152';
    static private $moduleName = 'modb46152';

    static private $fieldName = 'fldb46152';

    private $field;
    private $relatedModule = 'Opportunities';
    private $fieldLabelName = 'Opportunities';

    /**
     * Testing creation a field in Module Builder. Also test MBModule::getField
     * 
     * @group 46152
     */
    public function testMBAddField()
    {
        $this->createField();

        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $field = $module->getField(self::$fieldName);

        $this->assertNotNull($field);

        $fieldId = $module->getField($field->id_name);

        $this->assertNotNull($fieldId);

        $this->assertNotEmpty($field->vname);
        $this->assertNotEmpty($fieldId->vname);

        $this->assertArrayHasKey($field->vname, $modStrings);
        $this->assertArrayHasKey($fieldId->vname, $modStrings);
        $this->assertEquals($this->relatedModule, $field->ext2);

        return $field->id_name;
    }

    /**
     * Testing deleting a field in Module Builder. Also test MBModule::getField
     * 
     * @group 46152
     * @depends testMBAddField
     */
    public function testMBDeleteField($idFieldName)
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $field = $module->getField(self::$fieldName);
        $fieldId = $module->getField($field->id_name);

        $field->delete($module);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $this->assertNull($module->getField(self::$fieldName));
        $this->assertNull($module->getField($idFieldName));
        $this->assertArrayNotHasKey($fieldId->vname, $modStrings);

    }

    private function createField()
    {
        $this->fieldLabelName = 'LBL_' . strtoupper(self::$fieldName);
        $this->field = get_widget('relate');
        $this->field->audited = 0;
        $this->field->view = 'edit';
        $this->field->name = self::$fieldName;
        $this->field->vname = $this->fieldLabelName;
        $this->field->label = $this->fieldLabelName;
        $this->field->ext2 = $this->relatedModule;
        $this->field->label_value = self::$fieldName;


        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $this->field->save($module);
        $module->mbvardefs->save();
        $module->setLabel($GLOBALS['current_language'], $this->fieldLabelName, self::$fieldName);
        $module->save();

    }

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        SugarTestHelper::setUp('current_user');
        self::createPackage();
        self::createModule();

        parent::setUpBeforeClass();
    }

    private static function createPackage()
    {
        self::$moduleBuilder = new ModuleBuilder();
        $package = self::$moduleBuilder->getPackage(self::$packageName);
        $_REQUEST['key'] = self::$packageName;
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';

        $package->populateFromPost();
        $package->loadModules();
        self::$moduleBuilder->save();
    }

    public static function createModule()
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $_REQUEST ['team_security'] = 1;
        $_REQUEST ['has_tab'] = 1;
        $_REQUEST ['type'] = 'company';
        $_REQUEST ['label'] = self::$moduleName;
        $module->populateFromPost();
        self::$moduleBuilder->save();
    }

    public static function tearDownAfterClass()
    {
        self::$moduleBuilder->getPackage(self::$packageName)->delete ();

        $_REQUEST = array();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

}
