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
 * Converts old KB module.
 */
class SugarUpgradeConvertKBOLDDocuments extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.5';

    /**
     * @var array ID => Name.
     */
    protected $newTags = array();

    /**
     * Converted tags to categories.
     * @var array
     */
    protected $convertedTagsCategories = array();

    /**
     * Converted tags to tag.
     * @var array
     */
    protected $convertedTagsTag = array();

    public function run()
    {
        if (!version_compare($this->from_version, '7.7.0', '<=')) {
            return;
        }

        // Relationships for KBContents are not loaded yet.
        SugarRelationshipFactory::rebuildCache();

        // Need to setup custom tables.
        $rac = new RepairAndClear();
        $rac->execute = true;
        $rac->show_output = false;
        $rac->repairDatabase();

        //Setup category root
        $KBContent = BeanFactory::getBean('KBContents');
        $KBContent->setupCategoryRoot();
        $this->convertTags();

        while ($documents = $this->getOldDocuments()) {
            foreach ($documents as $row) {
                $this->log("Convert the KBOLDDocument {$row['id']} to a KBContent.");

                /** @var $KBBean KBContent */
                $KBContent = BeanFactory::getBean('KBContents');

                $data = $row;
                unset($data['id']);
                $data['kbdocument_body'] = $data['body'];
                $data['kbsapprover_id'] = $data['kbdoc_approver_id'];
                $data['is_external'] = empty($data['is_external_article']) ? false : true;
                $data['viewcount'] = $data['views_number'];

                $app_list_strings = return_app_list_strings_language('en_us');
                // Yes, the status_id is a lable.
                if ($data['status_id'] == 'Published') {
                    $data['status'] = KBContent::ST_PUBLISHED;
                } else {
                    $statusKey = array_search($data['status_id'], $app_list_strings['kbdocument_status_dom']);
                    $data['status'] = ($statusKey !== false) ? $statusKey : KBContent::DEFAULT_STATUS;
                }

                $KBContent->populateFromRow($data);
                $KBContent->set_created_by = false;
                $KBContent->update_modified_by = false;
                $KBContent->save();

                $KBContent->load_relationship('tag_link');
                foreach ($this->getOldDocTagIDs($row['id']) as $tag) {
                    $tagBean = $this->convertTagToTag(array('id' => $tag));
                    $KBContent->tag_link->add($tagBean);
                }

                foreach ($KBContent->kbarticles_kbcontents->getBeans() as $bean) {
                    $bean->assigned_user_id = $data['assigned_user_id'];
                    $bean->save();
                }
                foreach ($KBContent->kbdocuments_kbcontents->getBeans() as $bean) {
                    $bean->assigned_user_id = $data['assigned_user_id'];
                    $bean->save();
                }

                if (!empty($data['parent_type']) && $data['parent_type'] == 'Cases') {
                    $case = BeanFactory::getBean('Cases', $data['parent_id']);
                    if (!empty($case) && !empty($case->id)) {
                        $KBContent->load_relationship('relcases_kbcontents');
                        $KBContent->relcases_kbcontents->add($case);
                    }
                }

                $KBContent->load_relationship('attachments');

                // Converts attached files to Notes.
                $attachments = $this->getAttachments($row['id']);
                foreach ($attachments as $note) {
                    $this->log("Convert attachment {$note->id}.");
                    $KBContent->attachments->add($note);
                }
            }
        }
        $this->checkMenu();

        $tables = array(
            'prepKBDoc',
            'prepKBAtt',
            'prepKBTag',
            'prepKBCustom',
            'prepKBDocTag',
        );

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->dropTableName($table);
            }
        }
    }

    /**
     * Get IDs of tags for old document by old ID.
     * @param $id
     * @return array
     */
    protected function getOldDocTagIDs($id)
    {
        $data = array();
        $query = "SELECT
            kbtag_id
            FROM prepKBDocTag
            WHERE kbdocument_id = {$this->db->quoted($id)}";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            array_push($data, $row['kbtag_id']);
        }
        return $data;
    }

    /**
     * Convert all old tags to new categories.
     */
    protected function convertTags()
    {
        foreach ($this->getOldTags() as $tag) {
            $this->convertTagsToCategoriesRecursive($tag);
            $this->convertTagToTag($tag);
        }
    }

    /**
     * Convert old tag to new one.
     * @param $tag
     * @return null|SugarBean
     */
    protected function convertTagToTag($tag)
    {
        if (isset($this->convertedTagsTag[$tag['id']])) {
            return $this->convertedTagsTag[$tag['id']];
        }
        $tagBean = BeanFactory::getBean('Tags');
        $tagName = trim($tag['tag_name']);

        // See if this tag exists already. If it does send back the bean for it
        $q = new SugarQuery();
        // Get the tag from the lowercase version of the name, selecting all
        // fields so that we can load the bean from these fields if found
        $q->select(array('id', 'name', 'name_lower'));
        $q->from($tagBean)
            ->where()
            ->equals('name_lower', strtolower($tagName));
        $result = $q->execute();

        // If there is a result for this tag name, send back the bean for it
        if (!empty($result[0]['id'])) {
            $tagBean->fromArray($result[0]);
        } else {
            $tagBean->fromArray(array('name' => $tagName));
            $tagBean->verifiedUnique = true;
            $tagBean->save();
        }

        $this->convertedTagsTag[$tag['id']] = $tagBean;
        return $tagBean;
    }

    /**
     * Remove old KB from menu and add new one.
     */
    protected function checkMenu()
    {
        require_once('modules/MySettings/TabController.php');
        $tc = new TabController();

        $tabs = $tc->get_system_tabs();
        if (isset($tabs['KBDocuments'])) {
            unset($tabs['KBDocuments']);
        }
        if (!isset($tabs['KBContents'])) {
            $tabs['KBContents'] = 'KBContents';
        }
        $tc->set_system_tabs($tabs);
    }

    /**
     * Return legacy KBOLDDocuments.
     *
     * @return array|bool
     */
    protected function getOldDocuments()
    {
        static $count = 0;
        static $custom = null;
        if ($custom === null) {
            $custom = $this->db->tableExists('prepKBCustom');
        }
        $data = array();
        $query = "SELECT prepKBDoc.*";
        if ($custom) {
            $query .= ", prepKBCustom.* from prepKBDoc LEFT JOIN prepKBCustom on prepKBCustom.id_c = prepKBDoc.id";
        } else {
            $query .= " from prepKBDoc";
        }
        $query .= " ORDER BY prepKBDoc.date_entered";
        $query = $this->db->limitQuery($query, $count * 100, 100, false, '', false);
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            array_push($data, $row);
        }
        $count = $count + 1;
        return count($data) > 0 ? $data : false;
    }

    /**
     * Get attachments for old document.
     * @param string $docId
     * @return array
     */
    protected function getAttachments($docId)
    {
        $data = array();
        $query = "SELECT
            id, filename, file_mime_type
            FROM prepKBAtt
            WHERE kbdocument_id = {$this->db->quoted($docId)}";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            $fileLocation = "upload://{$row['id']}";
            $note = BeanFactory::getBean('Notes');
            $note->id = create_guid();
            $note->new_with_id = true;
            $note->name = $row['filename'];
            $note->filename = $row['filename'];
            $noteFile = "upload://{$note->id}";
            $note->file_mime_type = $row['file_mime_type'];
            copy($fileLocation, $noteFile);
            $note->save();
            array_push($data, $note);
        }
        return $data;
    }

    /**
     * Return tags by document ID.
     * Written because the KBOLDDocument's functions get_tags() and get_kbdoc_tags() return only one tag.
     *
     * @return array
     */
    protected function getOldTags()
    {
        $data = array();
        $query = "SELECT * FROM prepKBTag";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            array_push($data, $row);
        }
        return $data;
    }

    /**
     * Return data for old KBTag.
     * @param string $id
     * @return mixed
     */
    protected function getOldTag($id)
    {
        $query = "SELECT * FROM prepKBTag WHERE id = {$this->db->quoted($id)}";
        return $this->db->fetchOne($query);
    }

    /**
     * Recursively converts old tags to categories.
     *
     * @param array $tag
     * @return string Associated category ID.
     */
    protected function convertTagsToCategoriesRecursive($tag)
    {
        if (isset($this->convertedTagsCategories[$tag['id']])) {
            return $this->convertedTagsCategories[$tag['id']];
        }
        $category = BeanFactory::newBean('Categories');
        $category->name = $tag['tag_name'];

        if ($tag['parent_tag_id']) {
            $parentTag = $this->getOldTag($tag['parent_tag_id']);
            $parentCategoryId = $this->convertTagsToCategoriesRecursive($parentTag);
            $parentCategory = BeanFactory::getBean('Categories', $parentCategoryId, array('use_cache' => false));
            $parentCategory->append($category);
        } else {
            $KBContent = BeanFactory::getBean('KBContents');
            $rootCategory = BeanFactory::getBean(
                'Categories',
                $KBContent->getCategoryRoot(),
                array('use_cache' => false)
            );
            $rootCategory->append($category);
        }

        $categoryID = $category->save();
        $this->convertedTagsCategories[$tag['id']] = $categoryID;

        return $categoryID;
    }
}
