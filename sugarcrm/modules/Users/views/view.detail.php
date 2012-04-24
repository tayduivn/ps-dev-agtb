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

require_once('modules/Users/UserViewHelper.php');

class UsersViewDetail extends ViewDetail {

 	function UsersViewDetail(){
 		parent::ViewDetail();
 	}

    function preDisplay() {
        global $current_user, $app_strings, $sugar_config;

        if(!isset($this->bean->id) ) {
            // No reason to set everything up just to have it fail in the display() call
            return;
        }

        parent::preDisplay();

        $viewHelper = new UserViewHelper($this->ss, $this->bean, 'DetailView');
        $viewHelper->setupAdditionalFields();

        $errors = "";
        $msgGood = false;
        if (isset($_REQUEST['pwd_set']) && $_REQUEST['pwd_set']!= 0){
            if ($_REQUEST['pwd_set']=='4'){
                require_once('modules/Users/password_utils.php');
                $errors.=canSendPassword();
            }
            else {
                $errors.=translate('LBL_NEW_USER_PASSWORD_'.$_REQUEST['pwd_set'],'Users');
                $msgGood = true;
            }
        }else{
            //IF BEAN USER IS LOCKOUT
            if($this->bean->getPreference('lockout')=='1') {
                $errors.=translate('ERR_USER_IS_LOCKED_OUT','Users');
            }
        }
        $this->ss->assign("ERRORS", $errors);
        $this->ss->assign("ERROR_MESSAGE", $msgGood ? translate('LBL_PASSWORD_SENT','Users') : translate('LBL_CANNOT_SEND_PASSWORD','Users'));
        $buttons = array();
        if ((is_admin($current_user) || $_REQUEST['record'] == $current_user->id
             //BEGIN SUGARCRM flav=sales ONLY
             || $current_user->user_type == 'UserAdministrator'
             //END SUGARCRM flav=sales ONLY
                )
            //BEGIN SUGARCRM flav=sales ONLY
            && !is_admin($this->bean)
            //END SUGARCRM flav=sales ONLY
            && !empty($sugar_config['default_user_name'])
            && $sugar_config['default_user_name'] == $this->bean->user_name
            && isset($sugar_config['lock_default_user_name'])
            && $sugar_config['lock_default_user_name']) {
            $buttons[] = "<input id='edit_button' accessKey='".$app_strings['LBL_EDIT_BUTTON_KEY']."' name='Edit' title='".$app_strings['LBL_EDIT_BUTTON_TITLE']."' value='".$app_strings['LBL_EDIT_BUTTON_LABEL']."' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.return_id.value='".$this->bean->id."'; this.form.action.value='EditView'\" type='submit' value='" . $app_strings['LBL_EDIT_BUTTON_LABEL'] .  "'>";

        }
        elseif (is_admin($current_user)|| ($GLOBALS['current_user']->isAdminForModule('Users')&& !$this->bean->is_admin)
                //BEGIN SUGARCRM flav=sales ONLY
                || ($current_user->user_type == 'UserAdministrator' && !$this->bean->is_admin)
                //END SUGARCRM flav=sales ONLY
                || $_REQUEST['record'] == $current_user->id) {
            $buttons[] = "<input title='".$app_strings['LBL_EDIT_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_EDIT_BUTTON_KEY']."' name='Edit' id='edit_button' value='".$app_strings['LBL_EDIT_BUTTON_LABEL']."' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.return_id.value='".$this->bean->id."'; this.form.action.value='EditView'\" type='submit' value='" . $app_strings['LBL_EDIT_BUTTON_LABEL'] .  "'>";
            if ((is_admin($current_user)|| $GLOBALS['current_user']->isAdminForModule('Users')
                 //BEGIN SUGARCRM flav=sales ONLY
                 || ($current_user->user_type == 'UserAdministrator' && !is_admin($this->bean))
                 //END SUGARCRM flav=sales ONLY
                    )) {
                if (!$current_user->is_group){
                    $buttons[] = "<input id='duplicate_button' title='".$app_strings['LBL_DUPLICATE_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_DUPLICATE_BUTTON_KEY']."' class='button' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.isDuplicate.value=true; this.form.action.value='EditView'\" type='submit' name='Duplicate' value='".$app_strings['LBL_DUPLICATE_BUTTON_LABEL']."'>";

                    if($this->bean->id != $current_user->id) {
                        $buttons[] ="<input id='delete_button' type='button' class='button' onclick='confirmDelete();' value='".$app_strings['LBL_DELETE_BUTTON_LABEL']."' />";
                    }

                    if (!$this->bean->portal_only && !$this->bean->is_group && !$this->bean->external_auth_only
                        && isset($sugar_config['passwordsetting']['SystemGeneratedPasswordON']) && $sugar_config['passwordsetting']['SystemGeneratedPasswordON']){
                        $buttons[] = "<input title='".translate('LBL_GENERATE_PASSWORD_BUTTON_TITLE','Users')."' class='button' LANGUAGE=javascript onclick='generatepwd(\"".$this->bean->id."\");' type='button' name='password' value='".translate('LBL_GENERATE_PASSWORD_BUTTON_LABEL','Users')."'>";
                    }
                }
            }
        }

        $buttons = array_merge($buttons, $this->ss->get_template_vars('BUTTONS_HEADER'));

        $this->ss->assign('EDITBUTTONS',$buttons);

        //BEGIN SUGARCRM flav!=sales ONLY
        $show_roles = (!($this->bean->is_group=='1' || $this->bean->portal_only=='1'));
        //END SUGARCRM flav!=sales ONLY
        //BEGIN SUGARCRM flav=sales ONLY
        $show_roles = false;
        //END SUGARCRM flav=sales ONLY
        $this->ss->assign('SHOW_ROLES', $show_roles);
        //Mark whether or not the user is a group or portal user
        $this->ss->assign('IS_GROUP_OR_PORTAL', ($this->bean->is_group=='1' || $this->bean->portal_only=='1') ? true : false);
        if ( $show_roles ) {
            ob_start();
            echo "<div>";
            require_once('modules/ACLRoles/DetailUserRole.php');
            echo "</div></div>";

            //BEGIN SUGARCRM flav=pro ONLY
            if(file_exists('custom/modules/Users/Ext/UserPage/userpage.ext.php')) {
                include_once('custom/modules/Users/Ext/UserPage/userpage.ext.php');
            }
            //END SUGARCRM flav=pro ONLY

            $role_html = ob_get_contents();
            ob_end_clean();
            $this->ss->assign('ROLE_HTML',$role_html);
        }

    }

