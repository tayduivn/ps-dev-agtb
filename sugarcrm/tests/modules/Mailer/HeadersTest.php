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

require_once('modules/Mailer/Headers.php');

class HeadersTest extends Sugar_PHPUnit_Framework_TestCase
{
	/**
	 * @group mailer
	 */
	public function testSetPriority_PassInInteger_PriorityIsUpdated() {
		$expected = 5;
		$headers = new Headers();
		$headers->setPriority($expected);
		$actual = $headers->getPriority();
		self::assertEquals($expected, $actual, "The priority should have changed to {$expected}");
	}

	/**
	 * @group mailer
	 */
	public function testSetPriority_PassInString_PriorityIsNotUpdated() {
		$invalidPriority = "5";
		$headers = new Headers();
		$expected = $headers->getPriority();
		$headers->setPriority($invalidPriority);
		$actual = $headers->getPriority();
		self::assertEquals($expected, $actual, "The priority should have remained {$expected}");
	}

	/**
	 * @group mailer
	 */
	public function testSetRequestConfirmation_PassInBoolean_RequestConfirmationIsUpdated() {
		$expected = true;
		$headers = new Headers();
		$headers->setRequestConfirmation($expected);
		$actual = $headers->getRequestConfirmation();
		self::assertTrue($actual, "The request confirmation flag should have changed to true");
	}

	/**
	 * @group mailer
	 */
	public function testSetRequestConfirmation_PassInInteger_RequestConfirmationIsNotUpdated() {
		$invalidRequestConfirmation = 1;
		$headers = new Headers();
		$headers->setRequestConfirmation($invalidRequestConfirmation);
		$actual = $headers->getRequestConfirmation();
		self::assertFalse($actual, "The request confirmation flag should have remained false");
	}

	/**
	 * @group mailer
	 */
	public function testSetSubject_PassInString_SubjectIsUpdated() {
		$expected = "this is a subject";
		$headers = new Headers();
		$headers->setSubject($expected);
		$actual = $headers->getSubject();
		self::assertEquals($expected, $actual, "The subject should have changed to {$expected}");
	}

	/**
	 * @group mailer
	 */
	public function testSetSubject_PassInInteger_SubjectIsNotUpdated() {
		$invalidSubject = 1;
		$headers = new Headers();
		$headers->setSubject($invalidSubject);
		$actual = $headers->getSubject();
		self::assertNull($actual, "The subject should have remained null");
	}

	/**
	 * @group mailer
	 */
	public function testAddCustomHeader_PassInStrings_CustomHeaderIsAdded() {
		$key = "X-CUSTOM-HEADER";
		$expected = "custom header value";
		$headers = new Headers();
		$headers->addCustomHeader($key, $expected);
		$actual = $headers->getCustomHeader($key);
		self::assertEquals($expected, $actual, "The custom header should have been added");
	}

	/**
	 * @group mailer
	 */
	public function testAddCustomHeader_UpdateExistingCustomHeader() {
		$headers = new Headers();

		// first set the custom header to something
		$key = "X-CUSTOM-HEADER";
		$value = "custom header value";
		$headers->addCustomHeader($key, $value);

		// change the existing custom header
		$expected = "a different value";
		$headers->addCustomHeader($key, $expected);

		$actual = $headers->getCustomHeader($key);
		self::assertEquals($expected, $actual, "The custom header should have changed to '{$expected}'");
	}

	/**
	 * @group mailer
	 */
	public function testAddCustomHeader_PassInValidKeyAndInvalidValue_CustomHeaderIsNotUpdated() {
		$headers = new Headers();

		// first set the custom header to something valid
		$key = "X-CUSTOM-HEADER";
		$expected = "custom header value";
		$headers->addCustomHeader($key, $expected);

		// attempt to change the custom header, but it should fail
		$invalidValue = 1;
		$headers->addCustomHeader($key, $invalidValue);

		$actual = $headers->getCustomHeader($key);
		self::assertEquals($expected, $actual, "The custom header should have remained '{$expected}'");
	}

	/**
	 * Didn't bother testing for the condition where Headers::buildFromArray is given a non-array as a
	 * parameter because the failure will become apparent at the time of packaging the headers back
	 * into an array. For example, packaging the From header will fail because a From header is required.
	 * It makes more sense to raise this exception at the time of packaging because it is perfectly
	 * valid to build headers from an array without the From header and then set the From header
	 * explicitly, using its setter.
	 *
	 * @group mailer
	 */
	public function testBuildFromArray_ResultIsSuccessful() {
		$from = new EmailIdentity("foo@bar.com");
		$customHeaderKey = "X-CUSTOM-HEADER";
		$expected = array(
			Headers::From    => $from,
			Headers::Subject => "this is a subject",
			$customHeaderKey => "custom header value",
		);

		$headers = new Headers();
		$headers->buildFromArray($expected);

		$actual = $headers->getFrom();
		self::assertEquals($expected[Headers::From]->getEmail(), $actual->getEmail(), "The from should be " . $expected[Headers::From]->getEmail());

		$actual = $headers->getSubject();
		self::assertEquals($expected[Headers::Subject], $actual, "The subject should be {$expected[Headers::Subject]}");

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
		$from = new EmailIdentity("foo@bar.com");
		$customHeaderKey = "X-CUSTOM-HEADER";
		$expected = array(
			Headers::From    => $from,
			Headers::Subject => "this is a subject",
			$customHeaderKey => "custom header value",
		);

		$headers = new Headers();
		$headers->buildFromArray($expected);
		$actual = $headers->packageHeaders();

		self::assertEquals($expected[Headers::From]->getEmail(), $actual[Headers::From][0], "The from should be " . $expected[Headers::From]->getEmail());
		self::assertEquals($expected[Headers::Subject], $actual[Headers::Subject], "The subject should be {$expected[Headers::Subject]}");
		self::assertEquals($expected[$customHeaderKey], $actual[$customHeaderKey], "The custom header should be {$expected[$customHeaderKey]}");
	}

	/**
	 * @group mailer
	 */
	public function testPackageHeaders_NoFromHeaderCausesAMailerExceptionToBeThrown() {
		$exceptionWasCaught = false;

		try {
			$headers = new Headers();
			$actual = $headers->packageHeaders(); // hopefully nothing is actually returned
		} catch (MailerException $me) {
			$exceptionWasCaught = true;
		}

		if (!$exceptionWasCaught) {
			self::fail("A MailerException should have been raised because there is no from header");
		}
	}
}
