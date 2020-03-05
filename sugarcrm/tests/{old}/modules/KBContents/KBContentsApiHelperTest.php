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

class KBContentsApiHelperTest extends TestCase
{
    /**
     * @var KBContents
     */
    protected $bean;

    /**
     * @var array
     */
    protected $notes = [];

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    public function tearDown()
    {
        // clean up all notes
        if (!empty($this->notes)) {
            $qb = \DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->delete('notes')->where(
                $qb->expr()->in(
                    'id',
                    $qb->createPositionalParameter((array) $this->notes, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            )->execute();
            foreach ($this->notes as $noteId) {
                if (file_exists('upload/' . $noteId)) {
                    unlink('upload/' . $noteId);
                }
            }
        }

        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi() 
    {
        $helper = new KBContentsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $lang = $this->bean->getPrimaryLanguage();

        $this->assertEquals($data['name'], $this->bean->name);
        $this->assertEquals($data['language'], $lang['key']);
        $this->assertIsArray($data['attachment_list']);
    }

    /**
     * @covers \KBContentsApiHelper::formatForApi
     * @covers \Notes::hasAttachment
     * @covers \Notes::getAttachment
     */
    public function testFormatForApiKbNotesAttachments()
    {
        $restMock = SugarTestRestUtilities::getRestServiceMock();
        $kbHelper = new \KBContentsApiHelper($restMock);

        // test note without attachment
        $n = BeanFactory::newBean('Notes');
        $n->name = 'KBContentsApiHelperTest';
        $n->save();
        $noteId = $n->id;
        $this->notes[] = $noteId;

        // relate note to kb
        $this->bean->load_relationship('attachments');
        $this->bean->attachments->add($noteId);

        $resp = $kbHelper->formatForApi($this->bean);
        $this->assertNotEmpty($resp);
        $this->assertIsArray($resp);
        $this->assertEmpty($resp['attachment_list']);

        // test note with attachment
        $n = BeanFactory::newBean('Notes');
        $n->name = 'KBContentsApiHelperTest';
        $n->filename = 'KBContentsApiHelperTest.txt';
        $n->save();
        $noteId = $n->id;
        $this->notes[] = $noteId;

        // add the actual attachment file
        file_put_contents('upload/' . $noteId, 'KBContentsApiHelperTest');

        // relate note to kb
        $this->bean->load_relationship('attachments');
        $this->bean->attachments->add($noteId);

        $resp = $kbHelper->formatForApi($this->bean);
        $this->assertNotEmpty($resp);
        $this->assertIsArray($resp);
        $this->assertIsArray($resp['attachment_list']);
        $this->assertCount(1, $resp['attachment_list']);
        $this->assertEquals($resp['attachment_list'][0]['id'], $noteId);
        $this->assertEquals($resp['attachment_list'][0]['filename'], $n->filename);
        $this->assertEquals($resp['attachment_list'][0]['name'], $n->filename);
        $this->assertEquals($resp['attachment_list'][0]['isImage'], false);
    }
}
