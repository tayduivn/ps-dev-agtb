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
     * @var array ID => Name.
     */
    protected $newTags = array();

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

                $app_list_strings = return_app_list_strings_language('en_us');
                // Yes, the status_id is a lable.
                if ($data['status_id'] == 'Published') {
                    $data['status'] = ($data['is_external'] === true) ?
                        KBContent::ST_PUBLISHED_EX :
                        KBContent::ST_PUBLISHED_IN;
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
        $query = "SELECT
            jt0.id assigned_user_id ,
            jt0.user_name assigned_user_name ,
            jt1.id kbdoc_approver_id ,
            jt1.user_name kbdoc_approver_name ,
            cont.kbolddocument_body body ,
            kvr.views_number views_number ,
            kbdocuments.id ,
            kbdocuments.kbolddocument_name name,
            kbdocuments.active_date ,
            kbdocuments.exp_date ,
            kbdocuments.status_id ,
            kbdocuments.date_entered date_entered ,
            kbdocuments.date_modified ,
            kbdocuments.deleted ,
            kbdocuments.is_external_article ,
            kbdocuments.modified_user_id ,
            kbdocuments.created_by,
            kbdocuments.case_id kbscase_id
        FROM
            kbolddocuments kbdocuments
            LEFT JOIN kbolddocuments_views_ratings kvr
                ON kbdocuments.id = kvr.kbolddocument_id
            LEFT JOIN users jt0
                ON jt0.id = kbdocuments.assigned_user_id
                AND jt0.deleted = 0
            LEFT JOIN users jt1
                ON jt1.id = kbdocuments.kbdoc_approver_id
                AND jt1.deleted = 0
            LEFT JOIN kbolddocument_revisions rev
                ON rev.kbolddocument_id = kbdocuments.id
                AND rev.latest = 1
                AND rev.deleted = 0
            LEFT JOIN kboldcontents cont on rev.kboldcontent_id = cont.id
        ORDER BY
            kbdocuments.date_entered";
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
        $query = "SELECT id, filename, file_mime_type
        FROM
          document_revisions
        WHERE
            id IN (
                SELECT
                    document_revision_id
                FROM
                    kbolddocument_revisions
                WHERE
                    kbolddocument_id = {$this->db->quoted($docId)}
                    AND deleted = 0
            )
            AND file_mime_type IS NOT NULL
            AND deleted = 0";
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

}
