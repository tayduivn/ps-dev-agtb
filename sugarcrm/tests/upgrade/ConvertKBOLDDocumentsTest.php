<?php
require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_ConvertKBOLDDocuments.php';

class ConvertKBOLDDocumentsTest extends UpgradeTestCase
{
    /**
     * @var KBOLDDocument
     */
    protected $document;

    /**
     * @var KBOLDContent
     */
    protected $content;

    /**
     * @var KBOLDDocumentRevision
     */
    protected $revision;

    /**
     * @var KBOLDTag
     */
    protected $tagRoot1;

    /**
     * @var KBOLDTag
     */
    protected $tagRoot2;

    /**
     * @var KBOLDTag
     */
    protected $tagChild1Level1;

    /**
     * @var KBOLDTag
     */
    protected $tagChild2Level1;

    /**
     * @var KBOLDTag
     */
    protected $tagChild1Level2;

    /**
     * @var SugarUpgradeConvertKBOLDDocuments
     */
    protected $script;

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');

        $this->document = BeanFactory::getBean('KBOLDDocuments');

        if (!$this->document) {
            $this->markTestSkipped('Mark test skipped. The KBOLDDocuments module is not available.');
        }

        $this->document->name = uniqid();
        // Not a mistake, status_id is a lable.
        $this->document->status_id = $GLOBALS['app_list_strings']['kbdocument_status_dom']['expired'];
        $this->document->body = 'test body';

        $this->content = BeanFactory::getBean('KBOLDContents');
        $this->content->kbolddocument_body = $this->document->body;
        $this->content->save();

        $this->revision = BeanFactory::getBean('KBOLDDocumentRevisions');
        $this->revision->revision = 1;
        $this->revision->latest = true;
        $this->revision->kboldcontent_id = $this->content->id;
        $this->revision->save();

        $this->document->kbolddocument_revision_id = $this->revision->id;
        $this->document->save();

        $this->tagRoot1 = BeanFactory::getBean('KBOLDTags');
        $this->tagRoot1->tag_name = 'root_tag1';
        $this->tagRoot1->save();

        $this->tagRoot2 = BeanFactory::getBean('KBOLDTags');
        $this->tagRoot2->tag_name = 'root_tag2';
        $this->tagRoot2->save();

        $this->tagChild1Level1 = BeanFactory::getBean('KBOLDTags');
        $this->tagChild1Level1->tag_name = 'child_tag1_level1';
        $this->tagChild1Level1->parent_tag_id = $this->tagRoot1->id;
        $this->tagChild1Level1->save();

        $this->tagChild2Level1 = BeanFactory::getBean('KBOLDTags');
        $this->tagChild2Level1->tag_name = 'child_tag2_level1';
        $this->tagChild2Level1->parent_tag_id = $this->tagRoot1->id;
        $this->tagChild2Level1->save();

        $this->tagChild1Level2 = BeanFactory::getBean('KBOLDTags');
        $this->tagChild1Level2->tag_name = 'child_tag1_level2';
        $this->tagChild1Level2->parent_tag_id = $this->tagChild1Level1->id;
        $this->tagChild1Level2->save();

        // New root for tests.
        $KBContent = BeanFactory::getBean('KBContents');
        $KBContent->setupCategoryRoot();

        $this->upgrader->setVersions(6.7, 'ult', 7.5, 'ult');

        $this->script = $this->getMockBuilder('SugarUpgradeConvertKBOLDDocuments')
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
        $this->tagRoot1->mark_deleted($this->tagRoot1->id);
        $this->tagRoot2->mark_deleted($this->tagRoot2->id);
        $this->tagChild1Level1->mark_deleted($this->tagChild1Level1->id);
        $this->tagChild2Level1->mark_deleted($this->tagChild2Level1->id);
        $this->tagChild1Level2->mark_deleted($this->tagChild1Level2->id);

        $kbcontent = $this->getKBContentBeanByName($this->document->name);
        if ($kbcontent) {
            if ($kbcontent->load_relationship('tags')) {
                $tags = $kbcontent->tags->getBeans();
                foreach ($tags as $tag) {
                    $tag->mark_deleted($tag->id);
                }
            }
            $kbcontent->mark_deleted($kbcontent->id);
        }
        $names = array(
            $this->tagRoot1->tag_name,
            $this->tagRoot2->tag_name,
            $this->tagChild1Level1->tag_name,
            $this->tagChild2Level1->tag_name,
            $this->tagChild1Level2->tag_name,
        );
        $category = BeanFactory::newBean('Categories');
        $GLOBALS['db']->query("DELETE FROM {$category->table_name} WHERE name IN ('" . implode("', '", $names) . "')");

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Converted documents are active revision.
     */
    public function testAllNewDocumentsAreActive()
    {
        $this->script->run();

        $newDocument = $this->getKBContentBeanByName($this->document->name);
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

        $newDocument = $this->getKBContentBeanByName($this->document->name);

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

        $newDocument = $this->getKBContentBeanByName($this->document->name);
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

        $KBRevisionAtts = BeanFactory::getBean('KBOLDDocumentRevisions');
        $KBRevisionAtts->revision = $this->document->revision;
        $KBRevisionAtts->kbolddocument_id = $this->document->id;
        $KBRevisionAtts->document_revision_id = $docRevision->id;
        $KBRevisionAtts->save();

        $this->script->run();

        $KBRevisionAtts->mark_deleted($KBRevisionAtts->id);
        $docRevision->mark_deleted($docRevision->id);

        $newDocument = $this->getKBContentBeanByName($this->document->name);

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

        $newDocument = $this->getKBContentBeanByName($this->document->name);
        $this->assertEquals('draft', $newDocument->status);
    }

