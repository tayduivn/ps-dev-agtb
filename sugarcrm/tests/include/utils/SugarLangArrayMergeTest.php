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

class SugarLangArrayMergeTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSugarLangArrayMerge()
    {
        $target = array();
        $target['LBL_TO_BE_CHANGED'] = 'Good';
        $target['LBL_UNCHANGED'] = 'Blah';
        $target['LBL_UNCHANGED_TOO'] = 'foo';
        $target['LBL_DOM'] = array('LBL_ONE' => 'One', 'LBL_TWO' => 'Two', 'LBL_THREE' => 'Three');

        $source = array();
        $source['LBL_TO_BE_CHANGED'] = 'Better';
        $source['LBL_UNCHANGED_TOO'] = '';
        $source['LBL_DOM'] = array('LBL_ONE' => '', 'LBL_TWO' => 'Deux', 'LBL_FOUR' => 'Quatre');

        $merged = sugarLangArrayMerge($target, $source);
        $this->assertEquals(
            'Better',
            $merged['LBL_TO_BE_CHANGED'],
            'Source string should have overwritten target string.'
        );
        $this->assertEquals('Blah', $merged['LBL_UNCHANGED'], 'Source string should not have been changed.');
        $this->assertEquals(
            'foo',
            $merged['LBL_UNCHANGED_TOO'],
            'Source string should not have been changed to empty string.'
        );
        $this->assertEquals(
            array('LBL_ONE' => 'One', 'LBL_TWO' => 'Deux', 'LBL_THREE' => 'Three', 'LBL_FOUR' => 'Quatre'),
            $merged['LBL_DOM'],
            'Should merge subarrays too'
        );
        $this->assertEquals(count($target), count($merged), 'Merged array should be same size as target.');
    }
}
