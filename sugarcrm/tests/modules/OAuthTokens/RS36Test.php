<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * RS-36: Prepare OAuthTokens Module
 */
class RS36Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Contact */
    protected $contact = null;

    /** @var array */
    protected $beans = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        /** @var $bean SugarBean */
        foreach ($this->beans as $bean) {
            $bean->mark_deleted($bean->id);
        }
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testCreateAuthorized()
    {
        $consumer = new OAuthKey();
        $consumer->name = create_guid();
        $consumer->c_key = create_guid();
        $consumer->save();
        $actual = OAuthToken::createAuthorized($consumer, $GLOBALS['current_user']);
        $this->assertInstanceOf('OAuthToken', $actual);
    }

    public function testCleanup()
    {
        $actual = OAuthToken::cleanup();
        $this->assertEmpty($actual);
    }

    public function testCleanupOldUserTokensLimit1()
    {
        $bean = new OAuthToken();
        $bean->save();
        $actual = $bean->cleanupOldUserTokens();
        $this->assertEmpty($actual);
    }

    public function testCleanupOldUserTokensLimit2()
    {
        $bean = new OAuthToken();
        $bean->save();
        $actual = $bean->cleanupOldUserTokens(2);
        $this->assertEmpty($actual);
    }

    public function testCheckNonce()
    {
        $actual = OAuthToken::checkNonce(create_guid(), create_guid(), create_guid());
        $this->assertEquals(Zend_Oauth_Provider::OK, $actual);
    }

    public function testMarkDeleted()
    {
        $bean = new OAuthToken();
        $bean->save();
        $bean->mark_deleted($bean->id);
        $actual = $bean->retrieve($bean->id);
        $this->assertEmpty($actual);
    }

    public function testDeleteByConsumer()
    {
        $actual = OAuthToken::deleteByConsumer(create_guid());
        $this->assertEmpty($actual);
    }

    public function testDeleteByUser()
    {
        $actual = OAuthToken::deleteByUser(create_guid());
        $this->assertEmpty($actual);
    }
}
