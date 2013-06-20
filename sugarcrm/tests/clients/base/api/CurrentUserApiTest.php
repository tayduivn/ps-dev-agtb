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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/api/SugarApi.php';
require_once 'clients/base/api/CurrentUserApi.php';
require_once "tests/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * @group ApiTests
 */
class CurrentUserApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $currentUserApiMock;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        OutboundEmailConfigurationTestHelper::setUp();
        // load up the unifiedSearchApi for good times ahead
        $this->currentUserApiMock = new CurrentUserApiMock();
    }

    public function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testCurrentUserLanguage()
    {
        // test from session
        $_SESSION['authenticated_user_language'] = 'en_UK';
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals('en_UK', $result['preferences']['language']);
        // test from user
        unset($_SESSION['authenticated_user_language']);
        $GLOBALS['current_user']->preferred_language = 'AWESOME';
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals('AWESOME', $result['preferences']['language']);
        // test from default
        unset($_SESSION['authenticated_user_language']);
        unset($GLOBALS['current_user']->preferred_language);
        $result = $this->currentUserApiMock->getBasicInfo();
        $this->assertEquals($GLOBALS['sugar_config']['default_language'], $result['preferences']['language']);
    }
}

class CurrentUserApiMock extends CurrentUserApi
{
    public function getBasicInfo()
    {
        return parent::getBasicUserInfo();
    }
}
