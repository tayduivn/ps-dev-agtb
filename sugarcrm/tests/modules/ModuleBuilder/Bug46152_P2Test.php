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


require_once 'modules/DynamicFields/FieldCases.php';
require_once 'modules/DynamicFields/DynamicField.php';

class Bug46152_P2Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $fields = array();
    private $dynamicField = null;

    /**
     * Test is id fields have unique label
     *
     * Create 2 equal fields. Test is id fields have unique label. For correct import we must have unique label of id fields.
     * 
     * @group 46152
     */
    public function testDoubleLabel()
    {

        $idName1 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[0]->name]['id_name'];
        $idName2 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[1]->name]['id_name'];
        $vName1 = $GLOBALS['dictionary']['Note']['fields'][$idName1]['vname'];
        $vName2 = $GLOBALS['dictionary']['Note']['fields'][$idName2]['vname'];

        $this->assertArrayHasKey($vName1, $GLOBALS['mod_strings']);
        $this->assertArrayHasKey($vName2, $GLOBALS['mod_strings']);

        $this->assertNotEquals($GLOBALS['mod_strings'][$vName1], $GLOBALS['mod_strings'][$vName2]);
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Notes'));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField('Notes');
        $this->dynamicField->setup(BeanFactory::getBean('Notes'));

        $this->addField('testfield1_b46152');
        $this->addField('testfield2_b46152');

        SugarTestHelper::setUp('mod_strings', array('Notes'));

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

        $field->ext2 = 'Opportunities';
        $field->label_value = $name;
        $field->save($this->dynamicField);
        $this->fields[] = $field;

    }

    public function tearDown()
    {
        $this->deleteFields();

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    private function deleteFields()
    {
        foreach ($this->fields AS $field) {
            $field->delete($this->dynamicField);
        }
    }

}
