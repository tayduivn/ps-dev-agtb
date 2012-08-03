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

require_once('modules/InboundEmail/InboundEmail.php');

/**
 * @ticket 43554
 */
class Bug43554Test extends Sugar_PHPUnit_Framework_TestCase
{

	static $ie = null;
    static $_user = null;

	static public function setUpBeforeClass()
    {
        self::$_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = self::$_user;

		self::$ie = new InboundEmail();
	}

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function getUrls()
    {
        return array(
            array("http://localhost:8888/sugarent/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array(to_html("http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1")),
            array("/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("https://localhost/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            );
    }

    /**
     * @dataProvider getUrls
     * @param string $url
     */
	function testEmailCleanup($url)
	{
        $data = "Test: <img src=\"$url\">";
        $res = str_replace("<img />", "", SugarCleaner::cleanHtml($data));
        $this->assertNotContains("<img", $res);
	}
}