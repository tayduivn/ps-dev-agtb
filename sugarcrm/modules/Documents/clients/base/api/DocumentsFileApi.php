<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
require_once 'clients/base/api/FileApi.php';
/**
 * API Class to handle file and image (attachment) interactions with a field in
 * a record.
 */
class DocumentsFileApi extends FileApi {
    /**
     * Dictionary registration method, called when the API definition is built
     *
     * @return array
     */
    public function registerApiRest() {
        return array(
            'saveFilePost' => array(
                'reqType' => 'POST',
                'path' => array('Documents', '?', 'file', '?'),
                'pathVars' => array('module', 'record', '', 'field'),
                'method' => 'saveFilePost',
                'rawPostContents' => true,
                'shortHelp' => 'Saves a file. The file can be a new file or a file override.',
                'longHelp' => 'include/api/help/module_record_file_field_post_help.html',
            ),
            'saveFilePut' => array(
                'reqType' => 'PUT',
                'path' => array('Documents', '?', 'file', '?'),
                'pathVars' => array('module', 'record', '', 'field'),
                'method' => 'saveFilePut',
                'rawPostContents' => true,
                'shortHelp' => 'Saves a file. The file can be a new file or a file override. (This is an alias of the POST method save.)',
                'longHelp' => 'include/api/help/module_record_file_field_put_help.html',
            ),
        );
    }

    /**
     * (non-PHPdoc)
     * @see FileApi::checkFileAccess()
     */
    protected function checkFileAccess($bean, $field, $args)
    {
        parent::checkFileAccess($bean, $field, $args);
        // Check that we can create revision
        $revision = $bean->createRevisionBean();
        if(!$revision->ACLAccess('create')) {
            throw new SugarApiExceptionNotAuthorized('No access to create revisions');
        }
    }

    /**
     * (non-PHPdoc)
     * @see FileApi::saveBean()
     */
    protected function saveBean($bean)
    {
        // Recreate revision bean with correct data
        if($bean->document_revision_id) {
            ++$bean->revision;
        } else {
            $bean->revision = 1;
        }
        $revision = $bean->createRevisionBean();
        $bean->document_revision_id = $revision->id;
        // Save the bean
        $bean->save();
        // move the file to the revision's ID
        if(empty($bean->doc_type) || $bean->doc_type == 'Sugar') {
            rename("upload://{$bean->id}", "upload://{$revision->id}");
        }
        // Save the revision object
        $revision->save();
        // update the fields
        $bean->fill_in_additional_detail_fields();
    }

    protected function deleteIfFails($bean, $args)
    {
        // if we already have the revision, we won't delete the document on failure to add another one
        if($bean->document_revision_id) {
            return;
        }
        parent::deleteIfFails($bean, $args);
    }
}
