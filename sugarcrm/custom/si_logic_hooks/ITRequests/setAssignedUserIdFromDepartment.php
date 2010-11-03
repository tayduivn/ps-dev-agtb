<?php
class setAssignedUserIdFromDepartment {

    public function setAssignedToId(&$bean, $event, $arguments)
    {
        if ($event != 'before_save') return false;
        
        // we only want to do this when the id of the bean is empty
        if (empty($bean->assigned_user_id)) {
            // make sure it's empty because people in the IT group can set it

            switch ($bean->department_c) {
                // IT_User
                case 'it':
                    $bean->assigned_user_id = '4ec24c05-a913-6717-1241-46aef51489fb';
                    $bean->new_assigned_user_name = 'IT';
                    break;
                // Operations User
                case 'operations':
                    $bean->assigned_user_id = '225711f0-48e8-4683-ae69-4b742ec5e062';
                    $bean->new_assigned_user_name = 'Operations';
                    break;
                // Internal Systems User
                case 'internal':
                    // break omitted
                default:
                    // default it to internal systems
                    $bean->assigned_user_id = '3627d5b3-d7d6-bafb-add6-48f6641a90c4';
                    $bean->new_assigned_user_name = 'Internal Systems';
                    break;
            }

            // send assignment notification email
            $user = new User();
            $user->retrieve($bean->assigned_user_id);

            $admin = new Administration();
			$admin->retrieveSettings();
            
            $bean->send_assignment_notifications($user, $admin);

            $this->log('Bean Assigned To: ' . $bean->assigned_user_id);
        }

        return true;
    }

    public function log($msg)
    {
        syslog(LOG_DEBUG, date('r') . 'DepartmentITR - ' . $msg);
    }
}