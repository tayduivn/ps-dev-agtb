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

require_once "modules/Mailer/EmailFormatter.php";

class EmailFormatterTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group email
     * @group mailer
     */
    public function testFormatTextBody_IncludeDisclosure_DisclosureIsAppendedToBody() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatTextBody_DoNotIncludeDisclosure_BodyIsNotChanged() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_IncludeDisclosure_DisclosureIsAppendedToBody() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_DoNotIncludeDisclosure_BodyIsNotChanged() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_HasInlineImages_ConvertInlineImagesToEmbeddedImages_ReturnsModifiedBodyAndArrayOfEmbeddedImagesToAttach() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_DoesNotHaveInlineImages_BodyIsNotChangedAndReturnedArrayIsEmpty() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * Formerly HandleBodyInHTMLformatTest::testHandleBodyInHtmlformat. This is really testing that from_html works,
     * but it's best not to lose a test and thus risk a regression.
     *
     * @group email
     * @group bug30591
     * @group mailer
     */
    public function testTranslateCharacters_HtmlEntitiesAreTranslatedToRealCharacters() {
        $body = "Check to see if &quot; &lt; &gt; &#039; was translated to \" < > '";

        $mockLocale = self::getMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue($body)); // return the exact same string

        $mockFormatter = self::getMock("EmailFormatter", array("retrieveDisclosureSettings"));
        $mockFormatter->expects(self::any())
            ->method("retrieveDisclosureSettings")
            ->will(self::returnValue(false));

        $expected = "Check to see if \" < > ' was translated to \" < > '";
        $actual   = $mockFormatter->translateCharacters($body, $mockLocale, "UTF-8");
        self::assertEquals($expected, $actual, "The HTML entities were not all translated properly");
    }
}
