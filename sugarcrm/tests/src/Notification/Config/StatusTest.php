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

namespace Sugarcrm\SugarcrmTests\Notification\Config;

use Sugarcrm\Sugarcrm\Notification\Config\Status;

class StatusTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Data provider witch return different status values
     *
     * @return array
     */
    public function statusTypes()
    {
        return array(
            array(0,        false),
            array(null,     false),
            array(false,    false),
            array(true,     true),
            array(1,        true),
        );
    }

    /**
     * Checking saving carrier status
     *
     * @dataProvider statusTypes
     * @param $val
     * @param $valExpected
     */
    public function testGetCarrierStatus($val, $valExpected)
    {
        $name = 'CarrierEmail';

        $status = Status::getInstance();
        $status->setCarrierStatus($name, $val);
        $this->assertEquals($valExpected, $status->getCarrierStatus($name));
    }

    /**
     * Behavior carrier not exists or not saved yet
     *
     */
    public function testNotSavedCarrier()
    {
        $name = 'NotExists'.time();

        $status = Status::getInstance();
        $this->assertFalse($status->getCarrierStatus($name));
    }

}