    public function getMetaDataFile() {
        $userType = 'Regular';
        //BEGIN SUGARCRM flav=ent ONLY
        if($this->bean->portal_only == 1){
            $userType = 'Portal';
        }
        //END SUGARCRM flav=ent ONLY
        //BEGIN SUGARCRM flav!=sales ONLY
        if($this->bean->is_group == 1){
            $userType = 'Group';
        }
        //END SUGARCRM flav!=sales ONLY

        if ( $userType != 'Regular' ) {
            $oldType = $this->type;
            $this->type = $oldType.'group';
        }
        $metadataFile = parent::getMetaDataFile();
        if ( $userType != 'Regular' ) {
            $this->type = $oldType;
        }
        return $metadataFile;
    }

    function display() {
        if ($this->bean->portal_only == 1 || $this->bean->is_group == 1 ) {
            $this->options['show_subpanels'] = false;
            $this->dv->formName = 'DetailViewGroup';
            $this->dv->view = 'DetailViewGroup';
        }

	    //handle request to reset the homepage
        if(isset($_REQUEST['reset_homepage'])){
            $this->bean->resetPreferences('Home');
            global $current_user;
            if($this->bean->id == $current_user->id) {
                $_COOKIE[$current_user->id . '_activePage'] = '0';
                setcookie($current_user->id . '_activePage','0',3000);
            }
        }

        return parent::display();
    }


    /**
     * getHelpText
     *
     * This is a protected function that returns the help text portion.  It is called from getModuleTitle.
     * We override the function from SugarView.php to make sure the create link only appears if the current user
     * meets the valid criteria.
     *
     * @param $module String the formatted module name
     * @return $theTitle String the HTML for the help text
     */
    protected function getHelpText($module)
    {
        $theTitle = '';

        if($GLOBALS['current_user']->isAdminForModule('Users')
		//BEGIN SUGARCRM flav=sales ONLY
		|| $GLOBALS['current_user']->user_type == 'UserAdministrator'
		//END SUGARCRM flav=sales ONLY
        ) {
        $createImageURL = SugarThemeRegistry::current()->getImageURL('create-record.gif');
        $url = ajaxLink("index.php?module=$module&action=EditView&return_module=$module&return_action=DetailView");
        $theTitle = <<<EOHTML
&nbsp;
<img src='{$createImageURL}' alt='{$GLOBALS['app_strings']['LNK_CREATE']}'>
<a href="{$url}" class="utilsLink">
{$GLOBALS['app_strings']['LNK_CREATE']}
</a>
EOHTML;
        }
        return $theTitle;
    }

}
