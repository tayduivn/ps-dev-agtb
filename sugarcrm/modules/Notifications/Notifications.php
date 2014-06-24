<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class Notifications extends Basic
{
    public $new_schema = true;
    public $module_dir = 'Notifications';
    public $object_name = 'Notifications';
    public $table_name = 'notifications';
    public $importable = false;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $is_read;
    public $severity;
    public $disable_custom_fields = true;
    //BEGIN SUGARCRM flav=pro ONLY
    public $disable_row_level_security = true;
    //END SUGARCRM flav=pro  ONLY


    public function __construct()
    {
        parent::__construct();
        $this->addVisibilityStrategy('OwnerVisibility');
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     *
     * Should replace send notification portion in SugarBean.
     */
    public function sendNotification()
    {
        $GLOBALS['log']->deprecated('Notifications.php: sendNotification() is deprecated');
        //Determine how the user wants to receive notifications from the system (email|sms|in system)

        //Factory pattern returns array of classes cooresponding to different options for user

        //Iterate over each object, call send, all objects implement sendable.

    }

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     *
     * @param unknown_type $user
     */
    public function clearUnreadNotificationCacheForUser($user)
    {
        $GLOBALS['log']->deprecated('Notifications.php: clearUnreadNotificationCacheForUser() is deprecated');
    }

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     */
    public function retrieveUnreadCountFromDateEnteredFilter($date_entered)
    {
        $GLOBALS['log']->deprecated('Notifications.php: retrieveUnreadCountFromDateEnteredFilter() is deprecated');
        global $current_user;
        $query = "SELECT count(*) as cnt FROM {$this->table_name} where is_read='0' AND deleted='0' AND assigned_user_id='{$current_user->id}' AND
	               date_entered >  '$date_entered' ";
        $result = $this->db->query($query, false);
        $row = $this->db->fetchByAssoc($result);
        $result = ($row['cnt'] != null) ? $row['cnt'] : 0;

        return $result;
    }

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     */
    public function getUnreadNotificationCountForUser($user = null)
    {
        $GLOBALS['log']->deprecated('Notifications.php: getUnreadNotificationCountForUser() is deprecated');
        /** TO DO - ADD A CACHE MECHANISM HERE **/

        if ($user == null) {
            $user = $GLOBALS['current_user'];
        }

        $query = "SELECT count(*) as cnt FROM {$this->table_name} where is_read='0' AND deleted='0' AND assigned_user_id='{$user->id}' ";
        $result = $this->db->query($query, false);
        $row = $this->db->fetchByAssoc($result);
        $result = ($row['cnt'] != null) ? $row['cnt'] : 0;

        return $result;
    }

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     */
    public function getSystemNotificationsCount()
    {
        $GLOBALS['log']->deprecated('Notifications.php: getSystemNotificationsCount() is deprecated');

        $sv = new SugarView();
        $GLOBALS['system_notification_buffer'] = array();
        $GLOBALS['buffer_system_notifications'] = true;
        $GLOBALS['system_notification_count'] = 0;
        $sv->includeClassicFile('modules/Administration/DisplayWarnings.php');
        return $GLOBALS['system_notification_count'];
    }
}
