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
     * Saves a file to a module field using the POST method
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @param bool $temporary true if we are saving a temporary image
     * @return array
     * @throws SugarApiExceptionError
     */
    public function saveFilePost($api, $args) {

        // Get the field
        $field = $args['field'];

        if($field != 'filename') {
            //Needed by SugarFieldImage.php to know if we are saving a temporary image
            $args['temp'] = false;
            // if it's not document's filename field, do the regular thing
            return parent::saveFilePost($api, $args);
        }
        // Filename field in Documents requires special handling since it is
        // supposed to create DocumentRevision

        // To support field prefixes like Sugar proper
        $prefix = empty($args['prefix']) ? '' : $args['prefix'];

        // Set the files array index (for type == file)
        $filesIndex = $prefix . $field;

        // Get the bean before we potentially delete if fails (e.g. see below if attachment too large, etc.)
        $bean = $this->loadBean($api, $args);

        if(!$bean->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        // Simple validation
        // In the case of very large files that are too big for the request too handle AND
        // if the auth token was sent as part of the request body, you will get a no auth error
        // message on uploads. This check is in place specifically for file uploads that are too
        // big to be handled by checking for the presence of the $_FILES array and also if it is empty.
        if (isset($_FILES)) {
            if (empty($_FILES)) {

                // If we get here, the attachment was > php.ini upload_max_filesize value so we need to
                // check if delete_if_fails optional parameter was set true, etc.
                $this->deleteIfFails($bean, $args);

                // @TODO Localize this exception message
                throw new SugarApiExceptionRequestTooLarge('Attachment is too large');
            }
        } else {
            throw new SugarApiExceptionMissingParameter('Attachment is missing');
        }

        if (empty($_FILES[$filesIndex])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter("Incorrect field name for attachement: $filesIndex");
        }

        // Handle ACL - if there is no current field data, it is a CREATE
        // This addresses an issue where the portal user has create but not edit
        // rights for particular modules. The perspective here is that even if
        // a record exists, if there is no attachment, you are CREATING the
        // attachment instead of EDITING the parent record. -rgonzalez
        $accessType = empty($bean->$field) ? 'create' : 'edit';
        $this->verifyFieldAccess($bean, $field, $accessType);

        $revision = $bean->createRevisionBean();
        if(!$revision->ACLAccess('create')) {
            throw new SugarApiExceptionNotAuthorized('No access to create revisions');
        }

        // Get the defs for this field
        $def = $bean->field_defs[$field];

        // Only work on file or image fields
        if (isset($def['type']) && ($def['type'] == 'image' || $def['type'] == 'file')) {
            // Get our tools to actually save the file|image
            require_once 'include/SugarFields/SugarFieldHandler.php';
            $sfh = new SugarFieldHandler();
            $sf = $sfh->getSugarField($def['type']);
            if ($sf) {
                // SugarFieldFile expects something different than SugarFieldImage
                if ($def['type'] == 'file') {
                    // docType setting is throwing errors if missing
                    if (!isset($def['docType'])) {
                        $def['docType'] = 'Sugar';
                    }

                    // Session error handler is throwing errors if not set
                    $_SESSION['user_error_message'] = array();

                    // Handle setting the files array to what SugarFieldFile is expecting
                    if (!empty($_FILES[$filesIndex]) && empty($_FILES[$filesIndex . '_file'])) {
                        $_FILES[$filesIndex . '_file'] = $_FILES[$filesIndex];
                        unset($_FILES[$filesIndex]);
                        $filesIndex .= '_file';
                    }
                }

                // Noticed for some reason that API FILE[type] was set to application/octet-stream
                // That breaks the uploader which is looking for very specific mime types
                // So rather than rely on what $_FILES thinks, set it with our own methodology
                require_once 'include/download_file.php';
                $dl = new DownloadFileApi($api);
                $mime = $dl->getMimeType($_FILES[$filesIndex]['tmp_name']);
                $_FILES[$filesIndex]['type'] = $mime;

                // Set the docType into args if its in the def
                // This addresses a need in the UploadFile object
                if (isset($def['docType']) && !isset($args[$prefix . $def['docType']])) {
                    $args[$prefix . $def['docType']] = $mime;
                }

                // This saves the attachment
                $sf->save($bean, $args, $field, $def, $prefix);

                // Handle errors
                if (!empty($sf->error)) {
                    throw new SugarApiExceptionError($sf->error);
                }

                // Recreate revision bean with correct data
                ++$bean->revision;
                $revision = $bean->createRevisionBean();
                $bean->document_revision_id = $revision->id;
                // Save the bean
                $bean->save();
                // move the file to the revision's ID
                if(empty($this->doc_type) || $this->doc_type == 'Sugar') {
                    rename("upload://{$bean->id}", "upload://{$revision->id}");
                }
                // Save the revision object
                $revision->save();

                // Prep our return
                $fileinfo = $this->getFileInfo($bean, $field, $api);

                // This isn't needed in this return
                unset($fileinfo['path']);

                // This is a good return
                return array(
                    $field => $fileinfo,
                    'record' => $this->formatBean($api, $args, $bean)
                );
            }
        }

        // @TODO Localize this exception message
        throw new SugarApiExceptionError("Unexpected field type: ".$def['type']);
    }
}
