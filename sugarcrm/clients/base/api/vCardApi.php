<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/vCard.php');
/*
 * vCard API implementation
 */
class vCardApi extends SugarApi {

    /**
     * This function registers the vCard api
     */
    public function registerApiRest() {
        return array(
            'vCardSave' => array(
                'reqType' => 'GET',
                'path' => array('VCardDownload'),
                'pathVars' => array(''),
                'method' => 'vCardSave',
                'rawReply' => true,
                'shortHelp' => 'An API to download a contact as a vCard.',
                'longHelp' => 'include/api/help/vcarddownload_get_help.html',
            ),
            'vCardImportPost' => array(
                'reqType' => 'POST',
                'path' => array('<module>', 'file', 'vcard_import'),
                'pathVars' => array('module', '', ''),
                'method' => 'vCardImport',
                'rawPostContents' => true,
                'shortHelp' => 'Imports a person record from a vcard',
                'longHelp' => 'include/api/help/module_file_vcard_import_post_help.html',
            ),
        );
    }

    /**
     * vCardSave 
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function vCardSave($api, $args)
    {
        $this->requireArgs($args, array('id'));

        $vcard = new vCard();

        if (isset($args['module'])) {
            $module = clean_string($args['module']);
        } else {
            $module = 'Contacts';
        }

        $vcard->loadContact($args['id'], $module);

        $vcard->saveVCard();
    }

    /**
     * vCardImport
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function vCardImport($api, $args)
    {
        $this->requireArgs($args, array('module'));

        $bean = BeanFactory::getBean($args['module']);
        if (!$bean->ACLAccess('save') || !$bean->ACLAccess('import')) {
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_NOT_AUTHORIZED');
        }

        if (isset($_FILES) && count($_FILES) === 1) {
            reset($_FILES);
            $first_key = key($_FILES);
            if (isset($_FILES[$first_key]['tmp_name']) && $this->isUploadedFile($_FILES[$first_key]['tmp_name']) && isset($_FILES[$first_key]['size']) > 0
            ) {
                $vcard = new vCard();
                try {
                    $recordId = $vcard->importVCard($_FILES[$first_key]['tmp_name'], $args['module']);
                } catch (Exception $e) {
                    throw new SugarApiExceptionRequestMethodFailure('ERR_VCARD_FILE_PARSE');
                }

                $results = array($first_key => $recordId);
                return $results;
            }
        } else {
            throw new SugarApiExceptionMissingParameter('ERR_VCARD_FILE_MISSING');
        }
    }

    /**
     * This function is a wrapper for checking if the file was uploaded so that the php built in function can be mocked
     * @param string FileName
     * @return boolean
     */
    protected function isUploadedFile ($fileName)
    {
        return is_uploaded_file($fileName);
    }
}
