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

    public function run()
    {
        if (!version_compare($this->from_version, '7.7.0', '<=')) {
            return;
        }

        // Relationships for KBContents are not loaded yet.
        SugarRelationshipFactory::rebuildCache();

        //Setup category root
        $KBContent = BeanFactory::getBean('KBContents');
        $KBContent->setupCategoryRoot();

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

                foreach ($KBContent->kbarticles_kbcontents->getBeans() as $bean) {
                    $bean->assigned_user_id = $data['assigned_user_id'];
                    $bean->save();
                }
                foreach ($KBContent->kbdocuments_kbcontents->getBeans() as $bean) {
                    $bean->assigned_user_id = $data['assigned_user_id'];
                    $bean->save();
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
        $this->convertTags();
        $this->checkMenu();

        $tables = array(
            'prepKBDoc',
            'prepKBAtt',
            'prepKBTag',
        );

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->dropTableName($table);
            }
        }
    }

    /**
     * Convert all old tags to new categories.
     */
    protected function convertTags()
    {
        foreach ($this->getOldTags() as $tag) {
            $this->convertTagsToCategoriesRecursive($tag);
        }
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
        $data = array();
        $query = "SELECT * from prepKBDoc ORDER BY date_entered";
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
