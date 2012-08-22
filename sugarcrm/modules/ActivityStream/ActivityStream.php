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


/**
 * "Activity Stream" prototype using mysql.
 * It doesn't support 'related' activities yet.
 * 
 * @author hqi
 *
 */
class ActivityStream extends SugarBean {
    // activity types
    const ACTIVITY_TYPE_CREATE = 'created';
    const ACTIVITY_TYPE_UPDATE = 'updated';   
    const ACTIVITY_TYPE_DELETE = 'deleted'; 
    const ACTIVITY_TYPE_POST = 'posted';
    

    // common vars for sugar bean
    public $table_name = 'activity_stream';
    public $object_name = 'ActivityStream';
    public $module_dir = 'ActivityStream';
    public $new_schema = true;
    
    // db fields
    public $activity_id;
    public $target_id;
    public $target_module;
    public $activity_data;
    public $created_by;
    public $date_created;
        
    /**
     * Constructor
     */
    public function ActivityStream() {
        parent::SugarBean();       
    }
    
    /**
     * Creates a new comment for this activity
     * @param string $activityId
     * @param string $value comment body
     * @return bool query result
     */
    public function addComment($activityId, $value) {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityComments']['fields'];   
        $tableName = $dictionary['ActivityComments']['table'];      

        $values = array();
        $values['comment_id'] = $this->db->massageValue(create_guid(), $fieldDefs['comment_id']);
        $values['activity_id']= $this->db->massageValue($activityId, $fieldDefs['activity_id']);
        $values['value']= $this->db->massageValue($value, $fieldDefs['value']);
        $values['date_created'] = $this->db->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
        $values['created_by'] = $this->db->massageValue($current_user->id, $fieldDefs['created_by']); 
        
        $sql = "INSERT INTO ".$tableName;
        $sql .= "(".implode(",", array_keys($values)).") ";
        $sql .= "VALUES(".implode(",", $values).")"; 
        return $this->db->query($sql);         
    }
    
