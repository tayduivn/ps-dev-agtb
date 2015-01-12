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

require_once 'include/workflow/glue.php';

class GlueTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Make sure that write_escape() properly escapes the value, and
     * calls stripslashes() on it
     *
     * @dataProvider dataProviderWriteEscape
     * @param $value - To be escaped for use in PHP
     * @param $expected - Value after using it as PHP code
     */
    public function testWriteEscape($value, $expected)
    {
        $wfg = new WorkFlowGlue();
        $actual = $wfg->write_escape($value);

        eval("\$actual = $actual;");

        $this->assertEquals($expected, $actual, "write_escape() didn't return properly escaped value for use in PHP");
    }

    public static function dataProviderWriteEscape()
    {
        return array(
            array(
                'A strange string "that is $being" &#64;, &amp; compared',
                'A strange string "that is $being" @, & compared',
            ),
            array(
                "A strange string 'that is &#36;being' escaped, &#38; compared",
                "A strange string 'that is \$being' escaped, & compared",
            ),
        );
    }
}
