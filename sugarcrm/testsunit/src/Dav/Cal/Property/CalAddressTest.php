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
namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Property;

use Sugarcrm\Sugarcrm\Dav\Cal\Property\CalAddress;
use Sabre\VObject\Component;

/**
 * Class CalAddressTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Property
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Property\CalAddress
 */
class CalAddressTest extends \PHPUnit_Framework_TestCase
{
    public function getValuesProvider()
    {
        return array(
            array('mailto:a@b.com', 'mailto:a@b.com'),
            array('MAILTO:A@b.com', 'mailto:a@b.com'),
            array(null, null),
        );
    }

    /**
     * @param string $value
     * @param string $expectedValue
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Property\CalAddress::getValue
     * @dataProvider getValuesProvider
     */
    public function testGetValue($value, $expectedValue)
    {
        $vObj = new Component\VCalendar();
        $property = new CalAddress($vObj, 'ATTENDEE', $value);
        $this->assertEquals($expectedValue, $property->getValue());
    }
}
