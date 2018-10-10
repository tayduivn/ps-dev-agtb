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
 * @coversDefaultClass commentlog
 */
class CommentLogTest extends TestCase
{

    /**
     * @covers ::setModule()
     */
    public function testSetModuleSuccess()
    {
        global $beanList;
        $commentlog = $this->createPartialMock('CommentLog', []);
        $beanList['Is Module'] = "Is Module";

        $this->assertTrue($commentlog->setModule('Is Module'));
        $this->assertEquals($commentlog->module, 'Is Module');
    }

    /**
     * @covers ::setModule()
     */
    public function testSetMethodFailure()
    {
        $commentlog = $this->createPartialMock('CommentLog', []);

        $this->assertFalse($commentlog->setModule('Is Not Module'));
        $this->assertEmpty($commentlog->module);
    }

    /**
     * @covers ::setEntry()
     * @param string $Entry The Entry to store in the commentlog message
     * @dataProvider SetEntryProvider
     */
    public function testSetEntry(string $entry)
    {
        $commentlog = $this->createPartialMock('CommentLog', []);

        $commentlog->setEntry($entry);

        $this->assertEquals($commentlog->entry, $entry);
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
