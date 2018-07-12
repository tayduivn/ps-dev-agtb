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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Worklog
 */
class WorklogTest extends TestCase
{

    /**
     * @covers ::setModule()
     */
    public function testSetModuleSuccess()
    {
        global $beanList;
        $worklog = $this->createPartialMock('Worklog', []);
        $beanList['Is Module'] = "Is Module";

        $this->assertTrue($worklog->setModule('Is Module'));
        $this->assertEquals($worklog->module, 'Is Module');
    }

    /**
     * @covers ::setModule()
     */
    public function testSetMethodFailure()
    {
        $worklog = $this->createPartialMock('Worklog', []);

        $this->assertFalse($worklog->setModule('Is Not Module'));
        $this->assertEmpty($worklog->module);
    }

    /**
     * @covers ::setEntry()
     * @param string $Entry The Entry to store in the worklog message
     * @dataProvider SetEntryProvider
     */
    public function testSetEntry(string $entry)
    {
        $worklog = $this->createPartialMock('Worklog', []);

        $worklog->setEntry($entry);

        $this->assertEquals($worklog->entry, $entry);
    }

    public function SetEntryProvider()
    {
        return array(
            array("Ur the god of thunder, not the god of hammer."),
            array("     "),
            array(''),
            array('<scr' . 'ipt>console.log("Vicious matters");</scr' . 'ipt>'),
            array('<p>I have a big head</p>'),
        );
    }
}
