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
 * Rename KBDocument module.
 */
class SugarUpgradeKBRename extends UpgradeScript
{
    public $order = 2000;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        if (version_compare($this->from_version, '7.6.0', '<')) {
            $converter = new ConvertKBDocument($this->context['source_dir'], array($this, 'log'));
            $converter->run();
        }
    }
}

/**
 * Convert KBDocument modules to KBOLDDocument.
 * Class convertKBDocument
 */
class ConvertKBDocument
{
    /**
     * Callback for log.
     * @var callable
     */
    protected $logCallback;

    /**
     * Path to work with,
     * @var String
     */
    protected $path;

    /**
     * Excluded paths.
     * @var array
     */
    protected $exclude = array('upload', 'upgrades');

    /**
     * Mask to search for.
     * @var array
     */
    protected $masks = array(
        'KBContent',
        'KBTag',
        'KBDocument',
        'KBDocumentRevision'
    );

    /**
     * Pairs old table - new table.
     * @var array
     */
    protected $tablemap = array(
        'kbcontents' => 'kboldcontents',
        'kbdocuments_kbtags' => 'kbolddocuments_kboldtags',
        'kbdocument_revisions' => 'kbolddocument_revisions',
        'kbdocuments' => 'kbolddocuments',
        'kbtags' => 'kboldtags',
        'kbcontents_audit' => 'kboldcontents_audit',
        'kbdocuments_views_ratings' => 'kbolddocuments_views_ratings'
    );

    /**
     * Pairs old field - new field for each table.
     * @var array
     */
    protected $fieldmap = array(
        'kbcontents' => array(
            'kbdocument_body' => 'kbolddocument_body',
            'kbcontentspk' => 'kboldcontentspk',
            'kbcontentsftk' => 'kboldcontentsftk'
        ),
        'kbdocuments_kbtags' => array(
            'kbdocument_id' => 'kbolddocument_id',
            'kbtag_id' => 'kboldtag_id',
            'kbdocumentskbtagspk' => 'kbolddocumentskboldtagspk',
        ),
        'kbdocument_revisions' => array(
            'kbdocument_id' => 'kbolddocument_id',
            'kbcontent_id' => 'kboldcontent_id',
            'idx_del_latest_kbcontent_id' => 'idx_del_latest_kboldcontent_id',
        ),
        'kbdocuments' => array(
            'kbdocument_name' => 'kbolddocument_name',
            'kbdocument_revision_id' => 'kbolddocument_revision_id',
            'kbdocument_revision_number' => 'kbolddocument_revision_number',
            'kbdocumentspk' => 'kbolddocumentspk',
            'idx_kbdocument_date_entered' => 'idx_kbolddocument_date_entered',
        ),
        'kbtags' => array(
            'kbtagspk' => 'kboldtagspk'
        ),
        'kbcontents_audit' => array(),
        'kbdocuments_views_ratings' => array(
            'kbdocument_id' => 'kbolddocument_id',
        ),

    );

