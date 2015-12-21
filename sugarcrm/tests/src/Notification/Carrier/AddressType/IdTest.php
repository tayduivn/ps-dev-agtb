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

namespace Sugarcrm\SugarcrmTests\Notification\Carrier\AddressType;

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id as AddressTypeId;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id
 */
class IdTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressTypeId */
    protected $addressType = null;

    /** @var \User */
    protected $user = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addressType = new AddressTypeId();
        $this->user = new \User();
        $this->user->id = create_guid();
    }

    /**
     * getOptions should return id of provided user
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id::getOptions
     */
    public function testGetOptionsReturnsValidId()
    {
        $result = $this->addressType->getOptions($this->user);
        $this->assertEquals(array('id' => $this->user->id), $result);
    }

    /**
     * Data provider for testGetTransportValueReturnsCorrectId
     *
     * @see IdTest::testGetTransportValueReturnsCorrectId
     * @return array
     */
    public static function getTransportValueReturnsCorrectIdProvider()
    {
        return array(
            'anyKey1' => array(
                'key' => 0,
            ),
            'anyKey2' => array(
                'key' => 1,
            ),
            'anyKey3' => array(
                'key' => 2,
            ),
            'anyKey4' => array(
                'key' => -1,
            ),
            'anyKey5' => array(
                'key' => 'id',
            ),
        );
    }

    /**
     * getTransportValue should return id
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id::getTransportValue
     * @dataProvider getTransportValueReturnsCorrectIdProvider
     * @param int $key
     */
    public function testGetTransportValueReturnsCorrectId($key)
    {
        $result = $this->addressType->getTransportValue($this->user, $key);
        $this->assertEquals($this->user->id, $result);
    }

    /**
     * isSelectable should return false for Id Address Type
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id::isSelectable
     */
    public function isSelectableReturnsFalse()
    {
        $this->assertFalse($this->addressType->isSelectable());
    }
}
