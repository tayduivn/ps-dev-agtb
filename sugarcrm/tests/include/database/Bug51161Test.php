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


require_once 'include/database/DBManagerFactory.php';

class Bug51161Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;



	public function setUp()
    {
	    $this->_db = DBManagerFactory::getInstance();
	}

	public function tearDown()
	{

	}


	public function providerBug51161()
    {
        $returnArray = array(
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'varchar',
						'len' => '34',
						),
					),
					'/foo\s+$baseType\(34\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'nvarchar',
						'len' => '35',
						),
					),
					'/foo\s+$baseType\(35\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'char',
						'len' => '23',
						),
					),
					'/foo\s+$baseType\(23\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'text',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'clob',
						),
					),
					'/foo\s+$colType/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'clob',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'blob',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
           );

        return $returnArray;
    }

    /**
     * @dataProvider providerBug51161
     */

    public function testBug51161($fieldDef,$successRegex, $times)
    {
        // Allowing type part variables in passed in regular expression so that database specific mappings
        // can be accounted for in the test
        $ftype = $this->_db->getFieldType($fieldDef['foo']);
        $colType = $this->_db->getColumnType($ftype);
        $successRegex = preg_replace('/\$colType/', $colType, $successRegex);
        if($type = $this->_db->getTypeParts($colType)){
            if(isset($type['baseType']))
                $successRegex = preg_replace('/\$baseType/', $type['baseType'], $successRegex);
            if(isset($type['len']))
                $successRegex = preg_replace('/\$len/', $type['len'], $successRegex);
            if(isset($type['scale']))
                $successRegex = preg_replace('/\$scale/', $type['scale'], $successRegex);
            if(isset($type['arg']))
                $successRegex = preg_replace('/\$arg/', $type['arg'], $successRegex);
        }
        $result = $this->_db->createTableSQLParams('test', $fieldDef, array());
        $this->assertEquals($times, preg_match($successRegex, $result), "Resulting statement: $result failed to match /$successRegex/");
    }
}
