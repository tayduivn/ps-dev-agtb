<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('clients/base/api/ModuleApi.php');
require_once('modules/ProspectLists/ProspectListsService.php');

class ProspectListsApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'addToList' => array(
                'reqType' => 'POST',
                'path' => array('<module>', 'addToList'),
                'pathVars' => array('', ''),
                'method' => 'addRecordsToProspectList',
                'shortHelp' => 'This method updates a target list with selected users/contacts/leads',
                'longHelp' => 'include/api/help/module_addtolist_post_help.html',
            ),
        );
    }

    /**
     * Adds records to a prospect list
     */
    public function addRecordsToProspectList($api, $args)
    {
        $moduleName = $args['module'];
        $prospectListId = $args['prospectListId'];
        $recordIds = $args['recordIds'];

        if (empty($moduleName)) {
            throw new SugarApiExceptionMissingParameter('The module parameter is missing');
        }
        if (empty($prospectListId)) {
            throw new SugarApiExceptionMissingParameter('The prospectlistId parameter is missing');
        }
        if (empty($recordIds)) {
            throw new SugarApiExceptionMissingParameter('The recordIds are missing');
        }

        $targetList = new ProspectListsService();
        $response = $targetList->addRecordsToProspectList($moduleName, $prospectListId, $recordIds);

        if($response === false) {
            throw new SugarApiExceptionNotFound('Could not find parent record ' . $prospectListId . ' in module ' . $moduleName);
        }

        return $response;
    }
}
