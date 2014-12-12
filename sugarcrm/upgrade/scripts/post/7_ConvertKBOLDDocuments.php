<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Converts KBOLDDocuments to KBContents.
 */
class SugarUpgradeConvertKBOLDDocuments extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.5';

    /**
     * @var array Where key is a converted KB tag ID and a value is KBS category ID.
     */
    protected $convertedTagsTopics = array();

    public function run()
    {
        if (!version_compare($this->from_version, '7.5', '<=')) {
            return;
        }
        $documents = $this->getOldDocuments();

        // Relationships for KBContents are not loaded yet.
        SugarRelationshipFactory::rebuildCache();

        foreach ($documents as $row) {
            $this->log("Convert the KBOLDDocument {$row['id']} to a KBContent.");
            /** @var $KBBean KBOLDDocument */
            $KBOLDDocument = BeanFactory::getBean('KBOLDDocuments', $row['id']);

            /** @var $KBBean KBContent */
            $KBContent = BeanFactory::getBean('KBContents');

            $data = $KBOLDDocument->toArray();
            unset($data['id']);
            $data['kbolddocument_body'] = $KBOLDDocument->body;
            $data['kbsapprover_id'] = $KBOLDDocument->kbdoc_approver_id;

            $app_list_strings = return_app_list_strings_language('en_us');
            // Yes, the status_id is a lable.
            $statusKey = array_search($KBOLDDocument->status_id, $app_list_strings['kbdocument_status_dom']);
            $data['status'] = ($statusKey !== false) ? $statusKey : 'draft';

            $KBOLDDocument->load_relationship('cases');
            $firstCaseId = key($KBOLDDocument->cases->getBeans());

            if ($firstCaseId) {
                $data['kbscase_id'] = $firstCaseId;
            }

            $tagSet = $this->getOldTags($KBOLDDocument->id);
            foreach ($tagSet as $tag) {
                $this->log("Convert the KBOLDTag {$tag['kboldtag_id']} to a topic\\category.");
                $tag = BeanFactory::getBean('KBOLDTags', $tag['kboldtag_id']);
                $this->convertTagsToTopicsRecursive($tag);
            }

            $KBContent->populateFromRow($data);
            $KBContent->save();

            $KBContent->load_relationship('tags');
            if (!empty($KBContent->tags)) {
                foreach ($tagSet as $tag) {
                    $this->log("Convert the KBOLDTag {$tag['kboldtag_id']} to a tag.");
                    $tag = BeanFactory::getBean('KBOLDTags', $tag['kboldtag_id']);

                    // Get last child element from each entry, i.e. the entries "p1->c1", "p2->p3->c2", "p4"
                    // are converted to the set "c1, c2, p4".
                    $newTag = BeanFactory::getBean('Tags');
                    $newTag->name = $tag->tag_name;
                    $newTag->save();
                    $KBContent->tags->add($newTag);
                }
            } else {
                $this->log("Can't load tags.");
            }
            $KBContent->load_relationship('attachments');

            // Converts attached files to Notes.
            $attachments = $KBOLDDocument->get_kbdoc_attachments_for_newemail($KBOLDDocument->id);
            foreach ($attachments['attachments'] as $attachment) {
                $this->log("Convert attachment {$attachment['id']}.");
                $note = BeanFactory::getBean('Notes', $attachment['id']);
                $KBContent->attachments->add($note);
            }
        }
    }

    /**
     * Return legacy KBOLDDocuments.
     *
     * @return array Array of arrays.
     */
    protected function getOldDocuments()
    {
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('KBOLDDocuments'));
        return $sq->execute();
    }

    /**
     * Return tags by document ID.
     * Written because the KBOLDDocument's functions get_tags() and get_kbdoc_tags() return only one tag.
     *
     * @param $docId KBOLDDocument ID.
     * @return array
     */
    protected function getOldTags($docId)
    {
        $sq = new SugarQuery();
        $sq->select(array('tag.kboldtag_id'));
        $sq->joinTable(
            'kbolddocuments_kboldtags',
            array(
                'joinType' => 'INNER',
                'alias' => 'tag',
                'linkingTable' => true,
            )
        )->on()->equalsField(
            'kbolddocuments.id',
            'tag.kbolddocument_id'
        )->equals('tag.deleted', '0');
        $sq->from(BeanFactory::getBean('KBOLDDocuments'));
        $sq->where()->equals('kbolddocuments.id', $docId);

        return $sq->execute('array');
    }

    /**
     * Recursively converts old tags to topics\categories.
     *
     * @param KBOLDTag $tag
     * @return string Associated topic ID.
     */
    protected function convertTagsToTopicsRecursive(KBOLDTag $tag)
    {
        if (isset($this->convertedTagsTopics[$tag->id])) {
            return $this->convertedTagsTopics[$tag->id];
        }
        $topic = BeanFactory::newBean('Categories');
        $topic->name = $tag->tag_name;

        if ($tag->parent_tag_id) {
            $parentTag = BeanFactory::getBean('KBOLDTags', $tag->parent_tag_id);
            $parentTopicId = $this->convertTagsToTopicsRecursive($parentTag);
            $parentTopic = BeanFactory::getBean('Categories', $parentTopicId, array('use_cache' => false));
            $parentTopic->append($topic);
        } else {
            $KBContent = BeanFactory::getBean('KBContents');
            $rootTopic = BeanFactory::getBean(
                'Categories',
                $KBContent->getCategoryRoot(),
                array('use_cache' => false)
            );
            $rootTopic->append($topic);
        }

        $topicId = $topic->save();
        $this->convertedTagsTopics[$tag->id] = $topicId;

        return $topicId;
    }
}
