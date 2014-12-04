<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'data/BeanFactory.php';
require_once 'clients/base/api/vCardApi.php';

require_once 'modules/pmse_Inbox/engine/PMSEBusinessRuleExporter.php';
require_once 'modules/pmse_Inbox/engine/PMSEBusinessRuleImporter.php';

class PMSEBusinessRules extends vCardApi
{
    public function registerApiRest()
    {
        return array(
            'businessRuleDownload' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Business_Rules', '?', 'brules'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'businessRuleDownload',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'An API to download a contact as a vCard.',
                'longHelp' => 'include/api/help/module_businessruledownload_get_help.html',
            ),
            'businessRulesImportPost' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Business_Rules', 'file', 'businessrules_import'),
                'pathVars' => array('module', '', ''),
                'method' => 'businessRulesImport',
                'rawPostContents' => true,
                'shortHelp' => 'Imports a business rules record from a pbr file',
                'longHelp' => 'include/api/help/module_file_business_rules_import_post_help.html',
            ),
        );
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws SugarApiExceptionMissingParameter
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionNotAuthorized
     */
    public function businessRulesImport($api, $args)
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
                    $importerObject = new PMSEBusinessRuleImporter();
                    $data = $importerObject->importProject($_FILES[$first_key]['tmp_name']);
                    $results = array('businessrules_import' => $data);
                } catch (Exception $e) {
                    throw new SugarApiExceptionRequestMethodFailure('ERR_VCARD_FILE_PARSE');
                }
                return $results;
            }
        } else {
            throw new SugarApiExceptionMissingParameter('ERR_VCARD_FILE_MISSING');
        }
    }

    /**
     * @param $api
     * @param $args
     * @return string
     * @throws SugarApiExceptionMissingParameter
     */
    public function businessRuleDownload($api, $args)
    {
        $emailTemplate = new PMSEBusinessRuleExporter();
        $requiredFields = array('record', 'module');
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $args)) {
                throw new SugarApiExceptionMissingParameter('Missing parameter: ' . $fieldName);
            }
        }

        return $emailTemplate->exportProject($args['record'], $api);
    }
}