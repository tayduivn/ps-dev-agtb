<?php
/*********************************************************************************
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
 ********************************************************************************/

require_once('include/Localization/Localization.php');

/**
 * Bug #35413
 * Other character sets not displayed properly
 */
class Bug35413Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_localization = null;

    function setUp()
    {
        $this->_localization = new Localization();
    }

    function stringsProvider()
    {
        return array(
            array(
                '7cvU3iDI5d7L7O3TIOUg1cfU7N0g3c4g5ezR1NPd5eHU3csg287a',
                'يثشق بهقثىيس ه صاشىف فخ هىرشسفهلشفث غخع',
                'windows-1256'
            ),
            array(
                '7cjT7cjU0+3IwcbExNE=',
                'يبسيبشسيبءئؤؤر',
                'windows-1256'
            )
        );
    }

    /**
     * Test convert base64 $source to string and convert string from $encoding to utf8. It has to return $utf8string.
     *
     * @dataProvider stringsProvider
     * @ticket 35413
     * @param string $source base64 encoded string in native charset
     * @param string $utf8string previous string in utf8
     * @param string $encoding encoding of native string
     */
    public function testEncodings($source, $utf8string, $encoding)
    {
        $source = base64_decode($source);
        $translateCharsetResult = $this->_localization->translateCharset($source, $encoding, 'UTF-8');
        $this->assertEquals($utf8string, $translateCharsetResult, 'Strings have to be the same');
    }
}