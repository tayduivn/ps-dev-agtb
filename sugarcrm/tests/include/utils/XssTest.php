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

class XssTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function xssData()
    {
        return array(
            array("some data", "some data"),

            array("test <a href=\"http://www.digitalbrandexpressions.com\">link</a>", "test <a href=\"http://www.digitalbrandexpressions.com\">link</a>"),
            array("some data<script>alert('xss!')</script>", "some data<>alert('xss!')</>"),
            array("some data<script src=\" http://localhost/xss.js\"></script>", "some data< src=\" http://localhost/xss.js\"></>"),
            array("some data<applet></applet><script src=\" http://localhost/xss.js\"></script>", "some data<></>< src=\" http://localhost/xss.js\"></>"),
            array('some data before<img alt="<script>" src="http://www.symbolset.org/images/peace-sign-2.jpg"; onload="alert(35)" width="1" height="1"/>some data after', 'some data before<img alt="<>" src="http://www.symbolset.org/images/peace-sign-2.jpg"; ="alert(35)" width="1" height="1"/>some data after'),
            array('some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; onload="alert(35)" width="1" height="1"/>some data after', 'some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; ="alert(35)" width="1" height="1"/>some data after'),
            array('some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; width="1" height="1"/>some data after', 'some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; width="1" height="1"/>some data after'),
            array('<div style="font-family:Calibri;">Roger Smith</div>', '<div style="font-family:Calibri;">Roger Smith</div>'),
            array('some data before<img onmouseover onload onmouseover=\'alert(8)\' src="http://www.docspopuli.org/images/Symbol.jpg";\'/>some data after', 'some data before<img   =\'alert(8)\' src="http://www.docspopuli.org/images/Symbol.jpg";\'/>some data after'),
                        
            );
    }

    protected function clean($str) {
        $potentials = clean_xss($str, false);
        if(is_array($potentials) && !empty($potentials)) {
             foreach($potentials as $bad) {
                 $str = str_replace($bad, "", $str);
             }
        }
        return $str;
    }

    /**
     * @dataProvider xssData
     */
    public function testXssFilter($before, $after)
    {
        $this->assertEquals($after, $this->clean($before));
    }

    /**
     * @dataProvider xssData
     */
    public function testXssFilterBean($before, $after)
    {
        $bean = new EmailTemplate();
		$bean->body_html = to_html($before);
        $bean->cleanBean();
        $this->assertEquals(to_html($after), $bean->body_html);
    }
}
