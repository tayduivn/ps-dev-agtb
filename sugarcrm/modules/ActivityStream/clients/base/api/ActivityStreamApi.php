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
require_once 'clients/base/api/ListApi.php';

class ActivityStreamApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'getTaggableBeans' => array(
                'reqType' => 'GET',
                'path' => array('ActivityStreamTags'),
                'pathVars' => array(''),
                'method' => 'getTags',
            ),
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

    public function getTags($api, $args)
    {
        // The values represent which field to check in order to make sure
        // that the bean is valid.
        $beans = array(
            "Users" => "first_name",
            "Contacts" => "first_name",
            "Opportunities" => "name",
            "Accounts" => "name"
        );
        $data = array();

        // TODO: Make this non-n^2 somehow.
        foreach ($beans as $bean => $check) {
            $seed = BeanFactory::getBean($bean);
            $full_list = $seed->get_full_list();
            foreach ($full_list as $result) {
                if (!empty($result->$check)) {
                    $data[] = array(
                        'module' => $bean,
                        'name' => $result->name,
                        'id' => $result->id,
                    );
                }
            }
        }

        return $data;
    }

    public function getActivities($api, $args)
    {
        $seed = BeanFactory::getBean('ActivityStream');
        $targetModule = !empty($args['module']) ? $args['module'] : '';
        $targetId = !empty($args['id']) ? $args['id'] : '';
        $options = $this->parseArguments($api, $args, $seed);

        return $seed->getActivities($targetModule, $targetId, $options);
    }

    public function handlePost($api, $args)
    {
        $seed = BeanFactory::getBean('ActivityStream');
        $targetModule = isset($args['module']) ? $args['module'] : '';
        $targetId = isset($args['id']) ? $args['id'] : '';
        $value = isset($args['value']) ? $args['value'] : '';

        if ($targetModule == "ActivityStream") {
            // Make sure we have a valid activity id
            if (empty($targetId) || !$seed->retrieve($targetId, true, false)) {
                return false;
            }

            return $seed->addComment($value);
        } else {
            return $seed->addPost($targetModule, $targetId, $value);
        }
    }

    public function deleteRecord($api, $args)
    {
        $module = isset($args['module']) ? $args['module'] : '';
        $id = isset($args['id']) ? $args['id'] : '';

        if (!in_array($module, array('ActivityStream', 'ActivityComments')) || empty($id)) {
            return false;
        }

        $seed = BeanFactory::getBean('ActivityStream');

        return $module == 'ActivityStream' ? $seed->deletePost($id) : $seed->deleteComment($id);
    }

    public function parseArguments($api, $args, $seed)
    {
        // options supported: limit, offset (no 'end'), filter ('favorites', 'myactivities'), link, parent_module, parent_id
        $options = parent::parseArguments($api, $args, $seed);
        if (isset($args['filter']) && in_array($args['filter'], array('favorites', 'myactivities'))) {
            $options['filter'] = $args['filter'];
        }
        if (!empty($args['limit'])) {
            $options['limit'] = $args['limit'];
        }
        // For related tabs
        if (!empty($args['link'])) {
            $options['link'] = $args['link'];
        }
        if (!empty($args['parent_module'])) {
            $options['parent_module'] = $args['parent_module'];
        }
        if (!empty($args['parent_id'])) {
            $options['parent_id'] = $args['parent_id'];
        }

        return $options;
    }
}
