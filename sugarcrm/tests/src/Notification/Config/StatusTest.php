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

/**
 * Testing functionality Config/Status
 *
 * Class StatusTest
 * @package Notification
 */
class StatusTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_CONFIG = 'Sugarcrm\\Sugarcrm\\Notification\\Config\\Status';

    public function statusVariants()
    {
        return array(
            array(true, true),
            array(true, 1),
            array(true, 'true'),
            array(false, false),
            array(false, 0),
            array(false, null)
        );
    }

    /**
     * @dataProvider statusVariants
     * @param $expect
     * @param $statusSet
     */
    public function testGetCarrierStatus($expect, $statusSet)
    {
        $carrierName = 'carrierModule2';
        $config = \BeanFactory::getBean('Administration');
        $config->saveSetting(Status::CONFIG_CATEGORY, $carrierName, $statusSet);

        $carrierModules = array('carrierModule1', 'carrierModule2');

        $status = $this->getMock(self::NS_CONFIG, array('getCarriers'));
        $status->expects($this->once())->method('getCarriers')->willReturn($carrierModules);

        $actual = $status->getCarrierStatus($carrierName);
        $this->assertEquals($expect, $actual);
    }

    /**
     * @dataProvider statusVariants
     * @param $expect
     * @param $statusSet
     */
    public function testSetCarrierStatus($expect, $statusSet)
    {
        $carrierName = 'carrierModule2';

        $carrierModules = array('carrierModule1', 'carrierModule2');

        $status = $this->getMock(self::NS_CONFIG, array('getCarriers'));
        $status->expects($this->once())->method('getCarriers')->willReturn($carrierModules);

        $setRes = $status->setCarrierStatus($carrierName, $statusSet);

        $config = \BeanFactory::getBean('Administration');
        $config = $config->getSettings(Status::CONFIG_CATEGORY);
        $key = Status::CONFIG_CATEGORY . '_' . $carrierName;

        $this->assertEquals($expect, $setRes);
        $this->assertEquals($statusSet, $config->settings[$key]);
    }
}
