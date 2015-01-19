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
/**
 * Removes files that are no longer valid in 7.x for the KBOLDDocuments module.
 */
class SugarUpgradeKBOLDDocumentsRemoveFiles extends UpgradeScript
{
    public $order = 8501;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $files = array();

        // Remove these files if the from_version is less than 7.7.0.
        if (version_compare($this->from_version, '7.7.0', '<')) {
            // Files to delete.
            $files = array(
                'modules/KBOLDDocuments',
                'modules/KBOLDDocumentKBOLDTags',
                'module/KBOLDDocumentRevisions',
                'module/KBOLDContents',
                'module/KBOLDTags',
            );
            // check custom files
            if (class_exists('SugarAutoLoader', true) && method_exists('SugarAutoLoader', 'existingCustom')) {
                $files = array_unique(array_merge($files, SugarAutoLoader::existingCustom($files)));
            }
            // tables to delete
            $tables = array(
                'kboldcontents',
                'kboldcontents_cstm',
                'kboldcontents_audit',
                'kbolddocument_revisions',
                'kbolddocuments',
                'kbolddocuments_cstm',
                'kbolddocuments_kboldtags',
                'kbolddocuments_views_ratings',
                'kboldtags',
                'kboldtags_cstm',
            );

            $db = DBManagerFactory::getInstance();
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    $db->dropTableName($table);
                }
            }
        }
        if (!empty($files)) {
            $this->fileToDelete($files);
        }
    }
}
