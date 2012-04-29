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

require_once('include/api/ModuleApi.php');

class RelateRecordApi extends ModuleApi {
    public function registerApiRest() {
        return array(
            'fetchRelatedRecord' => array(
                'reqType'   => 'GET',
                'path'      => array('<module>','?',     'link','?',        '?'),
                'pathVars'  => array('module',  'record','',    'link_name','remote_id'),
                'method'    => 'getRelatedRecord',
                'shortHelp' => 'Fetch a single record related to this module',
                'longHelp'  => 'include/api/help/getRelatedRecord.html',
            ),
            'createRelatedRecord' => array(
                'reqType'   => 'POST',
                'path'      => array('<module>','?',     'link','?'),
                'pathVars'  => array('module',  'record','',    'link_name'),
                'method'    => 'createRelatedRecord',
                'shortHelp' => 'Create a single record and relate it to this module',
                'longHelp'  => 'include/api/help/createRelatedRecord.html',
            ),
            'createRelatedLink' => array(
                'reqType'   => 'POST',
                'path'      => array('<module>','?',     'link','?'        ,'?'),
                'pathVars'  => array('module',  'record','',    'link_name','remote_id'),
                'method'    => 'createRelatedLink',
                'shortHelp' => 'Relates an existing record to this module',
                'longHelp'  => 'include/api/help/createRelatedLink.html',
            ),
            'updateRelatedLink' => array(
                'reqType'   => 'PUT',
                'path'      => array('<module>','?',     'link','?'        ,'?'),
                'pathVars'  => array('module',  'record','',    'link_name','remote_id'),
                'method'    => 'updateRelatedLink',
                'shortHelp' => 'Updates relationship specific information ',
                'longHelp'  => 'include/api/help/updateRelatedLink.html',
            ),
            'deleteRelatedLink' => array(
                'reqType'   => 'DELETE',
                'path'      => array('<module>','?'     ,'link','?'        ,'?'),
                'pathVars'  => array('module'  ,'record',''    ,'link_name','remote_id'),
                'method'    => 'deleteRelatedLink',
                'shortHelp' => 'Deletes a relationship between two records',
                'longHelp'  => 'include/api/help/deleteRelatedLink.html',
            ),
        );
    }

    protected function checkRelatedSecurity($api, $args, $primaryBean, $securityTypeLocal='view', $securityTypeRemote='view') {
        if ( ! $api->security->canAccessModule($primaryBean,$securityTypeLocal) ) {
            throw new SugarApiExceptionNotAuthorized('No access to view primaryBeans for module: '.$args['module']);
        }
        // Load up the relationship
        $linkName = $args['link_name'];
        if ( ! $primaryBean->load_relationship($linkName) ) {
            // The relationship did not load, I'm guessing it doesn't exist
            throw new SugarApiExceptionNotFound('Could not find a relationship named: '.$args['link_name']);
        }
        // Figure out what is on the other side of this relationship, check permissions
        $linkModuleName = $primaryBean->$linkName->getRelatedModuleName();
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if ( ! $api->security->canAccessModule($linkSeed,$securityTypeRemote) ) {
            throw new SugarApiExceptionNotAuthorized('No access to view primaryBeans for module: '.$linkModuleName);
        }

        return array($linkName, $linkSeed);
        
    }

    protected function getRelatedFields($api, $args, $primaryBean, $linkName) {
        $relatedFields = $primaryBean->$linkName->getRelatedFields();
        $relatedData = array();
        if ( is_array($relatedFields) ) {
            foreach ( $relatedFields as $fieldName => $fieldParams ) {
                if ( isset($args[$fieldName]) ) {
                    $relatedData[$fieldName] = $args[$fieldName];
                }
            }
        }

        
        return $relatedData;
    }

    protected function formatNearAndFarRecords($api, $args, $primaryBean, $relatedBean, $linkName, $relatedData = array()) {
        $recordArray = $this->formatBean($api, $args, $primaryBean);
        $relatedArray = $this->formatBean($api, $args, $relatedBean);

        // TODO: When the related data is fixed and we can fetch it from the link class, replace this with that
        foreach ( $relatedData as $key => $value ) {
            $relatedArray[$key] = $value;
        }
        
        return array('record'=>$recordArray,
                     'related_record'=>$relatedArray);
    }


    function getRelatedRecord($api, $args) {
        $primaryBean = $this->loadBean($api, $args);
        
        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $relatedBean->retrieve($args['remote_id']);

        return $this->formatBean($api, $args, $relatedBean);
        
    }

    function createRelatedRecord($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','create');

        if ( isset($args['id']) ) {
            $relatedBean->new_with_id = true;
        }

        $id = $this->updateBean($relatedBean, $api, $args);

        $relatedData = $this->getRelatedFields($api, $args, $primaryBean, $linkName);
        
        $primaryBean->$linkName->add(array($relatedBean),$relatedData);

        return $this->formatNearAndFarRecords($api,$args,$primaryBean,$relatedBean,$linkName,$relatedData);
    }

    function createRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $relatedBean->retrieve($args['remote_id']);
        
        $relatedData = $this->getRelatedFields($api, $args, $primaryBean, $linkName);
        
        $primaryBean->$linkName->add(array($relatedBean),$relatedData);

        return $this->formatNearAndFarRecords($api,$args,$primaryBean,$relatedBean,$linkName,$relatedData);
    }


    function updateRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','edit');

        $relatedBean->retrieve($args['remote_id']);

        $id = $this->updateBean($relatedBean, $api, $args);

        $relatedData = $this->getRelatedFields($api, $args, $primaryBean, $linkName);
        
        $primaryBean->$linkName->add(array($relatedBean),$relatedData);

        return $this->formatNearAndFarRecords($api,$args,$primaryBean,$relatedBean,$linkName,$relatedData);
    }

    function deleteRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $relatedBean->retrieve($args['remote_id']);

        $primaryBean->$linkName->delete($primaryBean->id,$relatedBean);

        return $this->formatNearAndFarRecords($api,$args,$primaryBean,$relatedBean,$linkName);
    }


}
