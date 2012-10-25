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

include_once("include/Link2Tag.php");

/**
 * "Activity Stream" prototype using mysql.
 * It doesn't support 'related' activities yet.
 *
 */
class ActivityStream extends SugarBean {
    // activity types
    const ACTIVITY_TYPE_CREATE = 'created';
    const ACTIVITY_TYPE_RELATE = 'related';
    const ACTIVITY_TYPE_UPDATE = 'updated';
    const ACTIVITY_TYPE_POST = 'posted';


    // common vars for sugar bean
    public $table_name = 'activity_stream';
    public $object_name = 'ActivityStream';
    public $module_dir = 'ActivityStream';
    public $new_schema = true;

    // db fields
    public $id;
    public $target_id;
    public $target_module;
    public $activity_type;
    public $activity_data;
    public $created_by;
    public $date_created;
    public $deleted;
    //BEGIN SUGARCRM flav=pro ONLY
    public $team_id;
    public $team_set_id;    
    //END SUGARCRM flav=pro ONLY    

    /**
     * Constructor
     */
    public function ActivityStream() {
        parent::SugarBean();
    }

    /**
     * Creates a new comment for this activity
     * @param string $text comment text
     * @return bool query result
     */
    public function addComment($text) {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityComments']['fields'];
        $tableName = $dictionary['ActivityComments']['table'];

        $values = array();
        $id = create_guid();
        $values['id'] = $this->db->massageValue($id, $fieldDefs['id']);
        $values['activity_id']= $this->db->massageValue($this->id, $fieldDefs['activity_id']);
        $text = strip_tags($text);
        $values['value']= $this->db->massageValue(Link2Tag::convert($text), $fieldDefs['value']);
        $values['date_created'] = $this->db->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
        $values['created_by'] = $this->db->massageValue($current_user->id, $fieldDefs['created_by']);

        $sql = "INSERT INTO ".$tableName;
        $sql .= "(".implode(",", array_keys($values)).") ";
        $sql .= "VALUES(".implode(",", $values).")";
        return $this->db->query($sql,true) ? $id : false;
    }

    /**
     * Deletes a comment made by current user
     * @param string $commentId
     * @return bool query restult
     */
    public function deleteComment($commentId) {
        global $current_user, $dictionary;
        $tableName = $dictionary['ActivityComments']['table'];
        $sql = "UPDATE ".$tableName." SET deleted = 1 WHERE id = '".$commentId."' AND created_by = '".$current_user->id."'";
        return $this->db->query($sql,true);
    }

    /**
     * Creates new activity. For update, it may create multiple activity records, one for each changed field
     *
     * @param $bean Sugarbean instance that was affected
     * @param string $activityType 'create', 'update', or 'delete'
     * @return bool query result or false
     *
     */
    public function addUpdate($bean) {
        global $current_language;

        $dataChanges = $GLOBALS['db']->getDataChanges($bean, 'activity');
        $dataChanges = array_values($dataChanges);
        $fieldDefs = $bean->getFieldDefinitions();
        $mod_strings = return_module_language($current_language, $bean->module_dir);

        foreach($dataChanges as &$dataChange) {
            $fieldName = get_label($fieldDefs[$dataChange['field_name']]['vname'], $mod_strings);
            $dataChange['field'] = $dataChange['field_name'];
            $dataChange['field_name'] = str_replace(":","",$fieldName);
        }

        return $this->addActivity($bean, self::ACTIVITY_TYPE_UPDATE, $dataChanges);
    }

    /**
     * Creates new post.
     *
     * @param string $post_body
     * @param string $targetModule module name
     * @param string $targetId bean id
     * @param string $text posted text
     * @return bool query result or false
     *
     */
    public function addPost($targetModule, $targetId, $text
            //BEGIN SUGARCRM flav=pro ONLY
            ,$teamId = ''
            ,$teamSetId = ''
            //END SUGARCRM flav=pro ONLY            
            ) {
        global $current_user;        
        // This combination is not supportable
        if(empty($targetModule) && !empty($targetId)) {
            $GLOBALS['log']->debug("target_module cannot be empty when target_id is empty for activity post.");
            return false;
        }
        
        $bean = null;
        // Make sure targetModule and targetId are valid
        if(!empty($targetModule)) {
            $bean = BeanFactory::getBean($targetModule);

            if(empty($bean)) {
                $GLOBALS['log']->debug("target_module is invalid for activity post.");
                return false;
            }

            if(!empty($targetId) && !$bean->retrieve($targetId)) {
                $GLOBALS['log']->debug("target_id is invalid for activity post.");
                return false;
            }
        }

        $this->target_id = $targetId;
        $this->target_module = $targetModule;
        //BEGIN SUGARCRM flav=pro ONLY
        $this->team_id = $teamId;
        $this->team_set_id = empty($teamSetId) ? $teamId : $teamSetId;
        //END SUGARCRM flav=pro ONLY
        $text = strip_tags($text);
        $activityData = array('value'=>Link2Tag::convert($text));        
        $this->activity_data = json_encode($activityData);
        $this->activity_type = self::ACTIVITY_TYPE_POST;
        $this->date_created = TimeDate::getInstance()->nowDb();
        $this->created_by = $current_user->id;
        return $this->save();        
    }

