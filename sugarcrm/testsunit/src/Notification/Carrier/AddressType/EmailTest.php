<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Notification\Carrier\AddressType;

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getOptions
     */
    public function testGetOptions()
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $emailAddress = $this->getMockBuilder('EmailAddresses')
            ->disableOriginalConstructor()
            ->setMethods(array('getAddressesForBean'))
            ->getMock();
        $user->emailAddress = $emailAddress;

        $list = array(
            array(
                'email_address' => 'email.good1@example.com',
                'invalid_email' => '0',
                'opt_out' => '0',
                'id' => 'id-1',
            ),
            array(
                'email_address' => 'email.good2@example.com',
                'invalid_email' => '0',
                'opt_out' => '0',
                'id' => 'id-2',
            ),
            array(
                'email_address' => 'email.invalid@example.com',
                'invalid_email' => '1',
                'opt_out' => '0',
                'id' => 'id-3',
            ),
            array(
                'email_address' => 'email.opt.out@example.com',
                'invalid_email' => '1',
                'opt_out' => '0',
                'id' => 'id-4',
            ),
        );

        $emailAddress->expects($this->once())->method('getAddressesForBean')
            ->with($this->equalTo($user))
            ->willReturn($list);

        $email = new Email();

        $options = $email->getOptions($user);

        $this->assertContains('email.good1@example.com', $options);
        $this->assertContains('email.good2@example.com', $options);
        $this->assertNotContains('email.invalid@example.com', $options);
        $this->assertNotContains('email.opt.out@example.com', $options);
        $this->assertCount(2, $options);
    }

    public function transportValueVariants()
    {
        return array(
            array('0', 'email.good0@example.com'),
            array('2', 'email.good2@example.com'),
            array('no-exists', 'email.good0@example.com'),
        );
    }

    /**
     * @dataProvider transportValueVariants
     * @covers ::getOptions
     */
    public function testGetTransportValue($index, $expects)
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $options = array(
            '0' => 'email.good0@example.com',
            '1' => 'email.good1@example.com',
            '2' => 'email.good2@example.com'
        );

        $email = $this->getMock('Sugarcrm\\Sugarcrm\\Notification\\Carrier\\AddressType\\Email', array('getOptions'));
        $email->expects($this->once())->method('getOptions')
            ->with($this->equalTo($user))
            ->willReturn($options);

        $this->assertEquals($expects, $email->getTransportValue($user, $index));
    }
}
