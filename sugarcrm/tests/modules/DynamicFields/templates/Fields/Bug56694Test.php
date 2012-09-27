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
class Bug56694Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

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
    }

    /**
     * Test asserts that min & max properties more important than ext1 & ext2
     *
     * @group 53554
     */
    public function testMinMaxWinExt()
    {
        $this->templateInt->ext1 = 3;
        $this->templateInt->ext2 = 4;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(3, $vardef['validation']['min'], 'Ext won');
        $this->assertEquals(4, $vardef['validation']['max'], 'Ext won');

        $this->templateInt->min = 1;
        $this->templateInt->max = 2;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(1, $vardef['validation']['min'], 'Min won');
        $this->assertEquals(2, $vardef['validation']['max'], 'Max won');
    }

    /**
     * Method returns data for tests
     * min value
     * max value
     * should validator be present
     * should min value be present
     * should max value be present
     *
     * @return array
     */
    public function getMaxMin()
    {
        return array(
            array(null, null, false, false, false),
            array(0, null, true, true, false),
            array(0, 0, true, true, true),
            array('0', '0', true, true, true),
            array(null, 0, true, false, true),
            array(1, 5, true, true, true, true),
            array('1', '5', true, true, true, true),
            array('a', 'b', false, false, false),
            array('a', 5, true, false, true),
            array(5, 'a', true, true, false)
        );
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByMinMax($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->min = $min;
        $this->templateInt->max = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false)
        {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        }
        else
        {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true)
            {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true)
            {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByExt($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->ext1 = $min;
        $this->templateInt->ext2 = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false)
        {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        }
        else
        {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true)
            {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true)
            {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }
}
