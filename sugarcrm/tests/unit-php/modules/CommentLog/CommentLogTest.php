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

//    /**
//     * @covers ::createNotifications
//     */
//    public function testCreateNotifications()
//    {
//        $commentlog = $this->createPartialMock('CommentLog', [
//            'load_relationship', 'getNewBean', 'getSugarConfigValue', 'getModStrings', 'getAppListStrings',
//        ]);
//        $notificationMock = $this->createPartialMock('Notifications', ['save']);
//        $commentlog->method('getNewBean')->willReturn($notificationMock);
//        $commentlog->method('load_relationship')->willReturn(true);
//        $commentlog->method('getSugarConfigValue')->willReturn('en_us');
//        $commentlog->method('getModStrings')->willReturn(
//            ['LBL_YOU_HAVE_BEEN_MENTIONED' => 'You have been mentioned']
//        );
//        $commentlog->method('getAppListStrings')->willReturn(
//            ['moduleListSingular' => ['Cases'=> 'Case']]
//        );
//        $link2Mock = $this->createPartialMock('Link2', ['getRelatedModuleName']);
//        $link2Mock->method('getRelatedModuleName')->willReturn('Cases');
//        $new_rel_relname = 'cases';
//        $commentlog->new_rel_relname = $new_rel_relname;
//        $commentlog->$new_rel_relname = $link2Mock;
//        $commentlog->entry = '@[Users:id1] and @[Users:id1] and @[Contacts:id2]';
//        $notificationMock->expects($this->once())
//            ->method('save');
//        $commentlog->createNotifications();
//        $this->assertEquals('Case: You have been mentioned', $notificationMock->name);
//        $this->assertEquals('LBL_YOU_HAVE_BEEN_MENTIONED_BY', $notificationMock->description);
//        $this->assertEquals('information', $notificationMock->severity);
//    }
}
