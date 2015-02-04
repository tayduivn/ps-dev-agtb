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

namespace Sugarcrm\SugarcrmTest\Logger;

use Sugarcrm\Sugarcrm\Logger\LoggerTransition;
use Psr\Log\LogLevel;

/**
 * Test for the genetic Logger.
 */
class LoggerTransitionTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test translating PSR level to sugar level.
     * @param $psrLevel
     * @param $sugarLevel
     * @dataProvider providerGetSugarLevel
     */
    public function testGetSugarLevel($psrLevel, $sugarLevel)
    {
        $logger = new LoggerTransition(\LoggerManager::getLogger());
        $level = \SugarTestReflection::callProtectedMethod($logger, 'getSugarLevel', array($psrLevel));
        $this->assertEquals($sugarLevel, $level);
    }

    /**
     * Data provider to test getSugarLevel().
     * @return array
     */
    public function providerGetSugarLevel()
    {
        return array(
            array(LogLevel::EMERGENCY, "fatal"),
            array(LogLevel::ALERT, "fatal"),
            array(LogLevel::CRITICAL, "fatal"),
            array(LogLevel::ERROR, "error"),
            array(LogLevel::WARNING, "warn"),
            array(LogLevel::NOTICE, "info"),
            array(LogLevel::INFO, "info"),
            array(LogLevel::DEBUG, "debug"),
        );
    }
}
