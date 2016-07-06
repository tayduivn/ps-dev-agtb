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

namespace Sugarcrm\SugarcrmTests\Notification\Carrier\AddressType;

require_once 'modules/EmailAddresses/EmailAddress.php';

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email as AddressTypeEmail;
use EmailAddress;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressTypeEmail */
    protected $addressType = null;

    /** @var \User */
    protected $user = null;

    /** @var EmailAddress|\PHPUnit_Framework_MockObject_MockObject */
    protected $emailAddress = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addressType = new AddressTypeEmail();
        $this->emailAddress = $this->getMock('EmailAddress');
        $this->user = new \User();
        $this->user->id = create_guid();
        $this->user->emailAddress = $this->emailAddress;
    }

    /**
     * Data provider for testGetOptionsReturnsValidEmail
     *
     * @see EmailTest::testGetOptionsReturnsValidEmail
     * @return array
     */
    public static function getOptionsReturnsValidEmailProvider()
    {
        $rand = rand(1000, 9999);

        return array(
            'emptyReturnsEmpty' => array(
                'data' => array(),
                'expected' => array(),
            ),
            'optOutIsIgnored' => array(
                'data' => array(
                    array(
                        'opt_out' => true,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.net',
                    ),
                ),
                'expected' => array(
                    0 => 'email' . $rand . '@domain.net',
                ),
            ),
            'invalidEmailIsIgnored' => array(
                'data' => array(
                    array(
                        'opt_out' => false,
                        'invalid_email' => true,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                ),
                'expected' => array(
                    0 => 'email' . $rand . '@domain.com',
                ),
            ),
            'processesEmailsCorrectly' => array(
                'data' => array(
                    array(
                        'opt_out' => false,
                        'invalid_email' => true,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                    array(
                        'opt_out' => true,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@internet.com',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@sugar.com',
                    ),
                ),
                'expected' => array(
                    0 => 'email' . $rand . '@domain.com',
                    1 => 'email' . $rand . '@internet.com',
                    2 => 'email' . $rand . '@sugar.com',
                ),
            ),
        );
    }

    /**
     * getOptions should return all suitable emails of provided user
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email::getOptions
     * @dataProvider getOptionsReturnsValidEmailProvider
     * @param array $data
     * @param array $expected
     */
    public function testGetOptionsReturnsValidEmail(array $data, array $expected)
    {
        $this->emailAddress->method('getAddressesForBean')->with($this->equalTo($this->user))->willReturn($data);
        $result = $this->addressType->getOptions($this->user);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testGetTransportValueReturnsCorrectEmail
     *
     * @see EmailTest::testGetTransportValueReturnsCorrectEmail
     * @return array
     */
    public static function getTransportValueReturnsCorrectEmailProvider()
    {
        $rand = rand(1000, 9999);

        return array(
            'emptyReturnsNull' => array(
                'data' => array(),
                'key' => 0,
                'expected' => null,
            ),
            'optOutIsIgnored' => array(
                'data' => array(
                    array(
                        'opt_out' => true,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.net',
                    ),
                ),
                'key' => 0,
                'expected' => 'email' . $rand . '@domain.net',
            ),
            'invalidEmailIsIgnored' => array(
                'data' => array(
                    array(
                        'opt_out' => false,
                        'invalid_email' => true,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                ),
                'key' => 0,
                'expected' => 'email' . $rand . '@domain.com',
            ),
            'processesEmailsCorrectly' => array(
                'data' => array(
                    array(
                        'opt_out' => false,
                        'invalid_email' => true,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                    array(
                        'opt_out' => true,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@internet.com',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@sugar.com',
                    ),
                ),
                'key' => 1,
                'expected' => 'email' . $rand . '@internet.com',
            ),
            'incorrectKeyReturnsFirst' => array(
                'data' => array(
                    array(
                        'opt_out' => false,
                        'invalid_email' => true,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.com',
                    ),
                    array(
                        'opt_out' => true,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@domain.au',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@internet.com',
                    ),
                    array(
                        'opt_out' => false,
                        'invalid_email' => false,
                        'email_address' => 'email' . $rand . '@sugar.com',
                    ),
                ),
                'key' => 5,
                'expected' => 'email' . $rand . '@domain.com',
            ),
        );
    }

    /**
     * getTransportValue should return related email
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email::getTransportValue
     * @dataProvider getTransportValueReturnsCorrectEmailProvider
     * @param array $data
     * @param int $key
     * @param string $expected
     */
    public function testGetTransportValueReturnsCorrectEmail(array $data, $key, $expected)
    {
        $this->emailAddress->method('getAddressesForBean')->with($this->equalTo($this->user))->willReturn($data);
        $result = $this->addressType->getTransportValue($this->user, $key);
        $this->assertEquals($expected, $result);
    }
}
