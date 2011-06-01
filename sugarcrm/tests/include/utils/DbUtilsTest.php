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
 
require_once 'include/utils/db_utils.php';

/**
 * @todo refactor this test to not use test-level fixtures.  Will require
 *       refactoring from_html() so it doesn't create static caches within
 *       itself
 */
class DbUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_old_toHTML = null;
    private $_random = null;
    
    public function setUp() 
    {
        $this->_random = rand(100, 200);
        $GLOBALS['from_html_cache_clear'] = true;
        $this->_old_toHTML = $GLOBALS['toHTML'];
        $GLOBALS['toHTML'] = array(
            'foobar' => 'barfoo',
            $this->_random => 'random',
        );
    }

    public function tearDown() 
    {
        $GLOBALS['toHTML'] = $this->_old_toHTML;
    }

    public function testReturnsSameValueOnNoneStrings() 
    {
        $random = rand(100, 200);
        $this->assertEquals(from_html($random), $random);
    }

    public function testSwapsValuesForKeysFromToHTMLGlobal() 
    {
        $GLOBALS['toHTML']['foobar'] = 'barfoo';
        $this->assertEquals(from_html('barfoo'), 'foobar');
    }

    public function testSwapsValuesForKeysFromToHTMLGlobalWithRandomData() 
    {
        $this->assertEquals(from_html('random'), $this->_random);
    }

    public function testWillReturnTheSameValueTwiceInARow() 
    {
        unset($GLOBALS['from_html_clear_cache']);
        $GLOBALS['toHTML']['foobar'] = 'barfoo';
        $this->assertEquals(from_html('barfoo'), 'foobar');
        $this->assertEquals(from_html('barfoo'), 'foobar');
    }

    public function testWillReturnRawValueIfEncodeParameterIsFalse() 
    {
        $GLOBALS['toHTML']['foobar'] = 'barfoo';
        $this->assertEquals(from_html('barfoo', false), 'barfoo');
    }
}

