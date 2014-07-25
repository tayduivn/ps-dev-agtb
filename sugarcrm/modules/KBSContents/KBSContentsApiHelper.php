<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once 'data/SugarBeanApiHelper.php';

class KBSContentsApiHelper extends SugarBeanApiHelper {

    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array())
    {
        if ($this->api->action == 'view' && !empty($this->api->getRequest()->args['viewed'])) {
            $bean->viewcount = $bean->viewcount + 1;
            $query = "UPDATE {$bean->table_name}
                set viewcount = {$bean->viewcount}
                where id = {$bean->db->quoted($bean->id)}";
            $bean->db->query($query);
        }
        $result = parent::formatForApi($bean, $fieldList, $options);

        $bean->load_relationship('attachments');
        $result['attachment_list'] = array();
        foreach ($bean->attachments->getBeans() as $attachment) {
            $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), 'upload://'. $attachment->id);
            $attach = array(
                'id' => $attachment->id,
                'filename' => $attachment->filename,
                'name' => $attachment->filename,
                'isImage' => strpos($mimeType, 'image') !== false,
            );
            array_push($result['attachment_list'], $attach);
        }

        $query = new SugarQuery();
        $query->select(array('language'));
        $query->distinct(true);
        $query->from(BeanFactory::getBean('KBSContents'));
        $query->where()
            ->equals('kbsdocument_id', $bean->kbsdocument_id);
        
        $langs = $query->execute();
        if ($langs) {
            $result['related_languages'] = array();
            foreach ($langs as $lang) {
                $result['related_languages'][] = $lang['language'];
            }
        }

        return $result;
    }

    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        $attachment_list = array();
        if (!empty($submittedData['attachment_list'])) {
            $attachment_list = $submittedData['attachment_list'];
            unset($submittedData['attachment_list']);
        }
        $result = parent::populateFromApi($bean, $submittedData, $options);
        if (!empty($attachment_list) && $result) {
            $bean->load_relationship('attachments');
            $attachments = array();
            if ($bean->id) {
                $attachments = $bean->attachments->getBeans();
            } else {
                $bean->id = create_guid();
                $bean->new_with_id = true;
            }
            foreach ($attachment_list as $info) {
                $found = false;
                foreach ($attachments as $attachment) {
                    if ($attachment->id === $info['id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    //@TODO: Add mime-type detection
                    $attachment = BeanFactory::getBean('Notes', $info['id']);
                    if (!$attachment) {
                        $attachment = BeanFactory::getBean('Notes');
                        $attachment->new_with_id = true;
                        $attachment->portal_flag = true;
                        $attachment->id = create_guid();
                        sugar_rename(
                            UploadFile::get_file_path('', $info['id'], true),
                            UploadFile::get_file_path('', $attachment->id, true)
                        );
                        $attachment->filename = $info['name'];
                        $attachment->name = $info['name'];
                    }
                    $bean->attachments->add($attachment);
                }
            }
        }
        return $result;
    }
}