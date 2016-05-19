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

namespace Sugarcrm\SugarcrmTests\upgrade\scripts\post;

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/9_RepairReminders.php';

/**
 * @covers SugarUpgradeRepairReminders
 */
class SugarUpgradeRepairRemindersTest extends \UpgradeTestCase
{
    /** @var \SugarUpgradeRepairReminders */
    protected $reminderUpgrade;

    /** @var \Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet|\PHPUnit_Framework_MockObject_MockObject */
    protected $runner;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->runner = $this->getMockBuilder('Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet')
            ->disableOriginalConstructor()
            ->getMock();

        $this->reminderUpgrade = new \SugarUpgradeRepairReminders($this->upgrader, $this->runner);
    }

    /**
     * Data provider for testRun.
     *
     * @see SugarUpgradeRepairRemindersTest::testRun
     * @return array
     */
    public static function runProvider()
    {
        return array(
            'fromVersion7.8.0.0RC2RepairRemindersNotRun' => array('fromVersion' => '7.8.0.0RC2', 'isRun' => false),
            'fromVersion7.8.0.0RC1RepairRemindersRun' => array('fromVersion' => '7.8.0.0RC1', 'isRun' => true),
            'fromVersion7.8.0.0RC3RepairRemindersNotRun' => array('fromVersion' => '7.8.0.0RC3', 'isRun' => false),
            'fromVersion7.8.0.0RepairRemindersNotRun' => array('fromVersion' => '7.8.0.0', 'isRun' => false),
            'fromVersion7.7.0.0RepairRemindersRun' => array('fromVersion' => '7.7.0.0', 'isRun' => true),
        );
    }

    /**
     * Testing running Repair runner for different versions.
     *
     * @covers       SugarUpgradeRepairReminders::run
     * @dataProvider runProvider
     * @param string $fromVersion
     * @param boolean $isRun
     */
    public function testRun($fromVersion, $isRun)
    {
        if ($isRun) {
            $this->runner->expects($this->once())->method('run');
        } else {
            $this->runner->expects($this->never())->method('run');
        }

        $this->reminderUpgrade->from_version = $fromVersion;
        $this->reminderUpgrade->run();
    }
}
