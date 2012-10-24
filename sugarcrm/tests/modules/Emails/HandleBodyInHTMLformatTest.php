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
 
require_once('modules/Emails/Email.php');
require_once('include/SugarPHPMailer.php');

/**
 * Test cases for Bug 30591
 */
class HandleBodyInHTMLformatTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $sugarMailer;
	private $email;
	
	public function setUp()
	{
	    global $current_user;
	    
	    $current_user = SugarTestUserUtilities::createAnonymousUser();
		$this->sugarMailer = new SugarPHPMailer();
		$this->email = new Email();
	}
	
	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
	
	public function testHandleBodyInHtmlformat ()
	{
		$emailBodyInHtml = <<<EOQ
Check to see if &quot; &lt; &gt; &#039; was translated
to " < > '
EOQ;

		$emailBodyInHtmlResult = <<<EOQ
Check to see if " < > ' was translated
to " < > '
EOQ;
		$this->email->description_html = $emailBodyInHtml;
		$this->assertNotEquals($this->sugarMailer->Body, $emailBodyInHtmlResult);
		$this->email->handleBodyInHTMLformat($this->sugarMailer);
		$this->assertEquals($this->sugarMailer->Body, $emailBodyInHtmlResult);
	}
}
