<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
require_once 'include/SugarObjects/templates/basic/Basic.php';

class UserSignature extends Basic
{
    public $id;
    public $date_entered;
    public $date_modified;
    public $deleted;
    public $user_id;
    public $name;
    public $signature;
    public $table_name = 'users_signatures';
    public $module_dir = 'UserSignatures';
    public $object_name = 'UserSignature';
    public $disable_custom_fields = true;
    //BEGIN SUGARCRM flav=pro ONLY
    public $disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY
    public $set_created_by = false;

    /**
     * Override's SugarBean's
     */
    public function get_list_view_data()
    {
        $temp_array = $this->get_list_view_array();
        $temp_array['MAILBOX_TYPE_NAME'] = $GLOBALS['app_list_strings']['dom_mailbox_type'][$this->mailbox_type];
        return $temp_array;
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    /**
     * Override's SugarBean's
     */
    public function fill_in_additional_detail_fields()
    {
    }

    /**
     * {@inheritDoc}
     *
     * The created_by field must be kept in sync with the user_id field in order for the $created filter to work. This
     * fix (for jira: MAR-1841; SI: 67320) should be replaced by a refactor of the UserSignatures module so that the
     * user_id field can be dropped in favor of created_by.
     *
     * @param bool $check_notify
     * @return String
     */
    public function save($check_notify = false)
    {
        if (empty($this->user_id)) {
            $this->user_id = $GLOBALS['current_user']->id;
        }
        if ($this->created_by !== $this->user_id) {
            $this->created_by = $this->user_id;
        }
        return parent::save($check_notify);
    }
}
