<?php
//FILE SUGARCRM flav=pro ONLY
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
            array(
                array('name'=>array(0=>'this is a <span class="highlight">sugarcrm</span> test for <span class="highlight">sugarcrm</span>')),
                array('name'=>'this is a <span class="highlight">sugarcrm</span> test for <span class="highlight">sugarcrm</span>'),
                80, 1, '<span class="highlight">', '</span>'
            ),
            // string too long, only one hit is returned
            array(
                array('name'=>array(0=>'this is a <span class="highlight">sugarcrm<</span> test for <span class="highlight">sugarcrm</span> abc defgh xyz <span class="highlight">sugarcrm</span>sugarcrm</span> and more more more more')),
                array('name'=>'this is a <span class="highlight">sugarcrm<</span> test for '),
                80, 1, '<span class="highlight">', '</span>'
            ),
            // string too long, only 2 hit are returned
            array(
                array('name'=>array(0=>'this is a <span class="highlight">sugarcrm</span> test for <span class="highlight">sugarcrm</span> abc defgh xyz <span class="highlight">sugarcrm</span>sugarcrm</span> and more more more more')),
                array('name'=>'this is a <span class="highlight">sugarcrm</span> test for <span class="highlight">sugarcrm</span> abc defgh xyz '),
                80, 2, '<span class="highlight">', '</span>'
            ),
            // string too long, string is modified with ...
            array(
                array('name'=>array(0=>'this is a <span class="highlight">sugarcrm</span> test for abc defgh xyz and more more 1234567890 1234567890 1234567890')),
                array('name'=>'this is a <span class="highlight">sugarcrm</span> test for abc ... 1234567890'),
                80, 1, '<span class="highlight">', '</span>'),
            // unicode string too long, string is modified with ...
            array(
                array('name'=>array(0=>'我知道我知道我知道我知道我知道我知道 我知道我知道我知道我知道我知道我知道我知道我知道我知道<span class="highlight">sugarcrm</span> 我知道我知道我知道我知道我知道我知道我知道我知道我知道我知道我知道我知道')),
                array('name'=>'我知道我知道我知道我知道我知道我知道 ... 道我知道我知道我知道我知道我知道<span class="highlight">sugarcrm</span> 我知道我知道我知道我知道我知道 ... 道我知道我知道我知道我知道我知道'),
                80, 1, '<span class="highlight">', '</span>'),
            // string too long, string is modified with ...
            array(
                array('name'=>array(0=>'this is a <span class="highlight">sugarcrm</span> test for abc defgh xyz and more more 123456789012345678901234567890')),
                array('name'=>'this is a <span class="highlight">sugarcrm</span> test for abc ... 5678901234567890'),
                80, 1, '<span class="highlight">', '</span>'),
        );
    }

    /**
     * @dataProvider highlighterProvider
     */
    public function testHighlighter($resultArray, $expectedArray, $maxLen, $maxHits, $preTag, $postTag)
    {
        $this->markTestSkipped("Rewriting highlighting");
        $highlighter = new SugarSearchEngineHighlighter($maxLen, $maxHits, $preTag, $postTag);

        $ret = $highlighter->processHighlightText($resultArray);

        $diff = array_diff($ret, $expectedArray); // they should be the same

        $this->assertEmpty($diff, 'arrays not the same');
    }

}
