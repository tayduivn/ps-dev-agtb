<?php
/********************************************************************************
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

require_once "modules/Mailer/EmailHeaders.php";

class EmailHeadersTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group mailer
     */
    public function testSetPriority_ThroughSetHeader_PassInInteger_PriorityIsUpdated() {
        $expected = 5;
        $headers  = new EmailHeaders();
        $headers->setHeader(EmailHeaders::Priority, $expected);
        $actual = $headers->getPriority();
        self::assertEquals($expected, $actual, "The priority should have changed to {$expected}");
    }

    /**
     * @group mailer
     */
    public function testSetPriority_ThroughSetHeader_PassInString_PriorityIsNotUpdated() {
        $invalidPriority = "5";
        $headers         = new EmailHeaders();
        $expected        = $headers->getPriority();
        $headers->setHeader(EmailHeaders::Priority, $invalidPriority);
        $actual = $headers->getPriority();
        self::assertEquals($expected, $actual, "The priority should have remained {$expected}");
    }

    /**
     * @group mailer
     */
    public function testSetRequestConfirmation_ThroughSetHeader_PassInBoolean_RequestConfirmationIsUpdated() {
        $expected = true;
        $headers  = new EmailHeaders();
        $headers->setHeader(EmailHeaders::DispositionNotificationTo, $expected);
        $actual = $headers->getRequestConfirmation();
        self::assertTrue($actual, "The request confirmation flag should have changed to true");
    }

    /**
     * @group mailer
     */
    public function testSetRequestConfirmation_ThroughSetHeader_PassInInteger_RequestConfirmationIsNotUpdated() {
        $invalidRequestConfirmation = 1;
        $headers                    = new EmailHeaders();
        $headers->setHeader(EmailHeaders::DispositionNotificationTo, $invalidRequestConfirmation);
        $actual = $headers->getRequestConfirmation();
        self::assertFalse($actual, "The request confirmation flag should have remained false");
    }

    /**
     * @group mailer
     */
    public function testSetSubject_ThroughSetHeader_PassInString_SubjectIsUpdated() {
        $expected = "this is a subject";
        $headers  = new EmailHeaders();
        $headers->setHeader(EmailHeaders::Subject, $expected);
        $actual = $headers->getSubject();
        self::assertEquals($expected, $actual, "The subject should have changed to {$expected}");
    }

    /**
     * @group mailer
     */
    public function testSetSubject_ThroughSetHeader_PassInInteger_MailerExceptionIsThrown() {
        self::setExpectedException("MailerException");
        $invalidSubject = 1;
        $headers        = new EmailHeaders();
        $headers->setHeader(EmailHeaders::Subject, $invalidSubject);
        $actual = $headers->getSubject(); // hopefully nothing is actually returned
    }

    /**
     * @group mailer
     */
    public function testAddCustomHeader_ThroughSetHeader_PassInStrings_CustomHeaderIsAdded() {
        $key      = "X-CUSTOM-HEADER";
        $expected = "custom header value";
        $headers  = new EmailHeaders();
        $headers->setHeader($key, $expected);
        $actual = $headers->getCustomHeader($key);
        self::assertEquals($expected, $actual, "The custom header should have been added");
    }

    /**
     * @group mailer
     */
    public function testAddCustomHeader_ThroughSetHeader_UpdateExistingCustomHeader() {
        $headers = new EmailHeaders();

        // first set the custom header to something
        $key   = "X-CUSTOM-HEADER";
        $value = "custom header value";
        $headers->setHeader($key, $value);

        // change the existing custom header
        $expected = "a different value";
        $headers->setHeader($key, $expected);

        $actual = $headers->getCustomHeader($key);
        self::assertEquals($expected, $actual, "The custom header should have changed to '{$expected}'");
    }

    /**
     * @group mailer
     */
    public function testAddCustomHeader_ThroughSetHeader_PassInValidKeyAndInvalidValue_MailerExceptionIsThrown() {
        self::setExpectedException("MailerException");
        $headers      = new EmailHeaders();
        $key          = "X-CUSTOM-HEADER";
        $invalidValue = 1;
        $headers->setHeader($key, $invalidValue);
    }

    /**
     * Didn't bother testing for the condition where EmailHeaders::buildFromArray is given a non-array as
     * a parameter because the failure will become apparent at the time of packaging the headers back
     * into an array. For example, packaging the From header will fail because a From header is required.
     * It makes more sense to raise this exception at the time of packaging because it is perfectly
     * valid to build headers from an array without the From header and then set the From header
     * explicitly, using its setter.
     *
     * @group mailer
     */
    public function testBuildFromArray_ResultIsSuccessful() {
        $from            = new EmailIdentity("foo@bar.com");
        $customHeaderKey = "X-CUSTOM-HEADER";
        $expected        = array(
            EmailHeaders::From    => $from,
            EmailHeaders::Subject => "this is a subject",
            $customHeaderKey      => "custom header value",
        );

        $headers = new EmailHeaders();
        $headers->buildFromArray($expected);

        $actual = $headers->getFrom();
        self::assertEquals($expected[EmailHeaders::From]->getEmail(), $actual->getEmail(), "The from should be " . $expected[EmailHeaders::From]->getEmail());

        $actual = $headers->getSubject();
        self::assertEquals($expected[EmailHeaders::Subject], $actual, "The subject should be {$expected[EmailHeaders::Subject]}");

        $actual = $headers->getCustomHeader($customHeaderKey);
        self::assertEquals($expected[$customHeaderKey], $actual, "The custom header should be {$expected[$customHeaderKey]}");
    }

    /**
     * From and Subject are the only required headers, although others will be set by default. Since the required
     * headers may change over time -- potentially making this test brittle -- this test was written such that it
     * is only concerned with guaranteeing that the headers passed in to the object are present.
     *
     * @group mailer
     */
    public function testPackageHeaders_ResultIsSuccessful() {
        $from            = new EmailIdentity("foo@bar.com");
        $customHeaderKey = "X-CUSTOM-HEADER";
        $expected        = array(
            EmailHeaders::From    => $from,
            EmailHeaders::Subject => "this is a subject",
            $customHeaderKey      => "custom header value",
        );

        $headers = new EmailHeaders();
        $headers->buildFromArray($expected);
        $actual = $headers->packageHeaders();

        self::assertEquals($expected[EmailHeaders::From]->getEmail(), $actual[EmailHeaders::From][0], "The from should be " . $expected[EmailHeaders::From]->getEmail());
        self::assertEquals($expected[EmailHeaders::Subject], $actual[EmailHeaders::Subject], "The subject should be {$expected[EmailHeaders::Subject]}");
        self::assertEquals($expected[$customHeaderKey], $actual[$customHeaderKey], "The custom header should be {$expected[$customHeaderKey]}");
    }

    /**
     * @group mailer
     */
    public function testPackageHeaders_NoFromHeaderCausesAMailerExceptionToBeThrown() {
        $headers = new EmailHeaders();

        self::setExpectedException("MailerException");
        $actual = $headers->packageHeaders(); // hopefully nothing is actually returned
    }
}
