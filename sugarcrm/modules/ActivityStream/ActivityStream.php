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
    const ACTIVITY_TYPE_CREATE = 'create';
    const ACTIVITY_TYPE_UPDATE = 'update';   
    const ACTIVITY_TYPE_DELETE = 'delete'; 

    // common vars for sugar bean
    var $table_name = 'activity_stream';
    var $object_name = 'ActivityStream';
    var $module_dir = 'ActivityStream';
    var $new_schema = true;
    
    // db fields
    var $id;
    var $target_id;
    var $target_module;
    var $activity_data;
    var $created_by;
    var $date_created;
    var $deleted;
        
    /**
     * Constructor
     */
    function ActivityStream() {
        parent::SugarBean();       
    }
    
    /**
     * Creates a new comment for this activity
     * @param string $commentBody comment body
     * @return bool query result
     */
    public function addComment($commentBody) {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityComments']['fields'];   
        $tableName = $dictionary['ActivityComments']['table'];      

        $values = array();
        $values['id'] = $this->db->massageValue(create_guid(), $fieldDefs['id']);
        $values['activity_id']= $this->db->massageValue($this->id, $fieldDefs['activity_id']);
        $values['comment_body']= $this->db->massageValue($commentBody, $fieldDefs['comment_body']);
        $values['date_created'] = $this->db->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
        $values['created_by'] = $this->db->massageValue($current_user->id, $fieldDefs['created_by']); 
        
        $sql = "INSERT INTO ".$tableName;
        $sql .= "(".implode(",", array_keys($values)).") ";
        $sql .= "VALUES(".implode(",", $values).")"; 
        return $this->db->query($sql);         
    }
    
    /**
     * Returns an array of comments for this activity
     * @param integer $start offset
     * @param integer $numComments number of comments should be returned
     * @return array
     */
    public function getComments($start = 0, $numComments = 20) {
        global $dictionary;
        $fieldDefs = $dictionary['ActivityComments']['fields'];
        $tableName = $dictionary['ActivityComments']['table'];        
        
        $comments = array();   
             
        $sql = "SELECT ".implode(",", array_keys($fieldDefs)). " FROM ".$tableName." WHERE activity_id ='".$this->id."' ORDER BY date_created ASC LIMIT ".$start.", ".$numComments;
        $result = $this->db->query($sql);
        
        if(!empty($result)) {
            while(($row=$this->db->fetchByAssoc($result)) != null) {
                $comments[] = $row;
            }    
        }
        
        return $comments;
    }
    
    /**
     * Returns an array of activities for a bean
     * @param SugarBean $bean gets activities for this bean
     * @param integer $start offset
     * @param integer $numActivities number of activities should be returned
     * @para integer $numComments number of comments should be returned for each activity
     * @return array
     */
    public function getActivities($bean, $start = 0, $numActivities = 20, $numComments = 0) {
        global $dictionary;
        $tableName = $dictionary['ActivityStream']['table']; 
        $fieldDefs = $dictionary['ActivityStream']['fields'];        
        
        $activities = array();
              
        $sql = "SELECT ".implode(",",array_keys($fieldDefs))." FROM ".$tableName." WHERE target_module ='".$bean->module_name."' AND target_id = '".$bean->id."' order by DATE_CREATED DESC LIMIT ".$start.", ".$numActivities;
        $result = $GLOBALS['db']->query($sql);
        
        if(!empty($result)) {
            $activityIds = array();
            
            while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                $activities[] = $row;
                $activityIds[] = $row['id'];
            }
            
            if(!empty($activityIds)) {
                $comments = array();
                if($numComments != 0) {
                    $fieldDefs = $dictionary['ActivityComments']['fields'];
                    $tableName = $dictionary['ActivityComments']['table'];                
                    $sql = "SELECT ".implode(",", array_keys($fieldDefs)). " FROM ".$tableName." WHERE activity_id in ('".implode("','",$activityIds)."') ORDER BY date_created ASC".($numComments > 0 ? " LIMIT 0, ".$numComments : '');
                    $result = $GLOBALS['db']->query($sql);
                    
                    if(!empty($result)) {
                        while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null) {
                            $comments[$row['activity_id']][] = $row;
                        }
                    }
                }
                foreach($activities as &$activity) {
                    $activity['comments'] = isset($comments[$activity['id']]) ? $comments[$activity['id']] : array(); 
                }
            }
        }
        
        return $activities;    
    } 

    /**
     * Returns an array of values for a new activity record. Called by self::addActivity
     * @param SugarBean $bean
     * @param string $activityType 
     * @param string $fieldName
     * @param string $beforeValue
     * @param string $afterValue
     * @return array
     * @see ActivityStream::addActivity()
     */
    protected function getActivityValues($bean, $activityType, $fieldName = '', $beforeValue = '', $afterValue = '') {
        global $current_user, $dictionary;
        $fieldDefs = $dictionary['ActivityStream']['fields'];
                
        $activityValues = array();
        $activityValues['id'] = $GLOBALS['db']->massageValue(create_guid(), $fieldDefs['id']);
        $activityValues['target_id']= $GLOBALS['db']->massageValue($bean->id, $fieldDefs['target_id']);
        $activityValues['target_module']= $GLOBALS['db']->massageValue($bean->module_name, $fieldDefs['target_module']);
        $activityData = json_encode(array('action'=>$activityType, 'field_name'=>$fieldName, 'before_value'=>$beforeValue, 'after_value'=>$afterValue));
        $activityValues['activity_data'] = $GLOBALS['db']->massageValue($activityData, $fieldDefs['activity_data']);
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
        global $dictionary;
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
                if(!empty($dataChanges)) {
                    foreach($dataChanges as $dataChange) {
                        $values[] = $this->getActivityValues($bean, $activityType, $dataChange['field_name'], $dataChange['before'], $dataChange['after']);
                    }    
                }
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
}

?>