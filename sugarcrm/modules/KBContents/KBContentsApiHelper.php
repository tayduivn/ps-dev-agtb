<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'data/SugarBeanApiHelper.php';

class KBContentsApiHelper extends SugarBeanApiHelper {

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
        $fromOptions = array('team_security' => false);
        $query->from(BeanFactory::getBean('KBContents'), $fromOptions);
        $query->where()
            ->equals('kbdocument_id', $bean->kbdocument_id);
        
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
                    $note = BeanFactory::getBean('Notes', $info['id']);
                    if ($note) {
                        $attachment = clone $note;
                        $attachment->new_with_id = true;
                        $attachment->portal_flag = true;
                        $attachment->id = create_guid();
                        UploadFile::duplicate_file($note->id, $attachment->id);
                        $bean->attachments->add($attachment);
                    }
                }
            }
        }
        return $result;
    }
}
