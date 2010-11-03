<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class TaskHooks {

/**
 * task_before_save
 * 
 * @param $bean SugarBean instance for the Task
 * @param $event The event that triggered the logic hook
 * @param $arguments The optional arguments for the logic hook
 */
function task_before_save(&$bean, $event, $arguments) {
            //Check three conditions
            // 1) The bean has a parent id set (related to)
            // 2) The task type custom field is set to 'eng_code_review'
            // 3) The parent type is a Bug record (so we can retrieve the bug name and number)
            if(isset($bean->parent_id) && (isset($bean->task_type_c) && $bean->task_type_c == 'eng_code_review') && isset($bean->parent_type) && $bean->parent_type == 'Bugs') {
               require_once('include/utils.php');
               $bug = loadBean($bean->parent_type);
               $bug->retrieve($bean->parent_id);
               //Create the new task name
               $bean->name = "Review Bug {$bug->bug_number} ({$bug->name})";
            }
}

}
?>