    /**
     * Deletes a post made by current user
     * @param string $postId
     * @return bool query restult
     */
    public function deletePost($postId) {
        global $current_user;
        $sql = "UPDATE ".$this->getTableName()." SET deleted = 1 WHERE id = '".$postId."' AND created_by = '".$current_user->id."' AND activity_type = '".self::ACTIVITY_TYPE_POST."'";
        // Should we also delete comments or attachments for this post???
        return $this->db->query($sql,true);
    }

    /**
     * Creates a new relationship activity record.
     *
     * @param SugarBean $lhs
     * @param SugarBean $rhs
     * @return bool query result or false
     */
    public function addRelate($lhs, $rhs) {
        $activityData = array('relate_to'=>$rhs->module_dir, 'relate_id'=>$rhs->id, 'relate_name'=>$rhs->get_summary_text());
        return $this->addActivity($lhs, self::ACTIVITY_TYPE_RELATE, $activityData);
    }

    /**
     * Creates a new record activity.
     *
     * @param SugarBean $bean
     * @return bool query result or false
     */
    public function addCreate($bean) {
        $activityData = array();
        return $this->addActivity($bean, self::ACTIVITY_TYPE_CREATE, $activityData);
    }

    /**
     * Creates a new activity record.
     * @param SugarBean $bean
     * @param string $activityType
     * @param array $activityData
     * @return bool query result
     */
    protected function addActivity($bean, $activityType, $activityData = array()) {
        global $current_user;
        $this->target_id = $bean->id;
        $this->target_module = $bean->module_name;
        //BEGIN SUGARCRM flav=pro ONLY
        $this->team_id = $bean->team_id;
        $this->team_set_id = $bean->team_set_id;
        //END SUGARCRM flav=pro ONLY        
        $this->activity_data = json_encode($activityData);
        $this->activity_type = $activityType;
        $this->date_created = TimeDate::getInstance()->nowDb();
        $this->created_by = $current_user->id;
        // Needed for demo data.
        $this->set_created_by = false;
        if($activityType == self::ACTIVITY_TYPE_CREATE) {
            $this->created_by = $bean->created_by;
        }
        $this->updateLastActivityDate($bean, $this->date_created);
        return $this->save();
    }

