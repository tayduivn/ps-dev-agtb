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

require_once('modules/DynamicFields/templates/Fields/TemplateInt.php');

/**
 * Bug #56694
 * Integer fields preset to max of 100 after upgrade
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56694
 */
class Bug56694v2Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

    /**
     * @var javascript56694
     */
    protected $javascript = null;

    /**
     * @var SugarBean
     */
    protected $bean = null;

    public function setUp()
    {
        $this->templateInt = new TemplateInt();
        $this->templateInt->importable = "true";
        $this->templateInt->label = "LBL_TEST";
        $this->templateInt->name = "bug_c";
        $this->templateInt->no_default = 1;
        $this->templateInt->reportable = "1";
        $this->templateInt->supports_unified_search = true;
        $this->templateInt->vname = $this->templateInt->label;

        $this->bean = new SugarBean();

        $this->javascript = new javascript56694();
        $this->javascript->setSugarBean($this->bean);
    }

    /**
     * Test asserts that after addField call validator is not added
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutValidator()
    {
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertEmpty($this->javascript->getData(), 'Validator is added');
    }

    /**
     * Test asserts that after addField call validator is added with empty values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutRealValidator()
    {
        $this->templateInt->min = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->bean->field_name_map[$this->templateInt->name]['validation']['min'] = null;
        $this->bean->field_name_map[$this->templateInt->name]['validation']['max'] = null;
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array(false, false), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only for min value, max value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMinOnly()
    {
        $this->templateInt->min = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array($this->templateInt->min,false), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only to max value, min value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxOnly()
    {
        $this->templateInt->max = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array(false, $this->templateInt->max), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added with both values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxMin()
    {
        $this->templateInt->min = 5;
        $this->templateInt->max = 6;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertEquals(array($this->templateInt->min, $this->templateInt->max), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator added to both values and has min value, because of min value more than max
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithInvertedMaxMin()
    {
        $this->templateInt->min = 6;
        $this->templateInt->max = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertSame(array($this->templateInt->min, $this->templateInt->min), $actual, 'Min value is incorrect');
    }
}

/**
 * Mock of javascript class
 */
class javascript56694 extends javascript
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function addFieldRange($field, $type, $displayName, $required, $prefix = '', $min, $max)
    {
        $this->data = array($min, $max);
    }
}
