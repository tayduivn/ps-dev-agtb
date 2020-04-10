<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class Shift extends Basic {
    public $new_schema = true;
    public $module_dir = 'Shifts';
    public $module_name = 'Shifts';
    public $object_name = 'Shift';
    public $table_name = 'shifts';
    public $importable = true;

    public $id;
    public $name;
    public $deleted;
    public $description;

    public $timezone;

    public $date_start;
    public $date_end;

    public $is_open_sunday;
    public $sunday_open_hour;
    public $sunday_open_minutes;
    public $sunday_close_hour;
    public $sunday_close_minutes;
    public $is_open_monday;
    public $monday_open_hour;
    public $monday_open_minutes;
    public $monday_close_hour;
    public $monday_close_minutes;
    public $is_open_tuesday;
    public $tuesday_open_hour;
    public $tuesday_open_minutes;
    public $tuesday_close_hour;
    public $tuesday_close_minutes;
    public $is_open_wednesday;
    public $wednesday_open_hour;
    public $wednesday_open_minutes;
    public $wednesday_close_hour;
    public $wednesday_close_minutes;
    public $is_open_thursday;
    public $thursday_open_hour;
    public $thursday_open_minutes;
    public $thursday_close_hour;
    public $thursday_close_minutes;
    public $is_open_friday;
    public $friday_open_hour;
    public $friday_open_minutes;
    public $friday_close_hour;
    public $friday_close_minutes;
    public $is_open_saturday;
    public $saturday_open_hour;
    public $saturday_open_minutes;
    public $saturday_close_hour;
    public $saturday_close_minutes;

    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $created_by_link;
    public $modified_user_link;

    public $teams;
    public $team_id;
    public $team_set_id;
    public $team_link;
    public $team_count_link;
    public $acl_team_set_id;
    public $team_count;
    public $team_name;
    public $acl_team_names;

    public function bean_implements($interface){
        switch($interface){
            case 'ACL': return true;
        }
        return false;
    }
}
