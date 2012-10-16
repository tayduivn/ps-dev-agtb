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

/**
 * Test cases for Bug 30591
 */
class EmailTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $email;
	
	public function setUp()
	{
	    global $current_user;
		
	    $current_user = SugarTestUserUtilities::createAnonymousUser();
	    $this->email = new Email();
	    $this->email->email2init();
	}
	
	public function tearDown()
	{
		unset($this->email);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
	}
	
	public function testSafeAttachmentName ()
	{
		$extArray[] = '0.py';
		$extArray[] = '1.php';
		$extArray[] = '2.php3';
		$extArray[] = '3.php4';
		$extArray[] = '4.php5';
		$extArray[] = '5.js';
		$extArray[] = '6.htm';
		$extArray[] = '7.html';
		$extArray[] = '8.txt';
		$extArray[] = '9.doc';
		$extArray[] = '10.xls';
		$extArray[] = '11.pdf';
		$extArray[] = '12';

		for ($i = 0; $i < count($extArray); $i++) {
			$result = $this->email->safeAttachmentName($extArray[$i]);
			if ($i < 8) {
				$this->assertEquals($result, true);
			} else {
				$this->assertEquals($result, false);
			}
		}
	}
	
	public function testEmail2ParseAddresses()
	{
		$emailDisplayName[] = '';
		$emailDisplayName[] = 'Shine Ye';
		$emailDisplayName[] = 'Roger,Smith';
		$emailAddress[] = 'masonhu@sugarcrm.com';
		$emailAddress[] = 'xye@sugarcrm.com';
		$emailAddress[] = 'roger@sugarcrm.com';
		for ($j = 0; $j < count($emailDisplayName); $j++)
		{
			if ($j < 1)
				$emailString[] = $emailDisplayName[$j].$emailAddress[$j];
			else
				$emailString[] = $emailDisplayName[$j].'<'.$emailAddress[$j].'>';
			
		}
		$emailAddressString = implode(', ', $emailString);
		$result = $this->email->email2ParseAddresses($emailAddressString);
		$onlyEmailResult = $this->email->email2ParseAddressesForAddressesOnly($emailAddressString);
		for ($v = 0; $v < count($result); $v++)
		{
			$this->assertEquals($result[$v]['display'], $emailDisplayName[$v]);
			$this->assertEquals($result[$v]['email'], $emailAddress[$v]);
			$this->asserteQuals($onlyEmailResult[$v], $emailAddress[$v]);
		}
	}
	
	public function testDecodeDuringSend()
	{
		$testString = 'Replace sugarLessThan and sugarGreaterThan with &lt; and &gt;';
		$expectedResult = 'Replace &lt; and &gt; with &lt; and &gt;';
		$resultString = $this->email->decodeDuringSend($testString);
		$this->asserteQuals($resultString, $expectedResult);
	}

    public function configParamProvider()
    {
        $address_array =  array(
            'id1' => 'test1@example.com',
            'id2' => 'test2@example.com',
            'id3' => 'test3@example.com'
        );

        return array(
            array(',',$address_array,'test1@example.com,test2@example.com,test3@example.com'), // default and correct delimiter for email addresses
            array(';',$address_array,'test1@example.com;test2@example.com;test3@example.com'), // outlook's delimiter for email addresses
        );
    }

    /**
     * @group mailer
     */
    public function testEmailSend_Success()
    {
        //$this->markTestSkipped("In Progress ......");

        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $mockMailer = new MockMailer($config);

        $MockMailerFactoryClass = $this->getMockClass('MailerFactory', array('getMailer'));
        $MockMailerFactoryClass::staticExpects($this->once())
            ->method('getMailer')
            ->with($config)
            ->will($this->returnValue($mockMailer));

        $em = new Email();
        $em->email2init();

        $em->_setMailerFactoryClassName($MockMailerFactoryClass);

        $em->from_name = "Woody Woodpecker";
        $em->from_addr = "woody@woodpecker.com";

        $em->reply_to_name = "Captain Kangaroo";
        $em->reply_to_addr = "captainkangaroo@yahoo.com";

        $em->name = "This is the Subject";
        $em->description_html = "This is the HTML Description";
        $em->description      = "This is the Description";

        $em->send();

        // $mockMailer->dump();
    }

    /**
     * @group bug51804
     * @dataProvider configParamProvider
     * @param string $config_param
     * @param array $address_array
     * @param string $expected
     */
    public function testArrayToDelimitedString($config_param, $address_array, $expected)
    {
            $GLOBALS['sugar_config']['email_address_separator'] = $config_param;

            $this->assertEquals($expected,$this->email->_arrayToDelimitedString($address_array), 'Array should be delimited with correct delimiter');

    }
}

require_once "modules/Mailer/SmtpMailer.php"; // requires BaseMailer in order to extend it

class MockMailer extends SmtpMailer
{
    function __construct(OutboundEmailConfiguration $config) {
        $from    = new EmailIdentity("tony@tiger.com", "Tony Tiger");
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $from);
        $this->setHeaders($headers);
    }

    public function send() {
    }

    public function dump() {
        print_r($this);
    }
}
?>