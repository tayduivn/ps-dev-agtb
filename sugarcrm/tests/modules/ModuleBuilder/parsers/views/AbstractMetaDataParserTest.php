<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once 'modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php';

/**
 * Wrapper class to test protected functions of AbstractMetaDataParser
 */
class TestMetaDataParser extends AbstractMetaDataParser
{
    //Trim Field defs implementation is required to extend AbstractMetaDataParser
    static function _trimFieldDefs ( $def ) {}
    
    //Wrapper of isTrue for testing purposes
    public static function testIsTrue($val)
    {
        return self::isTrue($val);
    }
}

class AbstractMetaDataParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test the the isTrue function works correctly for boolean and non-boolean values
     * @group Studio
     */
    public function testIsTrue()
    {
        $testValues = array(
            true => true,
            false => false,
            0 => false,
            "" => false,
            "true" => true,
            "false" => false,
            "FALSE" => false,
            "0" => false,
            "something" => true,
        );
        
        foreach($testValues as $testVal => $boolVal){
            $this->assertEquals($boolVal, TestMetaDataParser::testIsTrue($testVal));
        }
    }
    
    /**
     * Tests validation of studio defs for client and view specific rules
     * 
     * @dataProvider studioValidationDefProvider
     * @param array $def Array of fields defs
     * @param string $view The view name to check defs for
     * @param string $client The client to check defs for
     * @param bool $expected The expected result of the validation call
     */
    public function testGetClientStudioValidation($def, $view, $client, $expected)
    {
        $actual = AbstractMetaDataParser::getClientStudioValidation($def, $view, $client);
        $this->assertEquals($expected, $actual);
    }
    
    public function studioValidationDefProvider()
    {
        return array(
            // Test no client specific rule in the defs is null
            array(
                'def' => array(),
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ),
            // Test no client passed is null
            array(
                'def' => array('base' => array()),
                'view' => 'list',
                'client' => '',
                'expected' => null,
            ),
            // Test def[client] is a string is null
            array(
                'def' => array('base' => 'list'),
                'view' => 'list',
                'client' => 'base',
                'expected' => null,
            ),
            // Test no view passed is null
            array(
                'def' => array('mobile' => array()),
                'view' => '',
                'client' => 'mobile',
                'expected' => null,
            ),
            // Test def[client] is boolean returns the boolean
            array(
                'def' => array('mobile' => true),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => true,
            ),
            array(
                'def' => array('mobile' => false),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ),
            // Test client and view specific rules are boolean
            array(
                'def' => array('mobile' => array('list' => false)),
                'view' => 'list',
                'client' => 'mobile',
                'expected' => false,
            ),
            array(
                'def' => array('custom' => array('record' => 'somestring')),
                'view' => 'record',
                'client' => 'custom',
                'expected' => true,
            ),
        );
    }
}