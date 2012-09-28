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

require_once "modules/Reports/utils.php";
require_once "modules/Users/User.php";

class ReportsUtilitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group reports
     * @group mailer
     */
    public function testSendNotificationOfInvalidReport_InvalidRecipientAddress_ThrowsMailerException() {
        $recipient = new User();
        $recipient->email1 = null;
        $recipient->email2 = null;

        self::setExpectedException("MailerException");
        $reportsUtilities = new ReportsUtilities();
        $reportsUtilities->sendNotificationOfInvalidReport($recipient, "asdf");
    }

    /**
     * @group reports
     * @group mailer
     */
    public function testSendNotificationOfInvalidReport_AllMethodCallsAreSuccessful_NoExceptionsThrown() {
        self::markTestIncomplete(
            "Not yet implemented; requires mocking MailerFactory to return a mocked Mailer with a stub for send"
        );

        $recipient = new User();
        $recipient->email1 = null;
        $recipient->email2 = "foo@bar.com";

        $reportsUtilities = new ReportsUtilities();
        $reportsUtilities->sendNotificationOfInvalidReport($recipient, "asdf");
    }
}
