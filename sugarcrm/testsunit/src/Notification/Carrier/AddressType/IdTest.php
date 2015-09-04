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

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id
 */
class IdTest extends \PHPUnit_Framework_TestCase
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

        $user->id = 'some-id-val';

        $addressTypeId = new Id();

        $expects = array('id');

        $this->assertEquals($expects, $addressTypeId->getOptions($user));
    }

    public function transportValueVariants()
    {
        return array(
            array('id'),
            array('not-exists-val'),
        );
    }

    /**
     * @dataProvider transportValueVariants
     * @covers ::getOptions
     */
    public function testGetTransportValue($index)
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $user->id = 'some-id-val';

        $addressTypeId = new Id();

        $this->assertEquals($user->id, $addressTypeId->getTransportValue($user, $index));
    }
}
