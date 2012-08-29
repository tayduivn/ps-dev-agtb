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

require_once('modules/Mailer/RecipientsCollection.php');

class RecipientsCollectionTest extends Sugar_PHPUnit_Framework_TestCase
{
	/*public function testAddRecipients_useAddTo_PassInAnEmailIdentity_NoInvalidRecipientsReturned() {
		$recipientsCollection = new RecipientsCollection();
		$recipient = new EmailIdentity('foo@bar.com', 'Foo Bar');

		$expected = 0;
		$actual = $recipientsCollection->addRecipients($recipient);
		self::assertEquals($expected, count($actual), "No invalid recipients should have been returned");
	}

	public function testAddRecipients_useAddTo_PassInAString_ReturnsTheRecipient() {
		$recipientsCollection = new RecipientsCollection();
		$recipient = 'foo@bar.com';

		$expected = 1;
		$actual = $recipientsCollection->addRecipients($recipient);
		self::assertEquals($expected, count($actual), "No invalid recipients should have been returned");
		self::assertEquals($recipient, $actual[0]);
	}

	public function testAddRecipients_useAddCc_PassInAnArrayOfEmailIdentityObjects_NoInvalidRecipientsReturned() {
		$recipientsCollection = new RecipientsCollection();
		$recipients = array(
			new EmailIdentity('foo@bar.com', 'Foo Bar'),
			new EmailIdentity('qux@baz.net'),
		);

		$expected = 0;
		$actual = $recipientsCollection->addRecipients($recipients, RecipientsCollection::FunctionAddCc);
		self::assertEquals($expected, count($actual), "No invalid recipients should have been returned");

		$cc = $recipientsCollection->getCc();
		self::assertEquals($recipients[1]->getEmail(), $cc[1]->getEmail());
	}

	public function testClearAll() {
		$recipientsCollection = new RecipientsCollection();
		$to = array(
			new EmailIdentity('foo@bar.com', 'Foo Bar'),
			new EmailIdentity('qux@baz.net'),
		);
		$bcc = array(
			new EmailIdentity('abc@123.com'),
			new EmailIdentity('tester@test.org'),
		);

		$invalidTos = $recipientsCollection->addRecipients($to);
		$invalidBccs = $recipientsCollection->addRecipients($bcc, RecipientsCollection::FunctionAddBcc);

		// make sure the recipients have been added
		$expected = 4;
		$allRecipients = $recipientsCollection->getAll();
		$actual = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
		self::assertEquals($expected, $actual, "{$expected} recipients should have been added");

		// now clear all recipients
		$recipientsCollection->clearAll();
		$expected = 0;
		$allRecipients = $recipientsCollection->getAll();
		$actual = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
		self::assertEquals($expected, $actual, "No recipients should remain");
	}*/
}