    /**
     * Returns an array of activities for a bean
     * @param string $targetModule module name
     * @param string $targetId bean id
     * @param array $options offset, limit, num_comments ( 0: no comments; -1:all comments)
     * @return array
     */
    public function getActivities($targetModule, $targetId, $options = array()) {
        global $dictionary, $current_language, $current_user;
        $tableName = $dictionary['ActivityStream']['table'];
        $fieldDefs = $dictionary['ActivityStream']['fields'];
        
        // This combination is not supportable
        if(empty($targetModule) && !empty($targetId)) {
            $GLOBALS['log']->debug("target_module cannot be empty when target_id is.");
            return false;
        } 
              
        // Convert to int for security
        $start = isset($options['offset']) ? (int) $options['offset'] : 0;
        $numActivities = isset($options['limit']) ? (int) $options['limit'] : -1;
        $numComments = isset($options['num_comments']) ? (int) $options['num_comments'] : -1;
        $filter = isset($options['filter']) ? $options['filter'] : 'all';
        $link = isset($options['link']) ? $options['link'] : '';
        $parentModule = isset($options['parent_module']) ? $options['parent_module'] : '';
        $parentId = isset($options['parent_id']) ? $options['parent_id'] : '';       
        
        $activities = array();
        
        $select = 'a.id, a.created_by, a.date_created,a.target_module,a.target_id,a.activity_type,a.activity_data, u.first_name, u.last_name, u.picture as created_by_picture';
        $from = 'activity_stream a LEFT JOIN users u ON a.created_by = u.id ';
        $where = 'a.deleted = 0';
        $order = 'a.date_created DESC';
        $limit = '';

        //BEGIN SUGARCRM flav=pro ONLY
        // Team security
        $this->addVisibilityFrom($from, array('table_alias'=>'a'));
        //END SUGARCRM flav=pro ONLY
        
        // For related tab
        if(!empty($link)) {
            if(empty($parentModule) || empty($parentId)) {
                $GLOBALS['log']->debug("parent_module and parent_id cannot be empty for related tab.");
                return false;
            }
            
            $parentBean = BeanFactory::getBean($parentModule, $parentId, false);
            if(empty($parentBean)) {
                $GLOBALS['log']->debug("No parent bean found for related tab.");
                return false;
            }
            
            // Load up the relationship
            if (!$parentBean->load_relationship($link)) {
                // The relationship did not load
                $GLOBALS['log']->debug("Couldn't find relationship.");
                return false;
            }

            // Get related ids
            $from .= ' INNER JOIN ('.$parentBean->$link->getQuery().') r ON a.target_id = r.id'; 
        }
        
        if($targetModule == 'Users' && !empty($targetId)) {
            // On an user's profile page, we also want to show this user's activities
            $where .= " AND (a.created_by = ".$GLOBALS['db']->massageValue($targetId, $fieldDefs['created_by']) ." OR (a.target_module = ".$GLOBALS['db']->massageValue($targetModule, $fieldDefs['target_module'])." AND a.target_id = ".$GLOBALS['db']->massageValue($targetId, $fieldDefs['target_id'])."))";
        }
        else if(!empty($targetModule) && $targetModule != 'Home') { // Show all activities on Home page
            $where .= " AND ((a.target_module = ".$GLOBALS['db']->massageValue($targetModule, $fieldDefs['target_module']);
            if(!empty($targetId)) {
                $where .= " AND a.target_id = ".$GLOBALS['db']->massageValue($targetId, $fieldDefs['target_id']);
                $post_tag = "@[".$targetModule.":".$targetId."]";
                $where .= ") OR (a.activity_data LIKE '%".$post_tag."%'";
                $where .= ") OR (a.id IN (SELECT activity_id FROM activity_comments where value LIKE '%".$post_tag."%')";
            }
            $where .= "))";
        }

        if($filter == 'myactivities') {
            $where .= " AND a.created_by = '".$current_user->id."'";
        }
        else if($filter == 'favorites') {
            $from .= " INNER JOIN sugarfavorites f ON (a.target_module = f.module AND a.target_id = f.record_id)";
            $where .= " AND f.deleted = 0 AND f.created_by = '".$current_user->id."'";
        }

        if($numActivities > 0) {
            $limit = ' LIMIT '.$start. ', '.$numActivities;
        }

        $sql = "SELECT ".$select." FROM ".$from. " WHERE ".$where. " ORDER BY ".$order.$limit;
        $GLOBALS['log']->debug("Activity query: $sql");
        $result = $GLOBALS['db']->query($sql);

        if(!empty($result)) {
            while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                $row['activity_data'] = json_decode(from_html($row['activity_data']), true);

                // Check module/view access for target module
                if (!empty($row['target_module']) && ACLController::moduleSupportsACL($row['target_module']) && 
                        !ACLController::checkAccess($row['target_module'], 'view', $row['created_by'] == $current_user->id) && 
                        !ACLController::checkAccess($row['target_module'], 'list', $row['created_by'] == $current_user->id)){
                    // User has no access to the module.
                    unset($row['target_module']);
                    unset($row['target_id']); 
                    if($row['activity_type'] == self::ACTIVITY_TYPE_UPDATE) {
                        $row['activity_data'] = array();
                    }               
                }  
                                            
                // Check module/view access for relate module
                if ($row['activity_type'] == self::ACTIVITY_TYPE_RELATE && !empty($row['activity_data']) && 
                        ACLController::moduleSupportsACL($row['activity_data']['relate_to']) && 
                        !ACLController::checkAccess($row['activity_data']['relate_to'], 'view', $row['created_by'] == $current_user->id) && 
                        !ACLController::checkAccess($row['activity_data']['relate_to'], 'list', $row['created_by'] == $current_user->id)){
                    // User has no access to the module.
                    $row['activity_data'] = array();
                } 
                            
                if(!empty($row['target_id'])) {
                    $bean = BeanFactory::getBean($row['target_module'], $row['target_id']);
                    if(!empty($bean)) {
                        $row['target_name'] = $bean->get_summary_text();
                        if($row['activity_type'] == self::ACTIVITY_TYPE_UPDATE) {
                            foreach($row['activity_data'] as &$update) {
                                // Check field access
                                if(!$bean->ACLFieldAccess($update['field'])) {
                                    $update['before'] = '';
                                    $update['after'] = '';
                                    // We need to tell frontend this field is not accessible
                                    $update['accessible'] = false;
                                }
                                else {
                                    $update['accessible'] = true;
                                }
                            }
                        }                        
                    } else {
                        // We don't have access to the target.
                        unset($row['target_module']);
                        unset($row['target_id']);
                        if($row['activity_type'] == self::ACTIVITY_TYPE_UPDATE) {
                            $row['activity_data'] = array();
                        }                        
                    }
                }
                else if(!empty($row['target_module'])) {
                    $bean = BeanFactory::getBean($row['target_module']);
                    if(!empty($bean->module_dir)) {
                        $mod_strings = return_module_language($current_language, $bean->module_dir);
                        if(!empty($mod_strings['LBL_MODULE_NAME'])) {
                            $row['target_name'] = $mod_strings['LBL_MODULE_NAME'];
                        }
                    }
                }
                $row['created_by_name'] = return_name($row, 'first_name', 'last_name');
                unset($row['first_name']);
                unset($row['last_name']);

                $activities[$row['id']] = $row;
            }

            if(!empty($activities)) {
                $comments = array();
                $commentNotes = array();

                // Get comments for returned activities
                if($numComments != 0) {
                    $fieldDefs = $dictionary['ActivityComments']['fields'];
                    $tableName = $dictionary['ActivityComments']['table'];
                    $activityIds = implode("','",array_keys($activities));
                    $sql = "SELECT c.id, c.activity_id,c.value,c.created_by, c.date_created,u.first_name, u.last_name, u.picture as created_by_picture FROM activity_comments c, users u WHERE c.activity_id in ('".$activityIds."') AND c.deleted = 0 AND c.created_by = u.id ORDER BY c.date_created ASC".($numComments > 0 ? " LIMIT 0, ".$numComments : '');
                    $result = $GLOBALS['db']->query($sql);

                    if(!empty($result)) {
                        $commentIds = array();
                        while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                            $row['created_by_name'] = return_name($row, 'first_name', 'last_name');
                            unset($row['first_name']);
                            unset($row['last_name']);
                            $row['value'] = from_html($row['value']);
                            // Group comments by activity id
                            $comments[$row['activity_id']][] = $row;
                            $commentIds[] = $row['id'];
                        }
                        // Get attachments for comments
                        if(!empty($commentIds)) {
                            $commentNotes = $this->getNotes('ActivityComments', $commentIds);
                        }
                    }
                }

                $activityNotes = $this->getNotes('ActivityStream', array_keys($activities));

                // Get attachments for activities
                foreach($activities as &$activity) {
                    $activity['comments'] = isset($comments[$activity['id']]) ? $comments[$activity['id']] : array();
                    foreach($activity['comments'] as &$comment) {
                        $comment['notes'] = isset($commentNotes[$comment['id']]) ? $commentNotes[$comment['id']] : array();
                    }
                    $activity['notes'] = isset($activityNotes[$activity['id']]) ? $activityNotes[$activity['id']] : array();
                }
            }
        }
        
        $activities = array_values($activities);
        $nextOffset = count($activities) < $options['limit'] ? -1 : $options['offset'] + count($activities);
        $list = array('next_offset'=>$nextOffset,'records'=>$activities);
        return $list;
    }

    /**
     * Gets notes attached to posts or comments
     * @param string $parentType 'ActivityStream' or 'ActivityComments'
     * @param array $parentIds activity or comment ids
     * @return array
     */
    protected function getNotes($parentType, $parentIds) {
        $notes = array();
        $sql = "SELECT n.id,n.parent_type,n.parent_id,n.name,n.description,n.created_by,n.date_entered,n.file_mime_type,n.filename,u.first_name, u.last_name FROM notes n, users u WHERE n.parent_type='".$parentType."' and parent_id in ('".implode("','",$parentIds)."') AND n.created_by = u.id AND n.deleted = 0 ORDER BY n.file_mime_type, n.date_entered ASC";
        $result = $GLOBALS['db']->query($sql);

        if(!empty($result)) {
            while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                $row['created_by_name'] = return_name($row, 'first_name', 'last_name');
                unset($row['first_name']);
                unset($row['last_name']);
                $notes[$row['parent_id']][] = $row;
            }
        }
        return $notes;

    }

    /**
     * Update a bean's 'last_activity_date' field
     * @param $bean
     * @param $date
     */
    protected function updateLastActivityDate($bean, $date){
        if(!empty($bean) && $bean->field_defs['last_activity_date'])$GLOBALS['db']->query("UPDATE " . $bean->table_name . " SET last_activity_date='" . $date .  "' WHERE id= '{$bean->id}'");
    }

    /**
     * This function will remove our video or image tags so we need to disable it.
     * @see SugarBean::cleanBean()
     */
    function cleanBean() {
    }
}

?>