    /**
     * Returns an array of activities for a bean
     * @param string $targetModule module name
     * @param string $targetId bean id
     * @param integer $start offset
     * @param integer $numActivities number of activities should be returned
     * @para integer $numComments number of comments should be returned for each activity. 0: no comments; -1:all comments
     * @return array
     */
    public function getActivities($targetModule = null, $targetId = null, $start = 0, $numActivities = 20, $numComments = -1) {
        global $dictionary, $current_language;
        $tableName = $dictionary['ActivityStream']['table']; 
        $fieldDefs = $dictionary['ActivityStream']['fields'];        
        
        $activities = array();
              
        $where = '';
        if(!empty($targetModule)) {
            $where .= "target_module = '".$targetModule."'";
            if(!empty($targetId)) {
                $where .= " AND target_id = '".$targetId."'";
            }
        }
        
        if(!empty($where)) {
            $where = ' AND '.$where;
        }
        $sql = "SELECT a.activity_id, a.created_by, a.date_created,a.target_module,a.target_id,a.activity_data, users.first_name, users.last_name FROM activity_stream a, users where a.created_by = users.id ".$where. " ORDER BY a.date_created DESC LIMIT ".$start.", ".$numActivities;
        $result = $GLOBALS['db']->query($sql);
        
        if(!empty($result)) {
            $activityIds = array();
            
            while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                $row['activity_data'] = json_decode(html_entity_decode($row['activity_data']), true);
                $row['target_name'] = '';
                if(!empty($row['target_id'])) {
                    $bean = BeanFactory::getBean($row['target_module'], $row['target_id']);
                    $row['target_name'] = $bean->name;
                }
                else if(!empty($row['target_module'])) {
                    $bean = BeanFactory::getBean($row['target_module']);                    
                    $mod_strings = return_module_language($current_language, $bean->module_dir);
                    $row['target_name'] = $mod_strings['LBL_MODULE_NAME'];
                }
                $row['created_by_name'] = return_name($row, 'first_name', 'last_name');
                unset($row['first_name']);
                unset($row['last_name']);
                
                $activities[] = $row;
                $activityIds[] = $row['activity_id'];
            }
            
            if(!empty($activityIds)) {
                $comments = array();
                if($numComments != 0) {
                    $fieldDefs = $dictionary['ActivityComments']['fields'];
                    $tableName = $dictionary['ActivityComments']['table'];                
                    $sql = "SELECT c.comment_id, c.activity_id,c.value,c.created_by, c.date_created,users.first_name, users.last_name FROM activity_comments c, users WHERE c.activity_id in ('".implode("','",$activityIds)."') AND c.created_by = users.id ORDER BY c.date_created ASC".($numComments > 0 ? " LIMIT 0, ".$numComments : '');
                    $result = $GLOBALS['db']->query($sql);
                    
                    if(!empty($result)) {
                        while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                            $row['created_by_name'] = return_name($row, 'first_name', 'last_name');
                            unset($row['first_name']);
                            unset($row['last_name']);
                            $comments[$row['activity_id']][] = $row;
                        }
                    }
                }
                foreach($activities as &$activity) {
                    $activity['comments'] = isset($comments[$activity['activity_id']]) ? $comments[$activity['activity_id']] : array(); 
                }
            }
        }
        
        return $activities;    
    } 

    /**
     * Returns an array of values for a new activity record. Called by self::addActivity
     * @param SugarBean $bean
     * @param string $activityType 
     * @param array $activityData
     * @return array
     * @see ActivityStream::addActivity()
     */
    protected function getActivityValues($bean, $activityType, $activityData = array()) {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityStream']['fields'];
                
        $activityValues = array();
        $activityValues['activity_id'] = $GLOBALS['db']->massageValue(create_guid(), $fieldDefs['activity_id']);
        $activityValues['target_id']= $GLOBALS['db']->massageValue($bean->id, $fieldDefs['target_id']);
        $activityValues['target_module']= $GLOBALS['db']->massageValue($bean->module_name, $fieldDefs['target_module']);
        $activityData = json_encode(array('action'=>$activityType, 'data'=>$activityData));
        $activityValues['activity_data'] = $this->db->massageValue($activityData, $fieldDefs['activity_data']);  
        $activityValues['date_created'] = $GLOBALS['db']->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
        $activityValues['created_by'] = $GLOBALS['db']->massageValue($current_user->id, $fieldDefs['created_by']); 

        return $activityValues;
    }
    
    /**
     * Creates new activity. For update, it may create multiple activity records, one for each changed field
     *
     * @param $bean Sugarbean instance that was affected
     * @param string $activityType 'create', 'update', or 'delete'
     * @return bool query result or false
     *
     */
    public function addActivity($bean, $activityType) {
        global $dictionary, $current_language;
        $fieldDefs = $dictionary['ActivityStream']['fields'];
        $tableName = $dictionary['ActivityStream']['table'];
        $values = array();
        
        switch ($activityType) {
            case ActivityStream::ACTIVITY_TYPE_CREATE:
            case ActivityStream::ACTIVITY_TYPE_DELETE:
                $values[] = $this->getActivityValues($bean, $activityType);          
                break;
            case ActivityStream::ACTIVITY_TYPE_UPDATE:
                $dataChanges = $GLOBALS['db']->getDataChanges($bean, 'activity');
                $dataChanges = array_values($dataChanges);
                $fieldDefs = $bean->getFieldDefinitions();
                $mod_strings = return_module_language($current_language, $bean->module_dir);                
                foreach($dataChanges as &$dataChange) {
                    $dataChange['field_name'] = str_replace(":","",$mod_strings[$fieldDefs[$dataChange['field_name']]['vname']]);
                }
                $values[] = $this->getActivityValues($bean, $activityType, $dataChanges);
                break;
            default:
                return false;
        }
   
        if(!empty($values)) {
            $valueStrings = array();
            $fieldNames = array();
            
            foreach($values as $value) {
                if(empty($fieldNames)) {
                    $fieldNames = array_keys($value);
                }
                $valueStrings[] = "(".implode(",", $value).")";
            }
            
            $valueString = '';
             
            if(!empty($valueStrings)) {
                $valueString = implode(",", $valueStrings);
            }
            
            if(!empty($valueString)) {
                $sql = "INSERT INTO ".$tableName." (".implode(",", $fieldNames).") VALUES ".$valueString;
                return $GLOBALS['db']->query($sql);
            }
        }

        return false;
    } 

    /**
     * Creates new post. 
     *
     * @param string $post_body 
     * @param string $targetModule module name
     * @param string $targetId bean id
     * @return bool query result or false
     *
     */
    public function addPost($targetModule, $targetId, $postBody) {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityStream']['fields'];   
        $tableName = $dictionary['ActivityStream']['table'];      

        $values = array();
        $values['activity_id'] = $this->db->massageValue(create_guid(), $fieldDefs['activity_id']);
        $values['target_module']= $this->db->massageValue($targetModule, $fieldDefs['target_module']);
        $values['target_id']= $this->db->massageValue($targetId, $fieldDefs['target_id']);
        $activityData = json_encode(array('action'=>self::ACTIVITY_TYPE_POST,'value'=>$postBody));
        $values['activity_data']= $this->db->massageValue($activityData, $fieldDefs['activity_data']);
        $values['date_created'] = $this->db->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
        $values['created_by'] = $this->db->massageValue($current_user->id, $fieldDefs['created_by']); 
        
        $sql = "INSERT INTO ".$tableName;
        $sql .= "(".implode(",", array_keys($values)).") ";
        $sql .= "VALUES(".implode(",", $values).")"; 
        return $this->db->query($sql);  
    }    
}

?>