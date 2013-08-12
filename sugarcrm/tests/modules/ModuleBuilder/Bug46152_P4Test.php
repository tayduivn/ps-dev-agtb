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

class Bug46152_P4Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $module = 'Notes';
    private $object = 'Note';
    private $relatedModule = 'Opportunities';

    /**
     * Tested removing a field in studio.
     * 
     * @group 46152
     */
    public function testDelete()
    {
        $fieldName = 'test' . time();
        $field = $this->addField($fieldName);
        SugarTestHelper::setUp('mod_strings', array($this->module));


        $idName = $GLOBALS['dictionary'][$this->object]['fields'][$field->name]['id_name'];
        $vName = $GLOBALS['dictionary'][$this->object]['fields'][$idName]['vname'];

        $field->delete($this->dynamicField);

        SugarTestHelper::setUp('mod_strings', array($this->module));

        $this->assertArrayNotHasKey($field->name, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayNotHasKey($idName, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayNotHasKey($vName, $GLOBALS['mod_strings']);

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
        $field->save($this->dynamicField);

        return $field;

    }

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array($this->module));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField($this->module);
        $this->dynamicField->setup(BeanFactory::getBean($this->module));

    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

}
