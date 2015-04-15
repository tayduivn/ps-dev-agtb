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


require_once 'clients/base/api/vCardApi.php';

require_once 'modules/pmse_Project/clients/base/api/wrappers/PMSECrmDataWrapper.php';
require_once 'modules/pmse_Inbox/engine/PMSEEmailTemplateExporter.php';
require_once 'modules/pmse_Inbox/engine/PMSEEmailTemplateImporter.php';

class PMSEEmailsTemplates extends vCardApi
{
    public function __construct()
    {
        $this->crmDataWrapper = new PMSECrmDataWrapper();
    }

    public function registerApiRest()
    {
        return array(
            'emailTemplateDownload' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Emails_Templates', '?', 'etemplate'),
                'pathVars' => array('module', 'record', ''),
                'method' => 'emailTemplateDownload',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'An API to download a contact as a vCard.',
                'longHelp' => 'include/api/help/module_emailtemplatedownload_get_help.html',
            ),
            'emailTemplatesImportPost' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Emails_Templates', 'file', 'emailtemplates_import'),
                'pathVars' => array('module', '', ''),
                'method' => 'emailTemplatesImport',
                'rawPostContents' => true,
                'shortHelp' => 'Imports a Email Templates record from a pet file',
                'longHelp' => 'include/api/help/module_file_email_templates_import_post_help.html',
            ),
            'listVariables' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Emails_Templates', 'variables', 'find'),
                'pathVars' => array('module', '', ''),
                'method' => 'findVariables',
                'shortHelp' => 'Search For Variables of related Modules',
                'longHelp' => 'modules/Emails/clients/base/api/help/variables_related_modules_find_get_help.html',
            ),
            'listModulesRelated' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Emails_Templates', '?', 'find_modules'),
                'pathVars' => array('module', '', ''),
                'method' => 'retrieveRelatedBeans',
                'shortHelp' => 'Search For Variables of related Modules',
                'longHelp' => 'modules/Emails/clients/base/api/help/variables_related_modules_find_get_help.html',
            ),
        );
    }

    /**
     * Finds related modules variables that match the search term.
     *
     * Arguments:
     *    q           - search string
     *    module_list -  one of the keys from $modules
     *    order_by    -  columns to sort by (one or more of $sortableColumns) with direction
     *                   ex.: name:asc,id:desc (will sort by last_name ASC and then id DESC)
     *    offset      -  offset of first record to return
     *    max_num     -  maximum records to return
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function findVariables($api, $args)
    {
        //ini_set("max_execution_time", 300);
        $offset = 0;
        $limit = (!empty($args["max_num"])) ? (int)$args["max_num"] : 20;
        $orderBy = array();

        if (!empty($args["offset"])) {
            if ($args["offset"] === "end") {
                $offset = "end";
            } else {
                $offset = (int)$args["offset"];
            }
        }

        if (!empty($args["order_by"])) {
            $orderBys = explode(",", $args["order_by"]);

            foreach ($orderBys as $sortBy) {
                $column = $sortBy;
                $direction = "ASC";

                if (strpos($sortBy, ":")) {
                    // it has a :, it's specifying ASC / DESC
                    list($column, $direction) = explode(":", $sortBy);

                    if (strtolower($direction) == "desc") {
                        $direction = "DESC";
                    } else {
                        $direction = "ASC";
                    }
                }

                // only add column once to the order-by clause
                if (empty($orderBy[$column])) {
                    $orderBy[$column] = $direction;
                }
            }
        }

        $records = array();
        $nextOffset = -1;

        if ($offset !== "end") {
            //$records = $this->crmDataWrapper->retrieveFields($args["module_list"]);
            //$records = $this->crmDataWrapper->retrieveFields($args["module_list"], $direction, $limit, $offset);
            $records = $this->retrieveFields($args["module_list"], $direction, $limit, $offset);
            $totalRecords = $records['totalRecords'];
            $trueOffset = $offset + $limit;

            if ($trueOffset < $totalRecords) {
                $nextOffset = $trueOffset;
            }
        }

        return array(
            "next_offset" => $nextOffset,
            "records" => $records['records'],
        );
}
    public function retrieveFields($module, $orderBy, $limit, $offset)
    {
        global $beanList;
        if (isset($beanList[$module])) {
            $newModuleFilter = $module;
        } else {
            $related = $this->crmDataWrapper->getRelationshipData($module);
            $newModuleFilter = $related['rhs_module'];
        }

        $output = array();
        $moduleBean = BeanFactory::getBean($newModuleFilter);
        $fieldsData = isset($moduleBean->field_defs) ? $moduleBean->field_defs : array();
        foreach ($fieldsData as $field) {
            //$retrieveId = isset($additionalArgs['retrieveId']) && !empty($additionalArgs['retrieveId']) && $field['name'] == 'id' ? $additionalArgs['retrieveId'] : false;
            if (isset($field['vname']) && (($this->crmDataWrapper->isValidStudioField($field) && !$this->crmDataWrapper->fieldTodo($field)))){ // || $retrieveId)) {
                $tmpField = array();
                $tmpField['id'] = $field['name'];
                $tmpField['_module'] = $newModuleFilter;
                $tmpField['name'] = str_replace(':', '', translate($field['vname'], $newModuleFilter));
                $tmpField['rhs_module'] = $module;
                $output[] = $tmpField;
            }
        }

        $text = array();
        foreach ($output as $key => $row) {
            $text[$key] = strtolower($row['name']);
        }
        if($orderBy == "ASC"){
            array_multisort($text, SORT_ASC, $output);
        } else {
            array_multisort($text, SORT_DESC, $output);
        }
        $start = $offset;
        $end = $offset + $limit;
        $count = 0;
        $outputTmp = array();
        foreach($output as $field){
            if($count >= $start && $count < $end){
                $outputTmp[] = $field;
            }
            $count++;
        }

        return array('totalRecords' => count($output),'records' => $outputTmp);
    }


    public function retrieveRelatedBeans($api, $args)
    {
        $related_modules = $this->crmDataWrapper->retrieveRelatedBeans($args['module_list']);
        return $related_modules;
    }

    public function emailTemplatesImport($api, $args)
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
                    $importerObject = new PMSEEmailTemplateImporter();
                    $name = $_FILES[$first_key]['name'];
                    $extension = pathinfo($name,  PATHINFO_EXTENSION);
                    if ($extension == $importerObject->getExtension()) {
                        $data = $importerObject->importProject($_FILES[$first_key]['tmp_name']);
                        $result = array('emailtemplates_import' => $data);
                    } else  {
                        throw new SugarApiExceptionRequestMethodFailure('ERROR_UPLOAD_FAILED');
                    }
                } catch (Exception $e) {
                    throw new SugarApiExceptionRequestMethodFailure('ERROR_UPLOAD_FAILED');
                }
                return $result;
            }
        } else {
            throw new SugarApiExceptionMissingParameter('ERROR_UPLOAD_FAILED');
        }
    }

    public function emailTemplateDownload ($api, $args)
    {
        $emailTemplate = new PMSEEmailTemplateExporter();
        $requiredFields  = array('record', 'module');
        foreach ( $requiredFields as $fieldName ) {
            if ( !array_key_exists($fieldName, $args) ) {
                throw new SugarApiExceptionMissingParameter('Missing parameter: '.$fieldName);
            }
        }

        return $emailTemplate->exportProject($args['record'], $api);
    }
}