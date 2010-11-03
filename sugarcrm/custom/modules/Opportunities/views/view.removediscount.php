<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Tasks/Task.php');

class OpportunitiesViewRemovediscount extends SugarView
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::SugarView();
    }

    public function display()
    {
        global $current_user;
        
        if (empty($_REQUEST['record'])) {
            sugar_die('No record ID passed.');
        }

        $focus = new Account();
        $focus->retrieve($_REQUEST['record']);

        foreach ($focus->field_name_map as $key => $field) {
            if (strrpos($key, 'discount_') === 0) {
                if(is_null($field['default'])) {
                    $field['default'] = '';
                }
                $focus->$key = $field['default'];
            }
        }
        $focus->save(false);

        // handle any tasks
        $tasks = $focus->get_linked_beans('tasks', 'Task');
        /**
         * @var $task Task
         */
        foreach($tasks as $task) {
            if($task->task_type_c == "discount_request" && $task->status != "Completed") {
                $task->status = "Completed";
                $task->description = "Discount Removed By " . $current_user->name;
                $task->assigned_user_id = $task->discount_requested_by_id_c;
                $task->save(false);
                break;
            }
        }

        header("Location: /index.php?module=Accounts&action=EditView&record=" . $focus->id);

    }

}