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
    var $email_xss;

    public function setUp()
    {
        global $sugar_config;
        if(isset($sugar_config['email_xss']))
        {
            $this->email_xss = $sugar_config['email_xss'];
            $sugar_config['email_xss'] = '';
        }
    }

    public function tearDown()
    {
        if(!empty($this->email_xss))
        {
            global $sugar_config;
            $sugar_config['email_xss'] = $this->email_xss;
        }
    }

    public function xssData()
    {
        return array(
            // before, after
            array("some data", "some data"),
            // a href
            array("test <a href=\"http://www.digitalbrandexpressions.com\">link</a>", "test <a href=\"http://www.digitalbrandexpressions.com\">link</a>"),
            // xss
            array("some data<script>alert('xss!')</script>", "some data"),
            // script with src
            array("some data<script src=\" http://localhost/xss.js\"></script> and more", "some data and more"),
            // applet & script
            array("some data<applet> and </applet>more <script src=\" http://localhost/xss.js\"></script>data", "some data and more data"),
            // onload
            array('some data before<img alt="<script>" src="http://www.symbolset.org/images/peace-sign-2.jpg"; onload="alert(35)" width="1" height="1"/>some data after',
            'some data before<img alt="&lt;script&gt;" src="http://www.symbolset.org/images/peace-sign-2.jpg" width="1" height="1" />some data after'),
           // JS
            array('some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; onload="alert(35)" width="1" height="1"/>some data after',
            'some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg" width="1" height="1" alt="peace-sign-2.jpg" />some data after'),

            array('some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg"; width="1" height="1"/>some data after',
            'some data before<img src="http://www.symbolset.org/images/peace-sign-2.jpg" width="1" height="1" alt="peace-sign-2.jpg" />some data after'),

            array('<div style="font-family:Calibri;">Roger Smith</div>', '<div style="font-family:Calibri;">Roger Smith</div>'),
            array('some data before<img onmouseover onload onmouseover=\'alert(8)\' src="http://www.docspopuli.org/images/Symbol.jpg";\'/>some data after',
            'some data before<img src="http://www.docspopuli.org/images/Symbol.jpg" alt="Symbol.jpg" />some data after'),
            // xmp
            array('<xmp>some data</xmp>', '<pre>some data</pre>'),
            // youtube video
            array('<object width="425" height="350"><param name="movie" value="http://www.youtube.com/watch?v=dQw4w9WgXcQ" /><param name="wmode" value="transparent" /><embed src="http://www.youtube.com/v/AyPzM5WK8ys" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350" /></object>',
                '<object width="425" height="350" data="http://www.youtube.com/watch?v=dQw4w9WgXcQ" type="application/x-shockwave-flash"><param name="allowScriptAccess" value="never" /><param name="allowNetworking" value="internal" /><param name="movie" value="http://www.youtube.com/watch?v=dQw4w9WgXcQ" /><param name="wmode" value="transparent" /><embed src="http://www.youtube.com/v/AyPzM5WK8ys" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350" allowscriptaccess="never" allownetworking="internal" /></object>'),
            // another youtube video
            array('<iframe width="420" height="315" src="http://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen>My Frame</iframe>',
            '<iframe width="420" height="315" src="http://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0">My Frame</iframe>'),
            // stuff inside iframe
            array('<iframe width="420" height="315" src="http://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen>My <script>alert(\'xss!\')</script>Frame</iframe>',
            '<iframe width="420" height="315" src="http://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0">My Frame</iframe>'),
            // body/html/head
            array("<body><head><title>My Page</title></head><html>My Content</html></body>", "My Content"),
            // link
            array('<link rel="stylesheet" type="text/css" href="styles/plain.css" />',
            '<link rel="stylesheet" type="text/css" href="styles/plain.css" />'
            ),
            // international
            array('в чащах юга жил-был <img src="http://images.com/fikus.jpg" alt="фикус"> - דג סקרן שט בים מאוכזב ולפתע מצא חברה',
            'в чащах юга жил-был <img src="http://images.com/fikus.jpg" alt="фикус" /> - דג סקרן שט בים מאוכזב ולפתע מצא חברה')
            );
    }

    protected function clean($str)
    {
        return SugarCleaner::cleanHtml($str, false);
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
