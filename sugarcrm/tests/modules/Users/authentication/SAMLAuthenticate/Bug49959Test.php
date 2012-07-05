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

require_once 'modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticateUser.php';
require_once 'modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml/settings.php';
require_once 'modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml/response.php';

/**
 * @ticket 49959
 */
class Bug49959Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SAMLAuthenticateUserTest
     */
    protected static $auth;

    /**
     * @var User
     */
    protected static $user;
    protected static $user_id,
        $user_name  = 'Bug49959TestUser',
        $user_email = 'bug49959.test.user@example.com';

    /**
     * This method is called before the first test of this test class is run.
     *
     * Creates shared test resources
     */
    public static function setUpBeforeClass()
    {
        self::$auth = new SAMLAuthenticateUserTest;
        $user = self::$user = new User();

        $user->user_name = self::$user_name;
        $user->email1    = self::$user_email;

        self::$user_id = $user->save();
    }


    /**
     * This method is called after the last test of this test class is run.
     *
     * Removes shared test resources
     */
    public static function tearDownAfterClass()
    {
        self::$user->mark_deleted(self::$user_id);
    }

    /**
     * Test fetching of a user to be authenticated by different fields
     */
    public function testFetchUser()
    {
        // test fetching user by ID
        $user = self::$auth->fetch_user(self::$user_id, 'id');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by username
        $user = self::$auth->fetch_user(self::$user_name, 'user_name');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by email (default case)
        $user = self::$auth->fetch_user(self::$user_email);
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by unsupported field
        $user = self::$auth->fetch_user(self::$user_email, 'unsupported_field');
        $this->assertNull($user->id);

        // test fetching non-existing user
        $user = self::$auth->fetch_user('some_wrong_key');
        $this->assertNull($user->id);
    }

    /**
     * Test that get_nameid() method of SamlResponse is called by default
     */
    public function testDefaultNameId()
    {
        // create a mock of SAML response
        $mock = $this->getResponse();
        $mock->expects($this->once())
            ->method('get_nameid');

        // create a default SAML settings object
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));

        // expect that get_nameid() method of response is used by default
        self::$auth->get_user_id($mock, $settings);
    }

    /**
     * Test that custom XPath is used when specified in settings
     */
    public function testCustomNameId()
    {
        $node_id = 'Bug49959Test';

        // create response with custom XML
        $mock2 = $this->getResponse();
        $mock2->xml = $this->getResponseXml($node_id);

        // create SAML settings object with custom name id definition
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));
        $settings->saml_settings['check']['user_name'] = '//root';

        // expect that user ID is fetched from the document according to settings
        $result = self::$auth->get_user_id($mock2, $settings);
        $this->assertEquals($node_id, $result);
    }

    /**
     * Returns a mock of SamlResponse object
     *
     * @return SamlResponse
     */
    protected function getResponse()
    {
        return $this->getMock('SamlResponse', array(), array(), '', false);
    }

    /**
     * Returns custom response XML document
     *
     * @param $node_id
     * @return DOMDocument
     */
    protected function getResponseXml($node_id)
    {
        $document = new DOMDocument();
        $document->loadXML('<root>' . $node_id . '</root>');
        $root = $document->createElement('root');
        $document->appendChild($root);
        return $document;
    }
}

/**
 * A SAMLAuthenticate class wrapper that makes some of its methods accessible
 */
class SAMLAuthenticateUserTest extends SAMLAuthenticateUser
{
    public function fetch_user($id, $field = null)
    {
        return parent::fetch_user($id, $field);
    }

    public function get_user_id($samlresponse, $settings)
    {
        return parent::get_user_id($samlresponse, $settings);
    }
}
