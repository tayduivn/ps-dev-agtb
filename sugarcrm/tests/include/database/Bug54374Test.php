<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/database/DBManagerFactory.php';

/**
 * Bug54374Test.php
 * This is a test for the massageValue function.  There was a problem with the IBMDB2Manager implementation in that some
 * code we had assumed that this function would return a value, but the IBMDB2Manager implementation had a few mistakes
 * in that the code was never written correctly and there was no guarantee that a value would be returned form massageValue.
 *
 */

class Bug54374Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {

    }

    /**
     * This is the provider function it returns an array of arrays.  The keys to the nested array correspond to a value,
     * a vardef entry and an expected value
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                'hello',
                array(
                    'name' => 'name',
                    'type' => 'name',
                    'dbType' => 'varchar',
                    'vname' => 'LBL_NAME',
                    'len' => 150,
                    'comment' => 'Name of the Company',
                    'unified_search' => true,
                    'full_text_search' => array('boost' => 3),
                    'audited' => true,
                    'required'=>true,
                    'importable' => 'required',
                    'merge_filter' => 'selected'
                ),
                "'hello'"
            )
        );
    }

    /**
     * @dataProvider provider
     *
     * @param $val Value of data
     * @param $fieldDef Field definition array
     */
    public function testMessageValue($val, $fieldDef, $expected)
    {
        $val = $this->_db->massageValue($val, $fieldDef);
        $this->assertEquals($expected, $val, "Assert that {$expected} is equal to {$val} after massageValue");
    }
}