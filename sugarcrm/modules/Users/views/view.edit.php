<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/MVC/View/views/view.edit.php');
require_once('modules/Users/UserViewHelper.php');

class UsersViewEdit extends ViewEdit {

 	function UsersViewEdit(){
 		parent::ViewEdit();
 	}
    
    function display() {
        global $current_user, $app_list_strings;

        if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
            $this->bean->id = "";
            $this->bean->user_name = "";
        }

        ///////////////////////////////////////////////////////////////////////////////
        ////	REDIRECTS FROM COMPOSE EMAIL SCREEN
        if(isset($_REQUEST['type']) && (isset($_REQUEST['return_module']) && $_REQUEST['return_module'] == 'Emails')) {
            $this->ss->assign('REDIRECT_EMAILS_TYPE', $_REQUEST['type']);
        }
        ////	END REDIRECTS FROM COMPOSE EMAIL SCREEN
        ///////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////
        ////	NEW USER CREATION ONLY
        if(empty($this->bean->id)) {
            $this->ss->assign('SHOW_ADMIN_CHECKBOX','height="30"');
            $this->ss->assign('NEW_USER','1');
        }else{
            $this->ss->assign('NEW_USER','0');
            $this->ss->assign('NEW_USER_TYPE','DISABLED');
            //BEGIN SUGARCRM flav=pro ONLY
            $this->ss->assign('REASSIGN_JS', "return confirmReassignRecords();");
            //END SUGARCRM flav=pro ONLY
        }
        
        ////	END NEW USER CREATION ONLY
        ///////////////////////////////////////////////////////////////////////////////



        
	    //BEGIN SUGARCRM lic=sub ONLY
        global $sugar_flavor;
        if((isset($sugar_flavor) && $sugar_flavor != null) &&
           ($sugar_flavor=='CE' || isset($admin->settings['license_enforce_user_limit']) && $admin->settings['license_enforce_user_limit'] == 1)){
            if (empty($this->bean->id)) {
                $admin = new Administration();
                $admin->retrieveSettings();
                $license_users = $admin->settings['license_users'];
                if ($license_users != '') {
        //END SUGARCRM lic=sub ONLY
		//BEGIN SUGARCRM dep=od ONLY

                    $license_seats_needed = count( get_user_array(false, "Active", "", false, null, " AND is_group=0 AND portal_only=0 AND user_name not like 'SugarCRMSupport' AND user_name not like '%_SupportUser'", false) ) - $license_users;
		//END SUGARCRM dep=od ONLY
		//BEGIN SUGARCRM flav=pro  && dep=os ONLY
                    $license_seats_needed = count( get_user_array(false, "Active", "", false, null, " AND deleted=0 AND is_group=0 AND portal_only=0 ", false) ) - $license_users;
		//END SUGARCRM flav=pro  && dep=os ONLY
        //BEGIN SUGARCRM lic=sub ONLY
                }
                else {
                    $license_seats_needed = -1;
                }
                if( $license_seats_needed >= 0 ){
                    displayAdminError( translate('WARN_LICENSE_SEATS_USER_CREATE', 'Administration') . translate('WARN_LICENSE_SEATS2', 'Administration')  );
                    if( isset($_SESSION['license_seats_needed'])) {
                        unset($_SESSION['license_seats_needed']);
                    }
                    //die();
                }
            }
        }
	    //END SUGARCRM lic=sub ONLY
        
        // FIXME: Translate error prefix
        if(isset($_REQUEST['error_string'])) $this->ss->assign('ERROR_STRING', '<span class="error">Error: '.$_REQUEST['error_string'].'</span>');
        if(isset($_REQUEST['error_password'])) $this->ss->assign('ERROR_PASSWORD', '<span id="error_pwd" class="error">Error: '.$_REQUEST['error_password'].'</span>');




        // Build viewable versions of a few fields for non-admins
        if(!empty($this->bean->id)) {
            $this->ss->assign('STATUS_READONLY',$app_list_strings['user_status_dom'][$this->bean->status]);
            $this->ss->assign('EMPLOYEE_STATUS_READONLY', $app_list_strings['employee_status_dom'][$this->bean->employee_status]);
            $this->ss->assign('REPORTS_TO_READONLY', get_assigned_user_name($this->bean->reports_to_id));
        }
        
        $fieldHelper = new UserViewHelper($this->ss, $this->bean, 'EditView');
        $fieldHelper->setupAdditionalFields();

        parent::display();
    }

}