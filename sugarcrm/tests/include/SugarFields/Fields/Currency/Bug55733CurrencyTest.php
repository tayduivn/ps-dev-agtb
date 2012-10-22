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


require_once('include/SugarFields/Fields/Currency/SugarFieldCurrency.php');
/*
 * This tests for precision formatting from the sugarfieldcurrency object.  Prior to bug 55733, the value would get picked up from
 * the vardefs['precision'] value, instead of the currency settings.
 */

class Bug55733CurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $value1 = '20000.0000';
    private $value2 = '20000';
    private $expectedValue = '20,000.00';
    private $vardef = array('precision' => '6');
    private $sfr;

    public function setUp()
    {
        global $locale;
        //if locale is not defined, create new global locale object.
        if(empty($locale))
        {
            require_once('include/Localization/Localization.php');
            $locale = new Localization();
        }

        //create a new SugarFieldCurrency object
        $this->sfr = new SugarFieldCurrency('currency');

    }
    
    public function testFormatPrecision()
    {
        //lets test some values with different decimals to make sure the formatting is returned correctly
        $testVal1 = $this->sfr->formatField($this->value1, $this->vardef);
        $testVal2 = $this->sfr->formatField($this->value2, $this->vardef);
        $this->assertSame($this->expectedValue, $testVal1,' The currency precision was not formatted correctly.');
        $this->assertSame($this->expectedValue, $testVal2,' The currency precision was not formatted correctly.');
    }
}