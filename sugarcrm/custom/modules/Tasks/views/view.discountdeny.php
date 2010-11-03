<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Tasks/Task.php');

class TasksViewDiscountdeny extends SugarView
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
        if (empty($_REQUEST['record'])) {
            sugar_die('No record ID passed.');
        }

        global $current_user, $beanList;

        $focus = new Task();
        $focus->retrieve($_REQUEST['record']);

        require_once(dirname(__FILE__) . "/../../../si_custom_files/MoofCartHelper.php");
        // get the proper class loaded
        // make sure that the current user is allowed to view this box
        $allowedList = MoofCartHelper::$taskApprovalChain[$focus->discount_chain_c];

        if (in_array($current_user->id, $allowedList)) {
            $focus->discount_denied_c = $current_user->id;
            $focus->status = "Completed";
            $focus->assigned_user_id = $focus->discount_requested_by_id_c;

            $obj = new $beanList[$focus->parent_type];
            $obj->retrieve($focus->parent_id);

            foreach ($obj->field_name_map as $key => $field) {
                if (strrpos($key, 'discount_') === 0) {
                    if (is_null($field['default'])) {
                        $field['default'] = '';
                    }
                    $obj->$key = $field['default'];
                }
            }

            $obj->discount_approval_status_c = "Discount Denied by " . $current_user->name;

            $focus->description = $obj->discount_approval_status_c;

            $obj->save(false);
            $focus->save(false);
        }

        header("Location: /index.php?module=Tasks&action=EditView&record=" . $focus->id);

    }

}