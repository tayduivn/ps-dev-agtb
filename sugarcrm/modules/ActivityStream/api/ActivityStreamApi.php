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
require_once("include/api/ListApi.php");

class ActivityStreamApi extends ListApi {
    public function registerApiRest() {
        return array(
            'getBeanActivities' => array(
                'reqType' => 'GET',
                'path' => array('ActivityStream', '<module>','?'),
                'pathVars' => array('','module','id'),
                'method' => 'getActivities',
            ),
            'getModuleActivities' => array(
                'reqType' => 'GET',
                'path' => array('ActivityStream', '<module>'),
                'pathVars' => array('','module'),
                'method' => 'getActivities',
            ),
            'getAllActivities' => array(
                'reqType' => 'GET',
                'path' => array('ActivityStream'),
                'pathVars' => array(''),
                'method' => 'getActivities',
                ),
            'postAll' => array(
                'reqType' => 'POST',
                'path' => array('ActivityStream'),
                'pathVars' => array(''),
                'method' => 'handlePost',
            ),
            'postModule' => array(
                'reqType' => 'POST',
                'path' => array('ActivityStream', '<module>'),
                'pathVars' => array('','module'),
                'method' => 'handlePost',
            ),
            'postBean' => array(
                'reqType' => 'POST',
                'path' => array('ActivityStream', '<module>','?'),
                'pathVars' => array('','module','id'),
                'method' => 'handlePost',
            ),
            'deleteRecord' => array(
                    'reqType' => 'DELETE',
                    'path' => array('ActivityStream','?','?'),
                    'pathVars' => array('','module', 'id'),
                    'method' => 'deleteRecord',
            ),                                
        );
    }

    public function getActivities($api, $args) {
        $seed = BeanFactory::getBean('ActivityStream');
        $targetModule = !empty($args['module']) ? $args['module'] : '';
        $targetId = !empty($args['id']) ? $args['id'] : '';
        $options = $this->parseArguments($api, $args, $seed);
        $activities = $seed->getActivities($targetModule, $targetId, $options);

        if($activities !== false) {
            $nextOffset = count($activities) < $options['limit'] ? -1 : $options['offset'] + count($activities);
            return array('next_offset'=>$nextOffset,'records'=>$activities);
        }
        else {
            return false;
        }
    }

    public function handlePost($api, $args) {
        $seed = BeanFactory::getBean('ActivityStream');
        $targetModule = isset($args['module']) ? $args['module'] : '';
        $targetId = isset($args['id']) ? $args['id'] : '';
        $value = isset($args['value']) ? $args['value'] : '';

        if($targetModule == "ActivityStream") {
            // Make sure we have a valid activity id
            if(empty($targetId) || !$seed->retrieve($targetId, true, false)) {
                return false;
            }

            return $seed->addComment($value);
        }
        else {
            return $seed->addPost($targetModule, $targetId, $value);
        }
    }

    public function deleteRecord($api, $args) {
        $module = isset($args['module']) ? $args['module'] : '';
        $id = isset($args['id']) ? $args['id'] : '';
        
        if(!in_array($module, array('ActivityStream', 'ActivityComments')) || empty($id)) {
            return false;
        }
        
        $seed = BeanFactory::getBean('ActivityStream');
        return $module == 'ActivityStream' ? $seed->deletePost($id) : $seed->deleteComment($id);
    }
    
    protected function parseArguments($api, $args, $seed) {
        // options supported: limit, offset (no 'end'), filter ('favorites', 'myactivities')
        $options = parent::parseArguments($api, $args, $seed);
        if(isset($args['filter']) && in_array($args['filter'], array('favorites', 'myactivities'))) {
            $options['filter'] = $args['filter'];
        }
        return $options;
    }
}
