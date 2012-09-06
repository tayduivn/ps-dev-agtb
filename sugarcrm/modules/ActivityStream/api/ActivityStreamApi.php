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
        $value = isset($args['value']['text']) ? $args['value']['text'] : '';
        $attachments = isset($args['value']['attachments']) ? $args['value']['attachments'] : array();
        $post_id = false;

        if($targetModule == "ActivityStream") {
            // Make sure we have a valid activity id
            if(empty($targetId) || !$seed->retrieve($targetId, true, false)) {
                return false;
            }

            $post_id = $seed->addComment($value);
            $parent_type = 'ActivityComments';
        }
        else {
            $post_id = $seed->addPost($targetModule, $targetId, $value);
            $parent_type = 'ActivityStream';
        }

        // If creating the post was successful, add the attachments.
        if($post_id) {
            require_once('include/upload_file.php');
            if(!in_array("upload", stream_get_wrappers())) {
                stream_wrapper_register("upload", "UploadStream");
            }
            foreach($attachments as $attachment) {
                $arr = preg_split("/[:;,]/", $attachment['data']);
                // Using BeanFactory returns an stdClass, not a note.
                $attachment_seed = new Note();
                $attachment_seed->parent_id = $post_id;
                $attachment_seed->parent_type = $parent_type;
                $attachment_seed->filename = $attachment['name'];
                $attachment_seed->file_mime_type = $arr[1];
                $attachment_seed->safeAttachmentName();
                $attachment_seed->save();
                if(!file_put_contents("upload://".$attachment_seed->id, base64_decode($arr[3]))) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return $post_id;
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
