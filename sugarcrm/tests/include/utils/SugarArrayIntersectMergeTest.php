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
 
require_once 'include/utils.php';

class SugarArrayIntersectMergeTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSubArrayOrderIsPreserved()
    {
        $array1 = array(
            'dog' => array(
                'dog1' => 'dog1',
                'dog2' => 'dog2',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
                )
            );

        $array2 = array(
            'dog' => array(
                'dog2' => 'dog2',
                'dog1' => 'dog1',
                'dog3' => 'dog3',
                'dog4' => 'dog4',
                )
            );

        $results = sugarArrayIntersectMerge($array1,$array2);

        $keys1 = array_keys($results['dog']);
        $keys2 = array_keys($array1['dog']);

        for ( $i = 0; $i < 4; $i++ ) {
            $this->assertEquals($keys1[$i],$keys2[$i]);
        }
    }

    public function testIntersectMerge()
    {
        $foo = array(
            'one' => 123,
            'two' => 123,
            'foo' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
            'bar' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
        );
        $bar = array(
            'one' => 123,
            'two' => 321,
            'three' => 321,
            'foo' => array(
                'int' => 321,
                'bar' => 'foo',
            ),
        );
        
        $expected = array(
            'one' => 123, 
            'two' => 321,
            'foo' => array(
                'int' => 321,
                'foo' => 'bar',
            ),
            'bar' => array(
                'int' => 123,
                'foo' => 'bar',
            ),
        );
        $this->assertEquals(sugarArrayIntersectMerge($foo, $bar), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($foo, $bar), $expected);
        $this->assertNotEquals(array_merge_recursive($foo, $bar), $expected);
    }

    public function testDaysOfTheWeek()
    {
        $foo = array(
            'days_of_the_week' => array('mon','tues','weds','thurs','fri','sat','sun'),
        );
        $bar = array(
            'days_of_the_week' => array('1','2','3','4','5','6','7'),
        );
        
        $expected = array(
            'days_of_the_week' => array('mon','tues','weds','thurs','fri','sat','sun'),
        );
        $this->assertEquals(sugarArrayIntersectMerge($foo, $bar), $expected);
        // insure that internal functions can't duplicate behavior
        $this->assertNotEquals(array_merge($foo, $bar), $expected);
        $this->assertNotEquals(array_merge_recursive($foo, $bar), $expected);
    }    
}
