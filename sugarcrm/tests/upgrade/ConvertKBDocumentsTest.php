<?php
require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_ConvertKBDocuments.php';

class ConvertKBDocumentsTest extends UpgradeTestCase
{
    /**
     * @var KBDocument
     */
    protected $document;

    /**
     * @var KBContent
     */
    protected $content;

    /**
     * @var KBDocumentRevision
     */
    protected $revision;

    /**
     * @var KBTag
     */
    protected $tagParent;

    /**
     * @var KBTag
     */
    protected $tagChild;

    /**
     * @var KBTag
     */
    protected $tagRoot;

    /**
     * @var SugarUpgradeConvertKBDocuments
     */
    protected $script;

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');

        $this->document = BeanFactory::getBean('KBDocuments');

        if (!$this->document) {
            $this->markTestSkipped('Mark test skipped. The KBDocuments module is not available.');
        }

        $this->document->name = uniqid();
        // Not a mistake, status_id is a lable.
        $this->document->status_id = $GLOBALS['app_list_strings']['kbsdocument_status_dom']['expired'];
        $this->document->body = 'test body';

        $this->content = BeanFactory::getBean('KBContents');
        $this->content->kbdocument_body = $this->document->body;
        $this->content->save();

        $this->revision = BeanFactory::getBean('KBDocumentRevisions');
        $this->revision->revision = 1;
        $this->revision->latest = true;
        $this->revision->kbcontent_id = $this->content->id;
        $this->revision->save();

        $this->document->kbdocument_revision_id = $this->revision->id;
        $this->document->save();

        $this->tagRoot = BeanFactory::getBean('KBTags');
        $this->tagRoot->tag_name = 'single_root_tag';
        $this->tagRoot->save();

        $this->tagParent = BeanFactory::getBean('KBTags');
        $this->tagParent->tag_name = 'parent_tag';
        $this->tagParent->save();

        $this->tagChild = BeanFactory::getBean('KBTags');
        $this->tagChild->tag_name = 'child_tag';
        $this->tagChild->parent_tag_id = $this->tagParent->id;
        $this->tagChild->save();

        $this->upgrader->setVersions(6.7, 'ult', 7.5, 'ult');

        $this->script = $this->getMockBuilder('SugarUpgradeConvertKBDocuments')
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getOldDocuments', 'getOldTags'))
            ->getMock();

        $this->script->expects($this->any())->method('getOldDocuments')
            ->will($this->returnValue(array(array('id' => $this->document->id))));

