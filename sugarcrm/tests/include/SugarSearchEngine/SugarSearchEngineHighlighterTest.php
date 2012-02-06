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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once 'include/SugarSearchEngine/SugarSearchEngineHighlighter.php';

class SugarSearchEngineHighlighterTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function highlighterProvider()
    {
        return array(
            // string length ok, so no truncation
            array(array(0=>'this is a sugarcrm test for sugarcrm'),
                array(0=>'this is a <b>sugarcrm</b> test for <b>sugarcrm</b>'),
                'sugarcrm',
                80, 1, '<b>', '</b>'),
            // string length ok, so no truncation
            array(array(0=>'this is a sugarcrm test for sugarcrm', 1=>'this sugarcrm string length is ok'),
                array(0=>'this is a <b>sugarcrm</b> test for <b>sugarcrm</b>',  1=>'this <b>sugarcrm</b> string length is ok'),
                'sugarcrm',
                80, 1, '<b>', '</b>'),
            // string too long, only one hit is returned
            array(array(0=>'this is a sugarcrm test for sugarcrm abc defgh xyz sugarcrm and more more'),
                array(0=>'this is a <b>sugarcrm</b> test for '),
                'sugarcrm',
                80, 1, '<b>', '</b>'),
            // string too long, only two hits are returned
            array(array(0=>'this is a sugarcrm test for sugarcrm abc defgh xyz sugarcrm and more more'),
                array(0=>'this is a <b>sugarcrm</b> test for <b>sugarcrm</b> abc defgh xyz '),
                'sugarcrm',
                80, 2, '<b>', '</b>'),
            // string too long, string is modified with ...
            array(array(0=>'this is a sugarcrm test for abc defgh xyz and more more 1234567890 1234567890 1234567890'),
                array(0=>'this is a <b>sugarcrm</b> test for abc ... 90 1234567890'),
                'sugarcrm',
                80, 1, '<b>', '</b>'),
        );
    }

    /**
     * @dataProvider highlighterProvider
     */
    public function testHighlighter($resultArray, $expectedArray, $searchString, $maxLen, $maxHits, $preTag, $postTag)
    {
        $highlighter = new SugarSearchEngineHighlighter($maxLen, $maxHits, $preTag, $postTag);

        $ret = $highlighter->getHighlightedHitText($resultArray, $searchString);

        $diff = array_diff($ret, $expectedArray); // they should be the same

        $this->assertEmpty($diff, 'arrays not the same');
    }

}
