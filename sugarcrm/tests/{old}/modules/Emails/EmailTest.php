<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once "tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * Test cases for Bug 30591
 *
 * @coversDefaultClass Email
 */
class EmailTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $email;

	public function setUp()
	{
	    global $current_user;

	    $current_user = BeanFactory::newBean("Users");
        $current_user->getSystemUser();
	    $this->email = new Email();
	    $this->email->email2init();
	}

	public function tearDown()
	{
        SugarTestEmailUtilities::removeAllCreatedEmails();
		unset($this->email);
		// SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
	}

    public function saveAndSetDateSentProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT, null, null),
            array(Email::EMAIL_STATE_ARCHIVED, null, null),
            array(Email::EMAIL_STATE_ARCHIVED, null, null),
            array(Email::EMAIL_STATE_ARCHIVED, '2014-06-22', '10:44'),
        );
    }

    /**
     * @covers ::save
     * @dataProvider saveAndSetDateSentProvider
     * @param string $state
     * @param null|string $dateStart
     * @param null|string $timeStart
     */
    public function testSave_DateSentIsSet($state, $dateStart, $timeStart)
    {
        $this->email->state = $state;
        $this->email->date_start = $dateStart;
        $this->email->time_start = $timeStart;
        $this->email->save();
        SugarTestEmailUtilities::setCreatedEmail($this->email->id);

        $this->assertNotEmpty($this->email->date_sent);
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

    public function testEmail2ParseAddresses_ParameterIsEmpty_EmptyArrayIsReturned()
    {
        $actual = $this->email->email2ParseAddresses('');
        $this->assertCount(0, $actual, 'An empty array should have been returned.');
    }

    /**
     * @dataProvider dataProviderEmailAddressParsing
     * @param string $fullEmailAddress
     * @param string $expDisplayName
     * @param string $expEmailAddress
     */
    public function testEmail2ParseEmailAddresses($fullEmailAddress, $expDisplayName, $expEmailAddress)
    {
        $result = $this->email->email2ParseAddresses($fullEmailAddress);
        $this->assertEquals($expDisplayName, $result[0]['display'], 'Unexpected Email Display Name');
        $this->assertEquals($expEmailAddress, $result[0]['email'], 'Unexpected Email Address');
    }

    /**
     * @dataProvider dataProviderEmailAddressParsing
     * @param string $fullEmailAddress
     * @param string $expDisplayName Not used in this test.
     * @param string $expEmailAddress
     */
    public function testEmail2ParseEmailAddressesAddressOnly($fullEmailAddress, $expDisplayName, $expEmailAddress)
    {
        $result = $this->email->email2ParseAddressesForAddressesOnly($fullEmailAddress);
        $this->assertEquals($expEmailAddress, $result[0], 'Unexpected Email Address');
    }

    public function dataProviderEmailAddressParsing()
    {
        return array(
            array(htmlspecialchars('John Doe<john@doe.com>'), 'John Doe', 'john@doe.com'),
            array(htmlspecialchars('Jo<hn Doe<john@doe.com>'), 'Jo<hn Doe', 'john@doe.com'),
            array(htmlspecialchars('Jo>hn Doe<john@doe.com>'), 'Jo>hn Doe', 'john@doe.com'),
            array(htmlspecialchars('Jo>h<n Doe<john@doe.com>'), 'Jo>h<n Doe', 'john@doe.com'),
            array(htmlspecialchars('Jo>h<n Doe  <john@doe.com>'), 'Jo>h<n Doe', 'john@doe.com'),
            array(htmlspecialchars("Jo'h<n D\"oe  <john@doe.com>"), "Jo'h<n D\"oe", 'john@doe.com'),
        );
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
     * @group email
     * @group mailer
     */
    public function testEmailSend_Success()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $mockMailer = new MockMailer($config);
        MockMailerFactory::setMailer($mockMailer);

        $em = new Email();
        $em->email2init();
        $em->_setMailerFactoryClassName('MockMailerFactory');

        $em->name = "This is the Subject";
        $em->description_html = "This is the HTML Description";
        $em->description      = "This is the Text Description";

        $from       = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");
        $replyto    = $from;
        $to         = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");
        $cc         = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");

        $em->from_addr = $from->getEmail();
        $em->from_name = $from->getName();

        $em->reply_to_addr = $replyto->getEmail();
        $em->reply_to_name = $replyto->getName();

        $em->to_addrs_arr = array(
            array(
                'email'     => $to->getEmail(),
                'display'   => $to->getName(),
            )
        );
        $em->cc_addrs_arr = array(
            array(
                'email'     => $cc->getEmail(),
                'display'   => $cc->getName(),
            )
        );

        $em->send();

        $data = $mockMailer->toArray();
        $this->assertEquals($em->description_html, $data['htmlBody']);
        $this->assertEquals($em->description, $data['textBody']);

        $headers = $mockMailer->getHeaders();
        $this->assertEquals($em->name, $headers['Subject']);
        $this->assertEquals($from->getEmail(), $headers['From'][0]);
        $this->assertEquals($from->getName(),  $headers['From'][1]);
        $this->assertEquals($replyto->getEmail(), $headers['Reply-To'][0]);
        $this->assertEquals($replyto->getName(),  $headers['Reply-To'][1]);

        $recipients = $mockMailer->getRecipients();

        $actual_to=array_values($recipients['to']);
        $this->assertEquals($to->getEmail(), $actual_to[0]->getEmail(), "TO Email Address Incorrect");
        $this->assertEquals($to->getName(),  $actual_to[0]->getName(),  "TO Name Incorrect");

        $actual_cc=array_values($recipients['cc']);
        $this->assertEquals($to->getEmail(), $actual_cc[0]->getEmail(), "CC Email Address Incorrect");
        $this->assertEquals($to->getName(),  $actual_cc[0]->getName(),  "CC Name Incorrect");

        $this->assertEquals(true,$mockMailer->wasSent());
        OutboundEmailConfigurationTestHelper::tearDown();
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

    /**
     * @covers ::getOutboundEmailDropdown
     */
    public function testGetOutboundEmailDropdown()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);

        SugarTestHelper::setUp('current_user');
        $systemConfig = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $userConfigs = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        $email = new Email();
        $options = $email->getOutboundEmailDropdown();
        $optionKeys = array_keys($options);

        $this->assertCount(3, $options, 'There should be three options');
        $this->assertSame($systemConfig->id, $optionKeys[0], 'The system configuration should be the first option');

        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @covers ::save
     * @covers ::updateTeamsForAttachments
     * @covers ::updateTeamsForAttachment
     * @covers Note::save
     */
    public function testUpdateTeamsForAttachments()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $data = array(
            'email_type' => 'Emails',
            'email_id' => $email->id,
        );
        $note1 = SugarTestNoteUtilities::createNote('', $data);
        $note2 = SugarTestNoteUtilities::createNote('', $data);

        // Change the teams on the email.
        $teams = BeanFactory::getBean('TeamSets');
        $email->team_id = 'East';
        $email->team_set_id = $teams->addTeams(array('East', 'West'));
        //BEGIN SUGARCRM flav=ent ONLY
        $email->team_set_selected_id = 'East';
        //END SUGARCRM flav=ent ONLY
        $email->save();

        $this->assertEquals($email->team_set_id, $note1->team_set_id, 'note1.team_set_id does not match');
        $this->assertEquals($email->team_set_id, $note2->team_set_id, 'note2.team_set_id does not match');
        $this->assertEquals($email->team_id, $note1->team_id, 'note1.team_id does not match');
        $this->assertEquals($email->team_id, $note2->team_id, 'note2.team_id does not match');
        //BEGIN SUGARCRM flav=ent ONLY
        $this->assertEquals(
            $email->team_set_selected_id,
            $note1->team_set_selected_id,
            'note1.team_set_selected_id does not match'
        );
        $this->assertEquals(
            $email->team_set_selected_id,
            $note2->team_set_selected_id,
            'note2.team_set_selected_id does not match'
        );
        //END SUGARCRM flav=ent ONLY
    }
}


class MockMailer extends SmtpMailer
{
    var $_sent;

    function __construct(OutboundEmailConfiguration $config) {
        $this->_sent = false;
        $this->config = $config;
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From,   $config->getFrom());
        $headers->setHeader(EmailHeaders::Sender, $config->getFrom());
        $this->headers = $headers;
        $this->recipients = new RecipientsCollection();
    }

    public function getHeaders() {
        return($this->headers->packageHeaders());
    }

    public function getRecipients() {
        return $this->recipients->getAll();
    }

    public function send() {
        $this->_sent = true;
    }

    public function wasSent() {
        return $this->_sent;
    }

    public function toArray() {
        return $this->asArray($this);
    }

    private function asArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        return $d;
    }
}

class MockMailerFactory extends MailerFactory
{
    private static $mailer;

    public static function setMailer(BaseMailer $mailer)
    {
        static::$mailer = $mailer;
    }

    public static function getMailer(OutboundEmailConfiguration $config)
    {
        return static::$mailer;
    }
}
