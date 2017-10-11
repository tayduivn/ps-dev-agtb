<?php
//FILE SUGARCRM flav=ent ONLY
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
class PMSEEmailHandlerTest extends PHPUnit_Framework_TestCase
{

    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testProcessEmailsFromJson()
    {
        $json = '{
            "to": ["geronimo@gmail.com"],
            "cc": ["ariana@gmail.com"],
            "bcc": ["joane.gill@gmail.com"]
        }';
        
        $flowData = array(
            "cas_id" => 1,
            "cas_index" => 1
        );
        
        $bean = new stdClass();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('processEmailsAndExpand'))
            ->getMock();

        $emailHandlerMock->expects($this->at(0))
            ->method('processEmailsAndExpand')
            ->will($this->returnValue("geronimo@gmail.com"));
        
        $emailHandlerMock->expects($this->at(1))
            ->method('processEmailsAndExpand')
            ->will($this->returnValue("ariana@gmail.com"));
        
        $emailHandlerMock->expects($this->at(2))
            ->method('processEmailsAndExpand')
            ->will($this->returnValue("joane.gill@gmail.com"));
        
        $result = $emailHandlerMock->processEmailsFromJson($bean, $json, $flowData);
        
        $this->assertEquals("geronimo@gmail.com", $result->to);
        $this->assertEquals("ariana@gmail.com", $result->cc);
        $this->assertEquals("joane.gill@gmail.com", $result->bcc);
    }

    public function testSendTemplateEmailAddressesNotDefined()
    {
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipients', 'retrieveBean', 'retrieveMailer'))
            ->getMock();

        $beanUtilsMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('mergeBeanInTemplate'))
            ->getMock();

        $sugarMailerMock = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipientsTo', 'addRecipientsCc', 'addRecipientsBcc', 'setHtmlBody', 'setTextBody',
                'setSubject', 'setHeader', 'send'))
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning'))
            ->getMock();
        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve'))
            ->getMock();

        $beanMock = new stdClass();

        $emailHandlerMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $emailHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->with('pmse_Emails_Templates')
            ->will($this->returnValue($templateMock));

        $emailHandlerMock->expects($this->once())
            ->method('retrieveMailer')
            ->will($this->returnValue($sugarMailerMock));

        $emailHandlerMock->setLogger($loggerMock);
        $emailHandlerMock->setBeanUtils($beanUtilsMock);

        $moduleName = 'Leads';

        $beanId = 'bean01';

        $addresses = new stdClass();

        $templateId = 'template01';

        $emailHandlerMock->sendTemplateEmail($moduleName, $beanId, $addresses, $templateId);
    }
    
    public function testSendTemplateEmailAddressesDefined()
    {
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipients', 'retrieveBean', 'retrieveMailer'))
            ->getMock();


        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning'))
            ->getMock();

        $beanUtilsMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('mergeBeanInTemplate'))
            ->getMock();

        $sugarMailerMock = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipientsTo', 'addRecipientsCc', 'addRecipientsBcc', 'setHtmlBody', 'setTextBody',
                'setSubject', 'setHeader', 'send'))
            ->getMock();

        $templateMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve'))
            ->getMock();

        $beanMock = new stdClass();

        $emailHandlerMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $emailHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->with('pmse_Emails_Templates')
            ->will($this->returnValue($templateMock));

        $emailHandlerMock->expects($this->once())
            ->method('retrieveMailer')
            ->will($this->returnValue($sugarMailerMock));

        $templateMock->from_name = 'administrator';
        $templateMock->from_address = 'admin@gmail.com';
        $templateMock->body = 'Hello Mr Goodman';
        $templateMock->body_html = '<h1>Hello Mr Goodman</h1>';
        $templateMock->subject = 'Nice to hear from you!';

        $emailHandlerMock->setLogger($loggerMock);
        $emailHandlerMock->setBeanUtils($beanUtilsMock);
        
        $moduleName = 'Leads';

        $beanId = 'bean01';

        $addresses = (object)array (
            "to" => array(
                (object)array("name" => "user01", "address" => "user01@mail.com"),
                (object)array("name" => "user02", "address" => "user02@mail.com")
            ),
            "cc" => array(
                (object)array("name" => "user03", "address" => "user03@mail.com"),
                (object)array("name" => "user04", "address" => "user04@mail.com")
            ),
            "bcc" => array(
                (object)array("name" => "user05", "address" => "user05@mail.com"),
                (object)array("name" => "user06", "address" => "user06@mail.com")
            )
        );

        $templateId = 'template01';
        $emailHandlerMock->sendTemplateEmail($moduleName, $beanId, $addresses, $templateId);
    }
    
    public function testSendTemplateEmailTemplateIdNotDefined()
    {
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieveMailer', 'retrieveBean', 'addRecipients'))
            ->getMock();

        $sugarMailerMock = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipientsTo', 'addRecipientsCc', 'addRecipientsBcc', 'setHtmlBody', 'setTextBody',
                'setSubject', 'setHeader', 'send'))
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning'))
            ->getMock();
        
        $beanUtilsMock = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('mergeBeanInTemplate'))
            ->getMock();

        $templateObjectMock = $this->getMockBuilder('pmse_Emails_Templates')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve'))
            ->getMock();

        $beanMock = new stdClass();
        
        $emailHandlerMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $emailHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->with('pmse_Emails_Templates')
            ->will($this->returnValue($templateObjectMock));

        $emailHandlerMock->expects($this->once())
            ->method('retrieveMailer')
            ->will($this->returnValue($sugarMailerMock));
        
        $templateObjectMock->from_name = 'administrator';
        $templateObjectMock->from_address = 'admin@gmail.com';
        $templateObjectMock->body = '';
        $templateObjectMock->body_html = '';
        $templateObjectMock->subject = 'Nice to hear from you!';

        $emailHandlerMock->setLogger($loggerMock);
        $emailHandlerMock->setBeanUtils($beanUtilsMock);
        
        $moduleName = 'Leads';

        $beanId = 'bean01';

        $addresses = (object)array (
            "to" => array(
                (object) array("name" => "user01", "address" => "user01@mail.com"),
                (object) array("name" => "user02", "address" => "user02@mail.com")
            ),
            "cc" => array(
                (object) array("name" => "user03", "address" => "user03@mail.com"),
                (object) array("name" => "user04", "address" => "user04@mail.com")
            ),
            "bcc" => array(
                (object) array("name" => "user05", "address" => "user05@mail.com"),
                (object) array("name" => "user06", "address" => "user06@mail.com")
            )
        );

        $templateId = '';
        $emailHandlerMock->sendTemplateEmail($moduleName, $beanId, $addresses, $templateId);
    }
    
    public function testDoesPrimaryEmailExistsFalse()
    {
        $field = new stdClass();
        $field->field = 'email_addresses_primary';
        $field->value = 'address@mail.com';

        $bean = new stdClass();
        $bean->id = 'beanId01';
        $bean->module_dir = 'Leads';
        $bean->emailAddress = $addressMock = $this->getMockBuilder('EmailAddress')
                ->disableOriginalConstructor()
                ->setMethods(array('getPrimaryAddress'))
                ->getMock();

        $historyDataMock = $this->getMockBuilder('PMSEHistoryData')
                ->disableOriginalConstructor()
                ->setMethods(array('savePredata'))
                ->getMock();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getPrimaryEmailKeyFromREQUEST', 'updateEmails'))
            ->getMock();
        
        $emailHandlerMock->expects($this->once())
            ->method('getPrimaryEmailKeyFromREQUEST')
            ->will($this->returnValue('someKey'));
        
        $_REQUEST['someKey'] = '';
        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);
        
        $this->assertEquals('address@mail.com', $_REQUEST['someKey']);
        $this->assertEquals(true, $result);
    }
    
    public function testDoesPrimaryEmailExistsTrue()
    {
        $field = new stdClass();
        $field->field = 'email_addresses_primary';
        $field->value = 'address@mail.com';

        $bean = new stdClass();
        $bean->id = 'beanId01';
        $bean->module_dir = 'Leads';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
                ->disableOriginalConstructor()
                ->setMethods(array('getPrimaryAddress'))
                ->getMock();

        $bean->emailAddress->expects($this->once())
                ->method('getPrimaryAddress')
                ->will($this->returnValue('address@mail.com'));
        
        $historyDataMock = $this->getMockBuilder('PMSEHistoryData')
                ->disableOriginalConstructor()
                ->setMethods(array('savePredata'))
                ->getMock();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getPrimaryEmailKeyFromREQUEST', 'updateEmails'))
            ->getMock();                
        
        $_REQUEST['someKey'] = '';
        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);
        
        $this->assertEquals('', $_REQUEST['someKey']);
        $this->assertEquals(true, $result);
    }
    
    public function testDoesPrimaryEmailExistsInvalidField()
    {
        $field = new stdClass();
        $field->field = '';
        $field->value = '';
        
        $bean = new stdClass();
        
        $historyDataMock = new stdClass();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getPrimaryEmailKeyFromREQUEST', 'updateEmails'))
            ->getMock();                
        
        $result = $emailHandlerMock->doesPrimaryEmailExists($field, $bean, $historyDataMock);
        
        $this->assertEquals(false, $result);
    }
    
    public function testGetPrimaryEmailKeyFromREQUESTInvalid()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('updateEmails'))
            ->getMock();

        $_REQUEST['emailAddress'] = 'admin@mail.com';
        $_REQUEST['Leads_email_widget_id'] = 1;
        

        $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
    }
    
    public function testGetPrimaryEmailKeyFromREQUESTValid()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('updateEmails'))
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1']= '';
        $_REQUEST['Leads1emailAddressPrimaryFlag']= 'primary@mail.com';

        $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
    }
    
    public function testGetPrimaryEmailKeyFromREQUESTValidPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('updateEmails'))
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';
        $_REQUEST['Leads1emailAddressPrimaryFlag']= 'primary@mail.com';
        $_REQUEST['LeadsemailAddressPrimaryFlag']= 'primary@mail.com';

        $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
    }
    
    public function testGetPrimaryEmailKeyFromREQUESTInvalidPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('updateEmails'))
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';
        $_REQUEST['LeadsemailAddressPrimaryFlag']= 'primary@mail.com';

        $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
    }
    
    public function testGetPrimaryEmailKeyFromREQUESTInvalidAllAddresses()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('updateEmails'))
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['Leads1emailAddress0'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';

        $emailHandlerMock->getPrimaryEmailKeyFromREQUEST($bean);
    }
    
    public function testUpdateEmails()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
                ->disableOriginalConstructor()
                ->setMethods(array('getAddressesByGUID'))
                ->getMock();
        
        $addresses = array(
            'address' => array(
                'primary_address' => 'address@mail.com',
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com'
            ),
        );
        
        $bean->emailAddress->expects($this->once())
                ->method('getAddressesByGUID')
                ->will($this->returnValue($addresses));
        
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('error', 'debug', 'info', 'warning'))
            ->getMock();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = "new@mail.com";
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }

    public function testUpdateEmailsWithValidAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
                ->disableOriginalConstructor()
                ->setMethods(array('getAddressesByGUID'))
                ->getMock();
        
        $addresses = array(
            'address' => array(
                'primary_address' => 1,
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com'
            ),
        );
        
        $bean->emailAddress->expects($this->once())
                ->method('getAddressesByGUID')
                ->will($this->returnValue($addresses));
        
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('error', 'debug', 'info', 'warning'))
            ->getMock();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = "new@mail.com";
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }
    
    public function testUpdateEmailsWithoutPrimaryAddress()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $bean->id = 'bean01';
        $bean->emailAddress = $this->getMockBuilder('EmailAddress')
                ->disableOriginalConstructor()
                ->setMethods(array('getAddressesByGUID'))
                ->getMock();
        
        $addresses = array(
            'address' => array(
                'email_address' => 'address@mail.com',
                'email_address_id' => 'address@mail.com'
            ),
        );
        
        $bean->emailAddress->expects($this->once())
                ->method('getAddressesByGUID')
                ->will($this->returnValue($addresses));
        
        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(array('error', 'debug', 'info', 'warning'))
            ->getMock();
        
        $emailHandlerMock = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();

        $_REQUEST['Leads_email_widget_id'] = 1;
        $_REQUEST['emailAddressWidget'] = '';
        $_REQUEST['Leads1emailAddress1']= 'primary@mail.com';

        $emailHandlerMock->setLogger($loggerMock);
        $newEmailAddress = "new@mail.com";
        $emailHandlerMock->updateEmails($bean, $newEmailAddress);
    }
    
    
}
