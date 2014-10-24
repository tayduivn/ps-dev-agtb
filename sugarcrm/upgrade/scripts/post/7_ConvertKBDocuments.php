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
 * Converts KBDocuments to KBSContents.
 */
class SugarUpgradeConvertKBDocuments extends UpgradeScript
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

        // Relationships for KBSContents are not loaded yet.
        SugarRelationshipFactory::rebuildCache();

        foreach ($documents as $row) {
            $this->log("Convert the KBDocument {$row['id']} to a KBSContent.");
            /** @var $KBBean KBDocument */
            $KBDocument = BeanFactory::getBean('KBDocuments', $row['id']);

            /** @var $KBBean KBSContent */
            $KBSContent = BeanFactory::getBean('KBSContents');

            $data = $KBDocument->toArray();
            unset($data['id']);
            $data['kbdocument_body'] = $KBDocument->body;
            $data['kbsapprover_id'] = $KBDocument->kbdoc_approver_id;

            $app_list_strings = return_app_list_strings_language('en_us');
            // Yes, the status_id is a lable.
            $statusKey = array_search($KBDocument->status_id, $app_list_strings['kbsdocument_status_dom']);
            $data['status'] = ($statusKey !== false) ? $statusKey : 'draft';

            $KBDocument->load_relationship('cases');
            $firstCaseId = key($KBDocument->cases->getBeans());

            if ($firstCaseId) {
                $data['kbscase_id'] = $firstCaseId;
            }

            $tagSet = $this->getOldTags($KBDocument->id);
            foreach ($tagSet as $tag) {
                $this->log("Convert the KBTag {$tag['kbtag_id']} to a topic\\category.");
                $tag = BeanFactory::getBean('KBTags', $tag['kbtag_id']);
                $this->convertTagsToTopicsRecursive($tag);
            }

            $KBSContent->populateFromRow($data);
            $KBSContent->save();

            $KBSContent->load_relationship('tags_link');
            foreach ($tagSet as $tag) {
                $this->log("Convert the KBTag {$tag['kbtag_id']} to a tag.");
                $tag = BeanFactory::getBean('KBTags', $tag['kbtag_id']);

                // Get last child element from each entry, i.e. the entries "p1->c1", "p2->p3->c2", "p4"
                // are converted to the set "c1, c2, p4".
                $newTag = BeanFactory::getBean('Tags');
                $newTag->name = $tag->tag_name;
                $newTag->save();
                $KBSContent->tags_link->add($newTag);
            }

            $KBSContent->load_relationship('attachments');

            // Converts attached files to Notes.
            $attachments = $KBDocument->get_kbdoc_attachments_for_newemail($KBDocument->id);
            foreach ($attachments['attachments'] as $attachment) {
                $this->log("Convert attachment {$attachment['id']}.");
                $note = BeanFactory::getBean('Notes', $attachment['id']);
                $KBSContent->attachments->add($note);
            }
        }
    }

    /**
     * Return legacy KBDocuments.
     *
     * @return array Array of arrays.
     */
    protected function getOldDocuments()
    {
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('KBDocuments'));
        return $sq->execute();
    }

    /**
     * Return tags by document ID.
     * Written because the KBDocument's functions get_tags() and get_kbdoc_tags() return only one tag.
     *
     * @param $docId KBDocument ID.
     * @return array
     */
    protected function getOldTags($docId)
    {
        $sq = new SugarQuery();
        $sq->select(array('tag.kbtag_id'));
        $sq->joinTable(
            'kbdocuments_kbtags',
            array(
                'joinType' => 'INNER',
                'alias' => 'tag',
                'linkingTable' => true,
            )
        )->on()->equalsField(
            'kbdocuments.id',
            'tag.kbdocument_id'
        )->equals('tag.deleted', '0');
        $sq->from(BeanFactory::getBean('KBDocuments'));
        $sq->where()->equals('kbdocuments.id', $docId);

        return $sq->execute('array');
    }

    /**
     * Recursively converts old tags to topics\categories.
     *
     * @param KBTag $tag
     * @return string Associated topic ID.
     */
    protected function convertTagsToTopicsRecursive(KBTag $tag)
    {
        if (isset($this->convertedTagsTopics[$tag->id])) {
            return $this->convertedTagsTopics[$tag->id];
        }
        $topic = BeanFactory::newBean('Categories');
        $topic->name = $tag->tag_name;

        if ($tag->parent_tag_id) {
            $parentTag = BeanFactory::getBean('KBTags', $tag->parent_tag_id);
            $parentTopicId = $this->convertTagsToTopicsRecursive($parentTag);
            $parentTopic = BeanFactory::getBean('Categories', $parentTopicId, array('use_cache' => false));
            $parentTopic->append($topic);
        } else {
            $KBSContent = BeanFactory::getBean('KBSContents');
            $rootTopic = BeanFactory::getBean(
                'Categories',
                $KBSContent->getCategoryRoot(),
                array('use_cache' => false)
            );
            $rootTopic->append($topic);
        }

        $topicId = $topic->save();
        $this->convertedTagsTopics[$tag->id] = $topicId;

        return $topicId;
    }
}