    /**
     * Check converted document has identical body and status.
     */
    public function testConvertKBOLDDocuments()
    {
        $this->script->run();

        $newDocument = $this->getKBContentBeanByName($this->document->name);
        $this->assertNotNull($newDocument);
        $this->assertEquals($this->document->body, $newDocument->kbdocument_body);
        $this->assertEquals(
            array_search($this->document->status_id, $GLOBALS['app_list_strings']['kbdocument_status_dom']),
            $newDocument->status
        );
    }

    /**
     * Convert tags to KBS tags.
     * Use every last child from each entry, the set "p1->c1", "p2->p3->c2", "p4"
     * should be converted to "c1, c2, p4".
     */
    public function testConvertTags()
    {
        $this->markTestSkipped('Waiting for MT-909');
        $this->script = $this->getMockBuilder('SugarUpgradeConvertKBOLDDocuments')
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getOldDocuments', 'getOldTags'))
            ->getMock();

        $this->script->expects($this->any())->method('getOldDocuments')
            ->will($this->returnValue(array(array('id' => $this->document->id))));

        /*
         * tagRoot1->tagChild1Level1->tagChild1Level2
         * tagRoot1->tagChild2Level1
         * tagRoot2
         */
        $this->script->expects($this->once())->method('getOldTags')
            ->will(
                $this->returnValue(
                    array(
                        array('kboldtag_id' => $this->tagChild1Level2->id),
                        array('kboldtag_id' => $this->tagChild2Level1->id),
                        array('kboldtag_id' => $this->tagRoot2->id),
                    )
                )
            );

        /*
         * The tags "tagRoot1" and "tagChild1Level1" should be skipped.
         */
        $expectedTagNames = array(
            $this->tagChild1Level2->tag_name,
            $this->tagChild2Level1->tag_name,
            $this->tagRoot2->tag_name,
        );

        $this->script->run();

        $newDocument = $this->getKBContentBeanByName($this->document->name);
        $newDocument->load_relationship('tags');
        $newTags = $newDocument->tags->getBeans();

        $actualTagNames = array_map(
            function ($value) {
                return $value->name;
            },
            $newTags
        );

        $this->assertEquals($expectedTagNames, array_values($actualTagNames), '', 0, 10, true);
    }

    /**
     * Convert KBOLDTag tree to Categories.
     */
    public function testConvertTagsToCategories()
    {
        $this->markTestSkipped('Waiting for MT-909');
        $this->script = $this->getMockBuilder('SugarUpgradeConvertKBOLDDocuments')
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getOldDocuments', 'getOldTags'))
            ->getMock();

        $this->script->expects($this->any())->method('getOldDocuments')
            ->will($this->returnValue(array(array('id' => $this->document->id))));

        /*
         * root_tag1->child_tag1_level1->child_tag1_level2
         * root_tag1->child_tag2_level1
         * root_tag2
         */
        $this->script->expects($this->once())->method('getOldTags')
            ->will(
                $this->returnValue(
                    array(
                        array('kboldtag_id' => $this->tagChild1Level2->id),
                        array('kboldtag_id' => $this->tagChild2Level1->id),
                        array('kboldtag_id' => $this->tagRoot2->id),
                    )
                )
            );

        $this->script->run();

        $rootCat = BeanFactory::getBean(
            'Categories',
            BeanFactory::getBean('KBContents')->getCategoryRoot(),
            array('use_cache' => false)
        );
        $catTree = $rootCat->getTree();

        $this->assertEquals('root_tag1', $catTree[0]['name']);
        $this->assertEquals('child_tag1_level1', $catTree[0]['children'][0]['name']);
        $this->assertEquals('child_tag1_level2', $catTree[0]['children'][0]['children'][0]['name']);
        $this->assertEquals('child_tag2_level1', $catTree[0]['children'][1]['name']);
        $this->assertEquals('root_tag2', $catTree[1]['name']);
    }

    /**
     * Returns first KBContent record by name.
     *
     * @param string $name Name field value.
     * @return null|SugarBean
     */
    protected function getKBContentBeanByName($name)
    {
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('KBContents'));
        $sq->where()->equals('name', $name);
        $result = $sq->execute();

        if (!empty($result)) {
            return BeanFactory::getBean('KBContents', $result[0]['id']);
        }
    }
}