        $this->script->expects($this->any())->method('getOldTags')
            ->will($this->returnValue(array()));
    }

    public function tearDown()
    {
        $this->document->mark_deleted($this->document->id);
        $this->revision->mark_deleted($this->revision->id);
        $this->content->mark_deleted($this->content->id);
        $this->tagRoot->mark_deleted($this->tagRoot->id);
        $this->tagParent->mark_deleted($this->tagParent->id);
        $this->tagChild->mark_deleted($this->tagChild->id);

        $kbscontent = $this->getKBSContentBeanByName($this->document->name);
        if ($kbscontent) {
            $kbscontent->mark_deleted($kbscontent->id);
        }

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Converted documents are active revision.
     */
    public function testAllNewDocumentsAreActive()
    {
        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);
        $this->assertEquals(1, $newDocument->active_rev);
    }

    /**
     * Check related case.
     */
    public function testRelatedCases()
    {
        $case = SugarTestCaseUtilities::createCase();
        $expectedCaseId = $case->id;

        $this->document->load_relationship('cases');
        $this->document->cases->add($case);

        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);

        SugarTestCaseUtilities::removeAllCreatedCases();

        $this->assertEquals($expectedCaseId, $newDocument->kbscase_id);
    }

    /**
     * Approver should be converted.
     */
    public function testApproverConversion()
    {
        $this->document->kbdoc_approver_id = 1;
        $this->document->save();

        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);
        $this->assertEquals($this->document->kbdoc_approver_id, $newDocument->kbsapprover_id);
    }

    /**
     * Test that attachment's converted to a Note linked to a new document.
     */
    public function testAttachments()
    {
        // Create an attachment.
        $docRevision = BeanFactory::getBean('DocumentRevisions');
        $docRevision->save();

        $file = UploadStream::path('upload://') . $docRevision->id;
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($file);
        file_put_contents($file, 'test file content');

        $docRevision->document_id = $this->document->id;
        $docRevision->filename = basename($file);
        $docRevision->file_mime_type = 'text/plain';
        $docRevision->file_ext = '';
        $docRevision->save();

        $KBRevisionAtts = BeanFactory::getBean('KBDocumentRevisions');
        $KBRevisionAtts->revision = $this->document->revision;
        $KBRevisionAtts->kbdocument_id = $this->document->id;
        $KBRevisionAtts->document_revision_id = $docRevision->id;
        $KBRevisionAtts->save();

        $this->script->run();

        $KBRevisionAtts->mark_deleted($KBRevisionAtts->id);
        $docRevision->mark_deleted($docRevision->id);

        $newDocument = $this->getKBSContentBeanByName($this->document->name);

        $newDocument->load_relationship('attachments');
        $notes = $newDocument->attachments->getBeans();

        $this->assertEquals(1, count($notes));

        // Get the first note.
        $note = reset($newDocument->attachments->getBeans());
        $note->mark_deleted($note->id);

        $this->assertEquals($docRevision->id, $note->filename);
    }

    /**
     * Check default status is draft.
     */
    public function testDefaultStatusIsDraft()
    {
        $this->document->status_id = '';
        $this->document->save();

        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);
        $this->assertEquals('draft', $newDocument->status);
    }

    /**
     * Check converted document has identical body and status.
     */
    public function testConvertKBDocuments()
    {
        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);
        $this->assertNotNull($newDocument);
        $this->assertEquals($this->document->body, $newDocument->kbdocument_body);
        $this->assertEquals(
            array_search($this->document->status_id, $GLOBALS['app_list_strings']['kbsdocument_status_dom']),
            $newDocument->status
        );
    }

    /**
     * Convert tags to KBS tags.
     */
    public function testConvertTags()
    {
//        FIXME: temp disabled - needs to be retested when doing MT-909.
        $this->markTestSkipped('Awaiting MT-909');

        $this->script = $this->getMockBuilder('SugarUpgradeConvertKBDocuments')
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getOldDocuments', 'getOldTags'))
            ->getMock();

        $this->script->expects($this->any())->method('getOldDocuments')
            ->will($this->returnValue(array(array('id' => $this->document->id))));

        $this->script->expects($this->once())->method('getOldTags')
            ->will($this->returnValue(
                    array(
                        array('kbtag_id' => $this->tagRoot->id),
                        array('kbtag_id' => $this->tagChild->id),
                    )
                )
            );

        $expectedTagNames = array(
            $this->tagChild->tag_name,
            $this->tagRoot->tag_name,
        );

        $this->script->run();

        $newDocument = $this->getKBSContentBeanByName($this->document->name);
        $newDocument->load_relationship('tags_link');
        $newTags = $newDocument->tags_link->getBeans();

        $actualTagNames = array_map(
            function ($value) {
                return $value->name;
            },
            $newTags
        );

        foreach($newTags as $tag) {
            $tag->mark_deleted($tag->id);
        }

        $this->assertEquals($expectedTagNames, array_values($actualTagNames), '', 0, 10, true);
    }

    /**
     * Returns first KBSContent record by name.
     *
     * @param string $name Name field value.
     * @return null|SugarBean
     */
    protected function getKBSContentBeanByName($name)
    {
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('KBSContents'));
        $sq->where()->equals('name', $name);
        $result = $sq->execute();

        if (!empty($result)) {
            return BeanFactory::getBean('KBSContents', $result[0]['id']);
        }
    }
}
