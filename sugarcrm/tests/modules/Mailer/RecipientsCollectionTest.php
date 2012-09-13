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
    /**
     * This test essentially tests clearAll, clearTo, clearCc and clearBcc.
     *
     * @group mailer
     */
    public function testClearAll_ResultIsSuccessful() {
        $recipientsCollection = new RecipientsCollection();

        $to = array(
            new EmailIdentity("foo@bar.com", "Foo Bar"),
            new EmailIdentity("qux@baz.net"),
        );
        $recipientsCollection->addRecipients($to);

        $bcc = array(
            new EmailIdentity("abc@123.com"),
            new EmailIdentity("tester@test.org"),
        );
        $recipientsCollection->addRecipients($bcc, RecipientsCollection::FunctionAddBcc);

        // make sure the recipients have been added
        $expected      = 4;
        $allRecipients = $recipientsCollection->getAll();
        $actual        = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been added");

        // now clear all recipients
        $recipientsCollection->clearAll();
        $expected      = 0;
        $allRecipients = $recipientsCollection->getAll();
        $actual        = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should remain");
    }

    /**
     * @group mailer
     */
    public function testAddRecipients_CallInvalidMethod_ThrowsException() {
        $recipientCollection = new RecipientsCollection();
        $recipients          = array(); // the recipients don't matter for this test case
        $function            = "asdf";  // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $recipientCollection->addRecipients($recipients, $function);
    }

    /**
     * This test essentially tests addRecipients and addTo.
     *
     * @group mailer
     */
    public function testAddRecipients_UseAddTo_PassInAnEmailIdentity_RecipientIsValidSoRecipientIsAdded() {
        $recipientsCollection = new RecipientsCollection();
        $recipient            = new EmailIdentity("foo@bar.com", "Foo Bar");

        $recipientsCollection->addRecipients($recipient);

        $expected = 1;
        $actual   = $recipientsCollection->getTo();
        self::assertEquals($expected, count($actual), "{$expected} recipients should have been added to the TO list");

        $expected = $recipient->getEmail();
        self::assertEquals($expected, $actual[$expected]->getEmail());
    }

    /**
     * This test essentially tests addRecipients and addCc.
     *
     * @group mailer
     */
    public function testAddRecipients_UseAddCc_PassInAnArrayOfEmailIdentityObjects_NoInvalidRecipientsSoAllAreAdded() {
        $recipientsCollection = new RecipientsCollection();
        $recipients           = array(
            new EmailIdentity("foo@bar.com", "Foo Bar"),
            new EmailIdentity("qux@baz.net"),
        );

        $recipientsCollection->addRecipients($recipients, RecipientsCollection::FunctionAddCc);

        $expected = 2;
        $actual   = $recipientsCollection->getCc();
        self::assertEquals($expected, count($actual), "{$expected} recipients should have been added to the CC list");

        $expected = $recipients[1]->getEmail();
        self::assertEquals($expected, $actual[$expected]->getEmail());
    }

    /**
     * This test essentially tests getAll, getTo, getCc and getBcc.
     *
     * @group mailer
     */
    public function testGetAll_HasRecipients_ReturnsNonEmptyArrays() {
        $recipientsCollection = new RecipientsCollection();

        $to = array(
            new EmailIdentity("foo@bar.com", "Foo Bar"),
            new EmailIdentity("qux@baz.net"),
        );
        $recipientsCollection->addRecipients($to);

        $cc = array(
            new EmailIdentity("abc@123.com"),
        );
        $recipientsCollection->addRecipients($cc, RecipientsCollection::FunctionAddCc);

        $bcc = array(
            new EmailIdentity("tester@test.org"),
        );
        $recipientsCollection->addRecipients($bcc, RecipientsCollection::FunctionAddBcc);

        $expected      = 4;
        $allRecipients = $recipientsCollection->getAll();
        $actual        = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been added");

        $expected = $to[1]->getEmail();
        $actual   = $allRecipients['to'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the TO list");

        $expected = $cc[0]->getEmail();
        $actual   = $allRecipients['cc'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the CC list");

        $expected = $bcc[0]->getEmail();
        $actual   = $allRecipients['bcc'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the BCC list");
    }

    /**
     * This test essentially tests getAll, getTo, getCc and getBcc.
     *
     * @group mailer
     */
    public function testGetAll_HasNoRecipients_ReturnsEmptyArrays() {
        $recipientsCollection = new RecipientsCollection();

        $expected      = 0;
        $allRecipients = $recipientsCollection->getAll();
        $actual        = count($allRecipients['to']) + count($allRecipients['cc']) + count($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been found");
    }
}
