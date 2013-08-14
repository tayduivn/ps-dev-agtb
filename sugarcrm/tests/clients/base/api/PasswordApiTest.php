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

require_once 'clients/base/api/PasswordApi.php';
require_once 'tests/SugarTestRestUtilities.php';

/**
 * @group ApiTests
 */
class PasswordApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $accounts;
    public $roles;
    public $unifiedSearchApi;
    public $moduleApi;
    public $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $this->passwordApi = new PasswordApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->args = array(
            'email' => 'test@test.com',
            'username' => 'test'
        );
        $this->passwordApi->usr = $this->getMock('User');

        $this->passwordApi->usr->expects($this->any())->method('retrieve_user_id')->will($this->returnValue('test_id'));
        $this->passwordApi->usr->expects($this->any())->method('retrieve')->will($this->returnValue(true));

        $this->passwordApi->usr->db = $this->getMock('db');
        $this->passwordApi->usr->db->expects($this->any())->method('query')->will($this->returnValue(true));
        $this->passwordApi->usr->emailAddress = $this->getMock('emailAddress');
        $this->passwordApi->usr->emailAddress->expects($this->any())->method('getPrimaryAddress')->will($this->returnValue($this->args['email']));

        $this->passwordApi->usr->portal_only = false;
        $this->passwordApi->usr->is_group = false;
        $this->passwordApi->usr->email1 = $this->args['email'];

        $this->passwordApi->usr->username = $this->args['username'];



    }

    public function tearDown()
    {
        unset($this->passwordApi);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    // test that when read only is set for every field you can still retrieve
    public function testRequestPasswordCorrect()
    {
        $this->passwordApi->usr->expects($this->any())->method('sendEmailForPassword')->will(
            $this->returnValue(
                array(
                    'status' => true,
                )
            )
        );

        $this->args['email'] = 'test@test.com';
        $result = $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        $this->assertEquals($result, 1);
    }

    // test that when read only is set for every field you can still retrieve
    public function testException()
    {
        $this->passwordApi->usr->expects($this->any())->method('sendEmailForPassword')->will(
            $this->returnValue(
                array(
                    'status' => true,
                )
            )
        );

        $this->args['email'] = 'asdf';
        try {
            $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        } catch (SugarApiExceptionRequestMethodFailure $expected) {
            return;
        }

        $this->fail('An expected exception has not been raised.');

    }
    public function testMissingParamException()
    {
        unset($this->args['email']);
        try {
            $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        } catch (SugarApiExceptionMissingParameter $expected) {
            return;
        }

        $this->fail('An expected exception has not been raised.');

    }
    public function testEmptyParam()
    {
        $this->args['email'] = '';
        try {
            $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        } catch (SugarApiExceptionMissingParameter $expected) {
            return;
        }

        $this->fail('An expected exception has not been raised.');

    }
    public function testBadEmailException()
    {
        $this->passwordApi->usr->expects($this->any())->method('sendEmailForPassword')->will(
            $this->returnValue(
                array(
                    'status' => false,
                    'message' => 'fail'
                )
            )
        );
        try {
            $this->passwordApi->requestPassword($this->serviceMock, $this->args);
        } catch (SugarApiExceptionRequestMethodFailure $expected) {
            return;
        }

        $this->fail('An expected exception has not been raised.');

    }
}
