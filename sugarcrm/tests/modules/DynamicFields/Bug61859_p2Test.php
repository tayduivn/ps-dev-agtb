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

require_once('modules/DynamicFields/DynamicField.php');

class Bug61859_p2Test extends Sugar_PHPUnit_Framework_TestCase
{

    static private $module = 'Leads';
    private $object = 'Lead';
    private $relatedModule = 'Contacts';

    static private $field;
    static private $dynamicField;

    /**
     * @group 61859
     */
    public function testAddField()
    {
        $this->addField('testfieldbug61859');
        SugarTestHelper::setUp('dictionary');

        $idName = $GLOBALS['dictionary'][$this->object]['fields'][self::$field->name]['id_name'];

        $this->assertArrayHasKey(self::$field->name, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayHasKey($idName, $GLOBALS['dictionary'][$this->object]['fields']);

        return $idName;
    }

    /**
     * @depends testAddField
     * @group 61859
     */
    public function testUpdateField($idName)
    {

        self::$field->label_value = 'UpdatedLabel';
        self::$field->save(self::$dynamicField);

        SugarTestHelper::setUp('dictionary');

        $this->assertEquals($idName, self::$field->ext3);

    }

    private function addField($name)
    {
        $labelName = 'LBL_' . strtoupper($name);
        $field = get_widget('relate');
        $field->audited = 0;
        $field->view = 'edit';
        $field->name = $name;
        $field->vname = $labelName;
        $field->label = $labelName;

        $field->ext2 = $this->relatedModule;
        $field->label_value = $name;
        $field->save(self::$dynamicField);

        self::$field = $field;
    }

    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array(self::$module));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        self::$dynamicField = new DynamicField(self::$module);
        self::$dynamicField->setup(BeanFactory::getBean(self::$module));

    }

    static public function tearDownAfterClass()
    {
        self::$field->delete(self::$dynamicField);

        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

}
