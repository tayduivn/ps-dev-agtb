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

namespace Sugarcrm\SugarcrmTestsUnit\Notification\Config;

require_once 'tests/SugarTestReflection.php';

/**
 * Testing functionality Config/Status
 *
 * Class StatusTest
 * @package Notification
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\Config\Status
 */

class StatusTest extends \PHPUnit_Framework_TestCase
{
    const NS_CONFIG = 'Sugarcrm\\Sugarcrm\\Notification\\Config\\Status';

    /**
     * @expectedException \LogicException
     * @covers ::verifyModule
     */
    public function testVerifyIncorrectModule()
    {
        $carrierModules = array('carrierModule1', 'carrierModule2');

        $status = $this->getMock(self::NS_CONFIG, array('getCarriers'));
        $status->expects($this->once())->method('getCarriers')->willReturn($carrierModules);

        \SugarTestReflection::callProtectedMethod($status, 'verifyModule', array('InvalidModule'));
    }

    /**
     * @covers ::verifyModule
     */
    public function testVerifyCorrectModule()
    {
        $carrierModules = array('carrierModule1', 'carrierModule2', 'carrierModule3');

        $status = $this->getMock(self::NS_CONFIG, array('getCarriers'));
        $status->expects($this->once())->method('getCarriers')->willReturn($carrierModules);

        \SugarTestReflection::callProtectedMethod($status, 'verifyModule', array($carrierModules[1]));
    }
}
