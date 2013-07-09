<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


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
    public $type;
    //BEGIN SUGARCRM PRO ONLY
    public $disable_row_level_security = true;
    //END SUGARCRM PRO ONLY


    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @deprecated
     */
    public function Notifications()
    {
        $this->__construct();
    }

    public function __construct()
    {
        parent::__construct();
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
     * TODO
     *
     * Should replace send notification portion in SugarBean.
     */
    public function sendNotification()
    {
        //Determine how the user wants to receive notifications from the system (email|sms|in system)

        //Factory pattern returns array of classes cooresponding to different options for user

        //Iterate over each object, call send, all objects implement sendable.

    }

    /**
     * TODO
     *
     * @param unknown_type $user
     */
    public function clearUnreadNotificationCacheForUser($user)
    {

    }

    public function retrieveUnreadCountFromDateEnteredFilter($date_entered)
    {
        global $current_user;
        $query = "SELECT count(*) as cnt FROM {$this->table_name} where is_read='0' AND deleted='0' AND assigned_user_id='{$current_user->id}' AND
	               date_entered >  '$date_entered' ";
        $result = $this->db->query($query, false);
        $row = $this->db->fetchByAssoc($result);
        $result = ($row['cnt'] != null) ? $row['cnt'] : 0;

        return $result;
    }

    public function getUnreadNotificationCountForUser($user = null)
    {
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

    public function getSystemNotificationsCount()
    {
        $sv = new SugarView();
        $GLOBALS['system_notification_buffer'] = array();
        $GLOBALS['buffer_system_notifications'] = true;
        $GLOBALS['system_notification_count'] = 0;
        $sv->includeClassicFile('modules/Administration/DisplayWarnings.php');
        return $GLOBALS['system_notification_count'];
    }
}
