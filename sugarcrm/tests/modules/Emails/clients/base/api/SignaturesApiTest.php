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

require_once("include/api/SugarApi.php");
require_once("modules/Emails/clients/base/api/SignaturesApi.php");

/**
 * @group api
 * @group email
 * @group signatures
 */
class SignaturesApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_user,
            $api,
            $signaturesApi,
            $signature1,
            $signature2,
            $signature3;

    public function setUp()
    {
        parent::setUp();

        $this->_user             = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS["current_user"] = $this->_user;

        $this->api           = SugarTestRestUtilities::getRestServiceMock();
        $this->signaturesApi = new SignaturesApi();

        $this->signature1          = SugarTestUserUtilities::createUserSignature();
        $this->signature1->user_id = $this->_user->id;
        $this->signature1->save();

        $this->signature2          = SugarTestUserUtilities::createUserSignature();
        $this->signature2->user_id = $this->_user->id;
        $this->signature2->save();

        $secondUser                = SugarTestUserUtilities::createAnonymousUser();
        $this->signature3          = SugarTestUserUtilities::createUserSignature();
        $this->signature3->user_id = $secondUser->id;
        $this->signature3->save();

        $deletedSignature = SugarTestUserUtilities::createUserSignature();
        $deletedSignature->mark_deleted($deletedSignature->id);
        $deletedSignature->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestUserUtilities::removeAllCreatedUserSignatures();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testRetrieveSignature_SignatureIdParameterIsMissing_ThrowsSugarApiExceptionMissingParameter()
    {
        $args = array();

        $this->setExpectedException("SugarApiExceptionMissingParameter");
        $actual = $this->signaturesApi->retrieveSignature($this->api, $args);
    }

    public function testRetrieveSignature_SignatureDoesNotExist_ThrowsSugarApiExceptionNotFound()
    {
        $args = array(
            "signatureId" => $this->signature3->id,
        );

        $this->setExpectedException("SugarApiExceptionNotFound");
        $actual = $this->signaturesApi->retrieveSignature($this->api, $args);
    }

    public function testRetrieveSignature_SignatureExists_ReturnsSuccessfully()
    {
        $args = array(
            "signatureId" => $this->signature1->id,
        );

        $expected = array(
            "id"      => $this->signature1->id,
            "name"    => $this->signature1->name,
            "user_id" => $this->_user->id,
        );
        $actual   = $this->signaturesApi->retrieveSignature($this->api, $args);
        $this->assertEquals($expected["id"], $actual["id"], "Incorrect signature Id was returned");
        $this->assertEquals($expected["name"], $actual["name"], "Incorrect signature name was returned");
        $this->assertEquals($expected["user_id"], $actual["user_id"], "Signature for another user was returned");
    }

    public function testListSignatures_SignaturesExist_ReturnsSuccessfully()
    {
        $args = array();

        $expected = 2;
        $actual   = $this->signaturesApi->listSignatures($this->api, $args);
        $this->assertEquals($expected, count($actual["records"]), "Incorrect number of signatures returned");
    }

    public function testListSignatures_LimitArgumentExists_NextOffsetIsNotLessThanZero()
    {
        $args = array(
            "max_num" => 1,
        );

        $expected = 1;
        $actual   = $this->signaturesApi->listSignatures($this->api, $args);
        $this->assertEquals($expected, count($actual["next_offset"]), "Incorrect next offset");
    }

    public function testListSignatures_LimitAndOffsetArgumentsExistSuchThatNoMoreRecordsAreFound_ReturnsOneRecordAndNextOffsetIsNegativeOne()
    {
        $args = array(
            "max_num" => 3,
            "offset"  => 1,
        );

        $expectedCount  = 1;
        $expectedOffset = -1;
        $actual         = $this->signaturesApi->listSignatures($this->api, $args);
        $this->assertEquals($expectedCount, count($actual["records"]), "Incorrect number of signatures returned");
        $this->assertEquals($expectedOffset, $actual["next_offset"], "Incorrect next offset");
    }

    public function testListSignatures_UserHasNoSignatures_ReturnsZeroRecordsAndNextOffsetIsNegativeOne()
    {
        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();
        $args                    = array();

        $expectedCount  = 0;
        $expectedOffset = -1;
        $actual   = $this->signaturesApi->listSignatures($this->api, $args);
        $this->assertEquals($expectedCount, count($actual["records"]), "Incorrect number of signatures returned");
        $this->assertEquals($expectedOffset, $actual["next_offset"], "Incorrect next offset");
    }

    public function testListSignatures_OffsetArgumentIsEnd_ReturnsNoRecordsAndNextOffsetIsNegativeOne()
    {
        $args = array(
            "offset" => "end",
        );

        $expectedCount  = 0;
        $expectedOffset = -1;
        $actual         = $this->signaturesApi->listSignatures($this->api, $args);
        $this->assertEquals($expectedCount, count($actual["records"]), "Incorrect number of signatures returned");
        $this->assertEquals($expectedOffset, $actual["next_offset"], "Incorrect next offset");
    }
}
