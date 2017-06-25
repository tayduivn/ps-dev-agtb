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
 


/**
 * @ticket 32487
 */
class ComposePackageTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $c = null;
	var $a = null;
	var $ac_id = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
        $mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $unid = uniqid();
        $time = date('Y-m-d H:i:s');

        $this->c = SugarTestContactUtilities::createContact();
		
		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
        unset($this->c);
    }

	public function testComposeFromMethodCallNoData()
	{    
	    $_REQUEST['forQuickCreate'] = true;
	    require_once('modules/Emails/Compose.php');
	    $data = array();
	    $compose_data = generateComposeDataPackage($data,FALSE);
	    
		$this->assertEquals('', $compose_data['to_email_addrs']);
    }
    
    public function testComposeFromMethodCallForContact()
    {    
	    $_REQUEST['forQuickCreate'] = true;
	    require_once('modules/Emails/Compose.php');
	    $data = array();
	    $data['parent_type'] = 'Contacts';
	    $data['parent_id'] = $this->c->id;
	    
	    $compose_data = generateComposeDataPackage($data,FALSE);

		$this->assertEquals('Contacts', $compose_data['parent_type']);
		$this->assertEquals($this->c->id, $compose_data['parent_id']);
		$this->assertEquals($this->c->name, $compose_data['parent_name']);
    }

    public function testGenerateComposeDataPackage_SingleEmailAddressWithoutName()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Contacts',
            'parent_id' => $this->c->id,
            'to_email_addrs' => $address->email_address,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Contacts', $data['parent_type']);
        $this->assertSame($this->c->id, $data['parent_id']);
        $this->assertSame($this->c->name, $data['parent_name']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame($args['to_email_addrs'], $data['to_email_addrs']);
        $this->assertCount(1, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $address->id,
                'email_address' => $address->email_address,
            ],
            $data['to'][0]
        );
    }

    public function testGenerateComposeDataPackage_SingleEmailAddressWithName()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Contacts',
            'parent_id' => $this->c->id,
            'to_email_addrs' => "{$this->c->name} <{$address->email_address}>",
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Contacts', $data['parent_type']);
        $this->assertSame($this->c->id, $data['parent_id']);
        $this->assertSame($this->c->name, $data['parent_name']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame($args['to_email_addrs'], $data['to_email_addrs']);
        $this->assertCount(1, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $address->id,
                'email_address' => $address->email_address,
            ],
            $data['to'][0]
        );
    }

    public function delimiterProvider()
    {
        return [
            [','],
            [';'],
        ];
    }

    /**
     * @dataProvider delimiterProvider
     */
    public function testGenerateComposeDataPackage_MultipleEmailAddresses($delimiter)
    {
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Contacts',
            'parent_id' => $this->c->id,
            'to_email_addrs' => "{$address1->email_address}{$delimiter} {$this->c->name} <{$address2->email_address}>",
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Contacts', $data['parent_type']);
        $this->assertSame($this->c->id, $data['parent_id']);
        $this->assertSame($this->c->name, $data['parent_name']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame($args['to_email_addrs'], $data['to_email_addrs']);
        $this->assertCount(2, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $address1->id,
                'email_address' => $address1->email_address,
            ],
            $data['to'][0]
        );
        $this->assertEquals(
            [
                'email_address_id' => $address2->id,
                'email_address' => $address2->email_address,
            ],
            $data['to'][1]
        );
    }

    public function testGenerateComposeDataPackage_UsingParentBeanWithFullName()
    {
        $primaryId = $this->c->emailAddress->getGuid($this->c->email1);

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Contacts',
            'parent_id' => $this->c->id,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Contacts', $data['parent_type']);
        $this->assertSame($this->c->id, $data['parent_id']);
        $this->assertSame($this->c->name, $data['parent_name']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame("{$this->c->full_name} <{$this->c->email1}>", $data['to_email_addrs']);
        $this->assertCount(1, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId,
                'email_address' => $this->c->email1,
                'parent_type' => 'Contacts',
                'parent_id' => $this->c->id,
                'parent_name' => $this->c->full_name,
            ],
            $data['to'][0]
        );
    }

    public function testGenerateComposeDataPackage_UsingParentBeanWithoutFullName()
    {
        $primaryId = $this->c->emailAddress->getGuid($this->c->email1);
        unset($this->c->full_name);

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Contacts',
            'parent_id' => $this->c->id,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Contacts', $data['parent_type']);
        $this->assertSame($this->c->id, $data['parent_id']);
        $this->assertSame($this->c->name, $data['parent_name']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame("<{$this->c->email1}>", $data['to_email_addrs']);
        $this->assertCount(1, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId,
                'email_address' => $this->c->email1,
                'parent_type' => 'Contacts',
                'parent_id' => $this->c->id,
                'parent_name' => '',
            ],
            $data['to'][0]
        );
    }

    public function testGenerateComposeDataPackage_FromCase()
    {
        $contact2 = SugarTestContactUtilities::createContact();

        $primaryId1 = $this->c->emailAddress->getGuid($this->c->email1);
        $primaryId2 = $contact2->emailAddress->getGuid($contact2->email1);

        $case = SugarTestCaseUtilities::createCase();
        $case->load_relationship('contacts');
        $case->contacts->add([$this->c, $contact2]);
        $case->case_number = 27;

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'parent_type' => 'Cases',
            'parent_id' => $case->id,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Cases', $data['parent_type']);
        $this->assertSame($case->id, $data['parent_id']);
        $this->assertSame($case->name, $data['parent_name']);
        $this->assertSame("[CASE:{$case->case_number}] {$case->name}", $data['subject']);
        $this->assertEmpty($data['attachments']);
        $this->assertEmpty($data['email_id']);
        $this->assertSame(
            "{$this->c->full_name} <{$this->c->email1}>, {$contact2->full_name} <{$contact2->email1}>",
            $data['to_email_addrs']
        );
        $this->assertCount(2, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId1,
                'email_address' => $this->c->email1,
                'parent_type' => 'Contacts',
                'parent_id' => $this->c->id,
                'parent_name' => $this->c->full_name,
            ],
            $data['to'][0]
        );
        $this->assertEquals(
            [
                'email_address_id' => $primaryId2,
                'email_address' => $contact2->email1,
                'parent_type' => 'Contacts',
                'parent_id' => $contact2->id,
                'parent_name' => $contact2->full_name,
            ],
            $data['to'][1]
        );
    }

    public function testGenerateComposeDataPackage_FromQuote()
    {
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $quote = SugarTestQuoteUtilities::createQuote();
        $email = SugarTestEmailUtilities::createEmail();
        $email->description_html = 'foo bar <b>biz</b> baz';
        $email->to_addrs = "{$address1->email_address}, {$this->c->name} <{$address2->email_address}>";

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'recordId' => $email->id,
            'parent_type' => 'Quotes',
            'parent_id' => $quote->id,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame('Quotes', $data['parent_type']);
        $this->assertSame($quote->id, $data['parent_id']);
        $this->assertSame($quote->name, $data['parent_name']);
        $this->assertSame($email->name, $data['subject']);
        $this->assertSame($email->description_html, $data['body']);
        $this->assertEmpty($data['attachments']);
        $this->assertSame($email->id, $data['email_id']);
        $this->assertSame($email->to_addrs, $data['to_email_addrs']);
        $this->assertCount(2, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $address1->id,
                'email_address' => $address1->email_address,
            ],
            $data['to'][0]
        );
        $this->assertEquals(
            [
                'email_address_id' => $address2->id,
                'email_address' => $address2->email_address,
            ],
            $data['to'][1]
        );
    }

    public function testGenerateComposeDataPackage_ForListView()
    {
        $contact2 = SugarTestContactUtilities::createContact();

        $primaryId1 = $this->c->emailAddress->getGuid($this->c->email1);
        $primaryId2 = $contact2->emailAddress->getGuid($contact2->email1);

        $_REQUEST['forQuickCreate'] = true;
        $_REQUEST['ListView'] = true;
        $_REQUEST['action_module'] = 'Contacts';
        $_REQUEST['uid'] = "{$this->c->id},{$contact2->id}";
        require_once 'modules/Emails/Compose.php';

        $data = generateComposeDataPackage([], false);

        $this->assertSame(
            "{$this->c->full_name} <{$this->c->email1}>,{$contact2->full_name} <{$contact2->email1}>",
            $data['to_email_addrs']
        );
        $this->assertCount(2, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId1,
                'email_address' => $this->c->email1,
            ],
            $data['to'][0]
        );
        $this->assertEquals(
            [
                'email_address_id' => $primaryId2,
                'email_address' => $contact2->email1,
            ],
            $data['to'][1]
        );
    }

    public function testGenerateComposeDataPackage_ForReplyAll()
    {
        $contact2 = SugarTestContactUtilities::createContact();

        $primaryId1 = $this->c->emailAddress->getGuid($this->c->email1);
        $primaryId2 = $contact2->emailAddress->getGuid($contact2->email1);
        $primaryId3 = $GLOBALS['current_user']->emailAddress->getGuid($GLOBALS['current_user']->email1);

        $email = SugarTestEmailUtilities::createEmail('', [
            'from_addr' => $this->c->email1,
            'to_addrs' => $GLOBALS['current_user']->email1,
            'cc_addrs' => $contact2->email1,
        ]);

        $_REQUEST['forQuickCreate'] = true;
        require_once 'modules/Emails/Compose.php';

        $args = [
            'replyForward' => true,
            'reply' => 'replyAll',
            'record' => $email->id,
        ];
        $data = generateComposeDataPackage($args, false);

        $this->assertSame($this->c->email1, $data['to_email_addrs']);
        $this->assertSame("$contact2->email1, {$GLOBALS['current_user']->email1}", $data['cc_addrs']);
        $this->assertCount(1, $data['to']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId1,
                'email_address' => $this->c->email1,
            ],
            $data['to'][0]
        );
        $this->assertCount(2, $data['cc']);
        $this->assertEquals(
            [
                'email_address_id' => $primaryId2,
                'email_address' => $contact2->email1,
            ],
            $data['cc'][0]
        );
        $this->assertEquals(
            [
                'email_address_id' => $primaryId3,
                'email_address' => $GLOBALS['current_user']->email1,
            ],
            $data['cc'][1]
        );
    }
}
