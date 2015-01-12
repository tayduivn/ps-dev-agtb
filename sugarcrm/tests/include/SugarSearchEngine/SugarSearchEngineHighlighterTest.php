<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



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
        $this->markTestIncomplete("Rewriting highlighting");
        $highlighter = new SugarSearchEngineHighlighter($maxLen, $maxHits, $preTag, $postTag);

        $ret = $highlighter->processHighlightText($resultArray);

        $diff = array_diff($ret, $expectedArray); // they should be the same

        $this->assertEmpty($diff, 'arrays not the same');
    }

}
