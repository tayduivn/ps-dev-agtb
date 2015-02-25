<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'data/BeanFactory.php';
require_once 'clients/base/api/vCardApi.php';
require_once 'modules/pmse_Inbox/engine/PMSEProjectImporter.php';
require_once 'modules/pmse_Inbox/engine/PMSEProjectExporter.php';

class PMSEProjectImportExportApi extends vCardApi
{
    /**
     *
     * @return type
     */
    public function registerApiRest()
    {
        return array(
            'projectImportPost' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Project', 'file', 'project_import'),
                'pathVars' => array('module', '', ''),
                'method' => 'projectImport',
                'rawPostContents' => true,
                'shortHelp' => 'Imports a project record from a bpm file',
                'longHelp' => 'modules/ProcessMaker/api/help/file_project_import_post_help.html',
            ),
            'projectDownload' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', '?', 'dproject'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'projectDownload',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'An API to download a contact as a vCard.',
                'longHelp' => 'modules/ProcessMaker/api/help/module_projectdownload_get_help.html',
            ),
        );
    }

    public function projectDownload($api, $args)
    {
        $projectBean = new PMSEProjectExporter();
        $requiredFields = array('record', 'module');
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $args)) {
                throw new SugarApiExceptionMissingParameter('Missing parameter: ' . $fieldName);
            }
        }

        return $projectBean->exportProject($args['record'], $api);
    }

    public function projectImport($api, $args)
    {
        $this->requireArgs($args, array('module'));

        $bean = BeanFactory::getBean($args['module']);
        if (!$bean->ACLAccess('save') || !$bean->ACLAccess('import')) {
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_NOT_AUTHORIZED');
        }

        if (isset($_FILES) && count($_FILES) === 1) {
            reset($_FILES);
            $first_key = key($_FILES);
            if (isset($_FILES[$first_key]['tmp_name'])
                && $this->isUploadedFile($_FILES[$first_key]['tmp_name'])
                && isset($_FILES[$first_key]['size'])
                && isset($_FILES[$first_key]['size']) > 0
            ) {
                try {
                    $importerObject = new PMSEProjectImporter();
                    $name = $_FILES[$first_key]['name'];
                    $extension = end(explode(".", $name));
                    if ($extension == $importerObject->getExtension()) {
                        $data = $importerObject->importProject($_FILES[$first_key]['tmp_name']);
                        $results = array('project_import' => $data);
                    } else  {
                        throw new SugarApiExceptionRequestMethodFailure('ERROR_UPLOAD_FAILED');
                    }
                } catch (Exception $e) {
                    throw new SugarApiExceptionRequestMethodFailure('ERROR_UPLOAD_FAILED');
                }

                return $results;
            }
        } else {
            throw new SugarApiExceptionMissingParameter('ERROR_UPLOAD_FAILED');
        }
    }
}