    /**
     * Properties of relationships to update.
     * @var array
     */
    protected $relationmap = array(
        'kbdocuments_team_count_relationship' => array(
            'name' => 'kbolddocuments_team_count_relationship',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kbdocuments_teams' => array(
            'name' => 'kbolddocuments_teams',
            'lhs_module' => 'KBOLDDocuments',
            'lhs_table' => 'kbolddocuments',
        ),
        'kbdocuments_team' => array(
            'name' => 'kbolddocuments_team',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kbdocument_revisions' => array(
            'name' => 'kbolddocument_revisions',
            'lhs_module' => 'KBOLDDocuments',
            'lhs_table' => 'kbolddocuments',
            'rhs_module' => 'KBOLDDocumentRevisions',
            'rhs_table' => 'kbolddocument_revisions',
            'rhs_key' => 'kbolddocument_id',
        ),
        'kbdocuments_modified_user' => array(
            'name' => 'kbolddocuments_modified_user',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kbdocuments_created_by' => array(
            'name' => 'kbolddocuments_created_by',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kb_assigned_user' => array(
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kbdoc_approver_user' => array(
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'case_kbdocuments' => array(
            'name' => 'case_kbolddocuments',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'email_kbdocuments' => array(
            'name' => 'email_kbolddocuments',
            'rhs_module' => 'KBOLDDocuments',
            'rhs_table' => 'kbolddocuments',
        ),
        'kbrev_revisions_created_by' => array(
            'rhs_module' => 'KBOLDDocumentRevisions',
            'rhs_table' => 'kbolddocument_revisions',
        ),
        'kbtags_team_count_relationship' => array(
            'name' => 'kboldtags_team_count_relationship',
            'rhs_module' => 'KBOLDTags',
            'rhs_table' => 'kboldtags',
        ),
        'kbtags_teams' => array(
            'name' => 'kboldtags_teams',
            'lhs_module' => 'KBOLDTags',
            'lhs_table' => 'kboldtags',
        ),
        'kbtags_team' => array(
            'name' => 'kboldtags_team',
            'rhs_module' => 'KBOLDTags',
            'rhs_table' => 'kboldtags',
        ),
        'kbdocumentkbtags_team_count_relationship' => array(
            'name' => 'kbolddocumentkboldtags_team_count_relationship',
            'rhs_module' => 'KBOLDDocumentKBOLDTags',
            'rhs_table' => 'kbolddocuments_kboldtags',
        ),
        'kbdocumentkbtags_teams' => array(
            'name' => 'kbolddocumentkboldtags_teams',
            'lhs_module' => 'KBOLDDocumentKBOLDTags',
            'lhs_table' => 'kbolddocuments_kboldtags',
        ),
        'kbdocumentkbtags_team' => array(
            'name' => 'kbolddocumentkboldtags_team',
            'rhs_module' => 'KBOLDDocumentKBOLDTags',
            'rhs_table' => 'kbolddocuments_kboldtags',
        ),
        'kbrevisions_created_by' => array(
            'rhs_module' => 'KBOLDDocumentKBOLDTags',
            'rhs_table' => 'kbolddocuments_kboldtags',
        ),
        'kbcontents_team_count_relationship' => array(
            'name' => 'kboldcontents_team_count_relationship',
            'rhs_module' => 'KBOLDContents',
            'rhs_table' => 'kboldcontents',
        ),
        'kbcontents_teams' => array(
            'name' => 'kboldcontents_teams',
            'lhs_module' => 'KBOLDContents',
            'lhs_table' => 'kboldcontents',
        ),
        'kbcontents_team' => array(
            'name' => 'kboldcontents_team',
            'rhs_module' => 'KBOLDContents',
            'rhs_table' => 'kboldcontents',
        ),
    );

    public function __construct($path, $logCallback)
    {
        $this->path = $path;
        $this->logCallback = $logCallback;
    }

    /**
     * Call external logger.
     * @param String $msg
     */
    public function log($msg)
    {
        call_user_func_array($this->logCallback, array($msg));
    }

    /**
     * Run conversion.
     */
    public function run()
    {
        $files = array();
        $files = $this->getFiles($this->path, $files, $this->exclude);
        $this->processFiles($files);
        $this->renameFiles($files);
        $this->copyTables($this->tablemap);
        $this->updateRelationships($this->relationmap);
    }

    /**
     * Get files to work with,
     * @param string $path
     * @param array $files
     * @return array
     */
    public function getFiles($path, $files)
    {
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $info) {
            if ($info->isFile()) {
                $files[$info->getPathname()] = $info->getRealPath();
            } elseif (!$info->isDot() || strpos($info->getFilename(), '.') !== 0) {
                if (!in_array($info->getFilename(), $this->exclude)) {
                    $list = $this->getFiles($info->getPathname(), $files);
                    if (!empty($list)) {
                        $files = array_merge($files, $list);
                    }
                }
            }
        }
        return $files;
    }

    /**
     * Return pattern to search in files.
     * @return string
     */
    public function getPattern()
    {
        $pattern = array();
        foreach ($this->masks as $mask) {
            $pattern[] = preg_quote($mask, '/');
        }
        return '/(' . implode('|', $pattern) . ')+/iu';
    }

    /**
     * Rename files..
     * @param $files
     */
    public function renameFiles($files)
    {
        $pattern = $this->getPattern();
        foreach ($files as $file => $_) {
            $count = 0;
            $newFile = preg_replace_callback($pattern, array($this, 'searchAndRepalce'), $file, -1, $count);
            if ($count > 0) {
                $dir = dirname($newFile);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $this->log("rename: {$file} to {$newFile}");
                rename($file, $newFile);
            }
        }
    }

    /**
     * Work with files' content.
     * @param $files
     */
    public function processFiles($files)
    {
        $pattern = $this->getPattern();
        foreach ($files as $file) {
            $count = 0;
            $content = file_get_contents($file);
            $content = preg_replace_callback($pattern, array($this, 'searchAndRepalce'), $content, -1, $count);
            if ($count > 0) {
                $this->log("file: {$file} changes: {$count}");
                file_put_contents($file, $content);
            }
        }
    }

    /**
     * Replace found matches with new string.
     * @param array $match
     * @return string
     */
    public function searchAndRepalce($match)
    {
        $string = $match[0];
        if ($string[0] === 'K' || $string[1] === 'B') {
            $prefix = 'KBOLD';
        } else {
            $prefix = 'kbold';
        }
        $res = $prefix . substr($string, 2);
        return $res;
    }

    /**
     * Create new tables and copy data from old.
     * @param $tables
     */
    public function copyTables($tables)
    {
        $db = DBManagerFactory::getInstance();
        foreach ($tables as $table => $newTable) {
            $cols = array();
            $columns = $db->get_columns($table);
            $newColumns = array();
            $fieldmap = $this->fieldmap[$table];
            foreach ($columns as $column => $def) {
                if (!empty($fieldmap[$column])) {
                    $def['name'] = $fieldmap[$column];
                    $newColumns[$fieldmap[$column]] = $def;
                    $cols[$fieldmap[$column]] = $column;
                } else {
                    $newColumns[$column] = $def;
                    $cols[$column] = $column;
                }
            }
            $indexes = $db->get_indices($table);
            $newIndexes = array();
            foreach ($indexes as $index => $def) {
                if (!in_array($def['type'], array('unique', 'primary'))) {
                    continue;
                }
                $newIndexFields = array();
                foreach ($def['fields'] as $field) {
                    if (!empty($fieldmap[$field])) {
                        array_push($newIndexFields, $fieldmap[$field]);
                    } else {
                        array_push($newIndexFields, $field);
                    }
                }
                $def['fields'] = $newIndexFields;
                if (!empty($fieldmap[$index])) {
                    $def['name'] = $fieldmap[$index];
                    $newIndexes[$fieldmap[$index]] = $def;
                } else {
                    $newIndexes[$index] = $def;
                }
            }
            if ($db->createTableParams($newTable, $newColumns, $newIndexes)) {
                $sql =
                    "INSERT INTO {$newTable} (" . implode(',', array_keys($cols)) . ") ".
                    "SELECT " . implode(',', array_values($cols)) . " FROM {$table}";
                $db->query($sql);
                $db->dropTableName($table);
                $this->log("table: {$table} renamed to: {$newTable}");
            }
        }
    }

    /**
     * Update relationship definitions.
     * @param array $relationships
     */
    public function updateRelationships($relationships)
    {
        $relationship = new Relationship();
        foreach ($relationships as $name => $def) {
            $relationship->retrieve_by_name($name);
            if (empty($relationship->id)) {
                continue;
            }
            foreach ($def as $key => $value) {
                $relationship->$key = $value;
            }
            $relationship->save(false);
            $this->log("relationship: {$name} updated");
        }
        SugarRelationshipFactory::deleteCache();
        if (class_exists('SugarAutoLoader', true) && method_exists('SugarAutoLoader', 'buildCache')) {
            SugarAutoLoader::buildCache();
        }
    }
}
