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


    /**
     * Fetches data from the $args array and updates the bean with that data
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how security is applied
     * @param $args array The arguments array passed in from the API
     * @param $primaryBean SugarBean The near side of the link
     * @param $securityTypeLocal string What ACL to check on the near side of the link
     * @param $securityTypeRemote string What ACL to check on the far side of the link
     * @return array Two elements: The link name, and the SugarBean of the far end
     */
    protected function checkRelatedSecurity(ServiceBase $api, $args, SugarBean $primaryBean, $securityTypeLocal='view', $securityTypeRemote='view') {
        if ( empty($primaryBean) ) {
            throw new SugarApiExceptionNotFound('Could not find the primary bean');
        }
        if ( ! $primaryBean->ACLAccess($securityTypeLocal) ) {
            throw new SugarApiExceptionNotAuthorized('No access to '.$securityTypeLocal.' records for module: '.$args['module']);
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

        // FIXME: No create ACL yet
        if ( $securityTypeRemote == 'create' ) { $securityTypeRemote = 'edit'; }

        if ( ! $linkSeed->ACLAccess($securityTypeRemote) ) {
            throw new SugarApiExceptionNotAuthorized('No access to '.$securityTypeRemote.' records for module: '.$linkModuleName);
        }

        return array($linkName, $linkSeed);
        
    }

    /**
     * This function is used to popluate an fields on the relationship from the request
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how security is applied
     * @param $args array The arguments array passed in from the API
     * @param $primaryBean SugarBean The near side of the link
     * @param $linkName string What is the name of the link field that you want to get the related fields for
     * @return array A list of the related fields pulled out of the $args array
     */
    protected function getRelatedFields(ServiceBase $api, $args, SugarBean $primaryBean, $linkName) {
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

    /**
     * This function is here temporarily until the Link2 class properly handles these for the non-subpanel requests
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how security is applied
     * @param $args array The arguments array passed in from the API
     * @param $primaryBean SugarBean The near side of the link
     * @param $relatedBean SugarBean The far side of the link
     * @param $linkName string What is the name of the link field that you want to get the related fields for
     * @param $relatedData array The data for the related fields (such as the contact_role in opportunities_contacts relationship)
     * @return array Two elements, 'record' which is the formatted version of $primaryBean, and 'related_record' which is the formatted version of $relatedBean
     */
    protected function formatNearAndFarRecords(ServiceBase $api, $args, SugarBean $primaryBean, $relatedArray = array()) {
        $recordArray = $this->formatBean($api, $args, $primaryBean);
        if (empty($relatedArray))
            $relatedArray = $this->getRelatedRecord($api, $args);

        return array(
            'record'=>$recordArray,
            'related_record'=>$relatedArray
        );
    }


    function getRelatedRecord($api, $args) {
        $primaryBean = $this->loadBean($api, $args);
        
        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $related = array_values($primaryBean->$linkName->getBeans(array(
            'where' => array(
                'lhs_field' => 'id',
                'operator' => '=',
                'rhs_value' => $args['remote_id'],
            )
        )));
        if ( empty($related[0]->id) ) {
            // Retrieve failed, probably doesn't have permissions
            throw new SugarApiExceptionNotFound('Could not find the related bean');
        }

        return $this->formatBean($api, $args, $related[0]);
        
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

        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        $args['remote_id'] = $relatedBean->id;
        return $this->formatNearAndFarRecords($api,$args,$primaryBean);
    }

    function createRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $relatedBean->retrieve($args['remote_id']);
        if ( empty($relatedBean->id) ) {
            // Retrieve failed, probably doesn't have permissions
            throw new SugarApiExceptionNotFound('Could not find the related bean');
        }

        $relatedData = $this->getRelatedFields($api, $args, $primaryBean, $linkName);
        $primaryBean->$linkName->add(array($relatedBean),$relatedData);

        return $this->formatNearAndFarRecords($api,$args,$primaryBean);
    }


    function updateRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','edit');

        $relatedBean->retrieve($args['remote_id']);
        if ( empty($relatedBean->id) ) {
            // Retrieve failed, probably doesn't have permissions
            throw new SugarApiExceptionNotFound('Could not find the related bean');
        }

        $id = $this->updateBean($relatedBean, $api, $args);

        $relatedData = $this->getRelatedFields($api, $args, $primaryBean, $linkName);
        $primaryBean->$linkName->add(array($relatedBean),$relatedData);
        
        //Clean up any hanging related records.
        SugarRelationship::resaveRelatedBeans();

        return $this->formatNearAndFarRecords($api,$args,$primaryBean);
    }

    function deleteRelatedLink($api, $args) {
        $primaryBean = $this->loadBean($api, $args);

        list($linkName, $relatedBean) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view','view');

        $relatedBean->retrieve($args['remote_id']);
        if ( empty($relatedBean->id) ) {
            // Retrieve failed, probably doesn't have permissions
            throw new SugarApiExceptionNotFound('Could not find the related bean');
        }

        $primaryBean->$linkName->delete($primaryBean->id,$relatedBean);

        //Because the relationship is now deleted, we need to pass the $relatedBean data into formatNearAndFarRecords
        return $this->formatNearAndFarRecords($api,$args,$primaryBean, $this->formatBean($api, $args, $relatedBean));
    }


}
