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

class UsersViewEdit extends ViewEdit {

 	function UsersViewEdit(){
 		parent::ViewEdit();
 	}
    
    function display() {
        global $current_user;

        // There is a lot of extra stuff that needs to go in here to properly render
        $this->is_current_admin=is_admin($current_user)
        //BEGIN SUGARCRM flav=sales ONLY
            ||$current_user->user_type = 'UserAdministrator'
        //END SUGARCRM flav=sales ONLY
            ||$current_user->isAdminForModule('Users');
        $this->is_super_admin = is_admin($current_user);

        $this->usertype='REGULAR';
        if($this->is_super_admin){
            $this->usertype='ADMIN';
        }



        
	    //BEGIN SUGARCRM lic=sub ONLY
        global $sugar_flavor;
        if((isset($sugar_flavor) && $sugar_flavor != null) &&
           ($sugar_flavor=='CE' || isset($admin->settings['license_enforce_user_limit']) && $admin->settings['license_enforce_user_limit'] == 1)){
            if ($this->bean->id == "") {
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

        if ($this->is_current_admin) {
            $this->ss->assign('IS_ADMIN','1');
        } else {
            $this->ss->assign('IS_ADMIN', '0');
        }

        if ($this->is_super_admin) {
            $this->ss->assign('IS_SUPER_ADMIN','1');
        } else {
            $this->ss->assign('IS_SUPER_ADMIN', '0');
        }

        if (isset($GLOBALS['sugar_config']['show_download_tab'])) {
            $enable_download_tab = $GLOBALS['sugar_config']['show_download_tab'];
        }else{
            $enable_download_tab = true;
        }

        $this->ss->assign('SHOW_DOWNLOADS_TAB', $enable_download_tab);



        // Fill in fake fields in the bean from user preferences
        if (isset($this->bean->id)) {
            $this->ss->assign('ID',$this->bean->id);
        }
        
        $this->setupPasswordTab();
        $this->setupThemeTab();
        $this->setupAdvancedTab();

        parent::display();
    }

    protected function setupPasswordTab() {
        global $current_user;

        $enable_syst_generate_pwd=false;
        if(isset($GLOBALS['sugar_config']['passwordsetting']) && isset($GLOBALS['sugar_config']['passwordsetting']['SystemGeneratedPasswordON'])){
            $enable_syst_generate_pwd=$GLOBALS['sugar_config']['passwordsetting']['SystemGeneratedPasswordON'];
        }

        // If new regular user without system generated password or new portal user
        if(((isset($enable_syst_generate_pwd) && !$enable_syst_generate_pwd && $this->usertype!='GROUP') || $this->usertype =='PORTAL_ONLY') && empty($this->bean->id)) {
            $this->ss->assign('REQUIRED_PASSWORD','1');
        } else {
            $this->ss->assign('REQUIRED_PASSWORD','0');
        }

        // If my account page or portal only user or regular user without system generated password or a duplicate user
        if((($current_user->id == $this->bean->id) || $this->usertype=='PORTAL_ONLY' || (($this->usertype=='REGULAR' || $this->usertype == 'ADMIN' || (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true' && $this->usertype!='GROUP')) && !$enable_syst_generate_pwd)) && !$this->bean->external_auth_only ) {
            $this->ss->assign('CHANGE_PWD', '1');
        } else {
            $this->ss->assign('CHANGE_PWD', '0');
        }
        
        // Make sure group users don't get a password change prompt
        if ( $this->usertype == 'GROUP' ) {
            $this->ss->assign('CHANGE_PWD', '0');
        }

        $configurator = new Configurator();
        if ( isset($configurator->config['passwordsetting']) 
             && ($configurator->config['passwordsetting']['SystemGeneratedPasswordON']
                 || $configurator->config['passwordsetting']['forgotpasswordON'])
             && $this->usertype != 'GROUP' && $this->usertype != 'PORTAL_ONLY' ) {
            $this->ss->assign('REQUIRED_EMAIL_ADDRESS','1');
        } else {
            $this->ss->assign('REQUIRED_EMAIL_ADDRESS','0');
        }
        if($this->usertype=='GROUP' || $this->usertype=='PORTAL_ONLY') {
            $this->ss->assign('HIDE_FOR_GROUP_AND_PORTAL', 'none');
            $this->ss->assign('HIDE_CHANGE_USERTYPE','none');
        } else {
            $this->ss->assign('HIDE_FOR_NORMAL_AND_ADMIN','none');
            if (!$this->is_current_admin) {
                $this->ss->assign('HIDE_CHANGE_USERTYPE','none');
            } else {
                $this->ss->assign('HIDE_STATIC_USERTYPE','none');
            }
        }
        
    }

    protected function setupThemeTab() {
        $user_theme = $this->bean->getPreference('user_theme');
        if(isset($user_theme)) {
            $this->ss->assign("THEMES", get_select_options_with_id(SugarThemeRegistry::availableThemes(), $user_theme));
        } else {
            $this->ss->assign("THEMES", get_select_options_with_id(SugarThemeRegistry::availableThemes(), $GLOBALS['sugar_config']['default_theme']));
        }
        $this->ss->assign("SHOW_THEMES",count(SugarThemeRegistry::availableThemes()) > 1);
        $this->ss->assign("USER_THEME_COLOR", $this->bean->getPreference('user_theme_color'));
        $this->ss->assign("USER_THEME_FONT", $this->bean->getPreference('user_theme_font'));
        $this->ss->assign("USER_THEME", $user_theme);
        
// Build a list of themes that support group modules
        $this->ss->assign("DISPLAY_GROUP_TAB", 'none');
        
        $selectedTheme = $user_theme;
        if(!isset($user_theme)) {
            $selectedTheme = $GLOBALS['sugar_config']['default_theme'];
        }
        
        $themeList = SugarThemeRegistry::availableThemes();
        $themeGroupList = array();
        
        foreach ( $themeList as $themeId => $themeName ) {
            $currThemeObj = SugarThemeRegistry::get($themeId);
            if ( isset($currThemeObj->group_tabs) && $currThemeObj->group_tabs == 1 ) {
                $themeGroupList[$themeId] = true;
                if ( $themeId == $selectedTheme ) {
                    $this->ss->assign("DISPLAY_GROUP_TAB", '');
                }
            } else {
                $themeGroupList[$themeId] = false;
            }
        }
        $this->ss->assign("themeGroupListJSON",json_encode($themeGroupList));
        
    }
    
    protected function setupAdvancedTab() {
        global $current_user, $locale, $app_list_strings;
        // This is for the "Advanced" tab, it's not controlled by the metadata UI so we have to do more for it.

        $this->ss->assign('EXPORT_DELIMITER', $this->bean->getPreference('export_delimiter'));

        if($this->bean->receive_notifications ||(!isset($this->bean->id) && $admin->settings['notify_send_by_default'])) $this->ss->assign("RECEIVE_NOTIFICATIONS", "checked");

        //jc:12293 - modifying to use the accessor method which will translate the
        //available character sets using the translation files
        $this->ss->assign('EXPORT_CHARSET', get_select_options_with_id($locale->getCharsetSelect(), $locale->getExportCharset('', $this->bean)));
        //end:12293

        if( $this->bean->getPreference('use_real_names') == 'on' 
            || ( empty($this->bean->id) 
                 && isset($GLOBALS['sugar_config']['use_real_names']) 
                 && $GLOBALS['sugar_config']['use_real_names'] 
                 && $this->bean->getPreference('use_real_names') != 'off') ) {
            $this->ss->assign('USE_REAL_NAMES', 'CHECKED');
        }

        //BEGIN SUGARCRM flav!=sales ONLY
        if($this->bean->getPreference('mailmerge_on') == 'on') {
            $this->ss->assign('MAILMERGE_ON', 'checked');
        }
        //END SUGARCRM flav!=sales ONLY

        if($this->bean->getPreference('no_opps') == 'on') {
            $this->ss->assign('NO_OPPS', 'CHECKED');
        }

        $reminder_time = $this->bean->getPreference('reminder_time');
        if(empty($reminder_time)){
            $reminder_time = -1;
        }
        //BEGIN SUGARCRM flav!=sales ONLY
        $this->ss->assign("REMINDER_TIME_OPTIONS", get_select_options_with_id($app_list_strings['reminder_time_options'],$reminder_time));
        if($reminder_time > -1){
            $this->ss->assign("REMINDER_TIME_DISPLAY", 'inline');
            $this->ss->assign("REMINDER_CHECKED", 'checked');
        }else{
            $this->ss->assign("REMINDER_TIME_DISPLAY", 'none');
        }
        $this->ss->assign('CALENDAR_PUBLISH_KEY', $this->bean->getPreference('calendar_publish_key' ));
        //END SUGARCRM flav!=sales ONLY
        
        $this->setupAdvancedTabNavSettings();
        $this->setupAdvancedTabLocaleSettings();
    }

    protected function setupAdvancedTabNavSettings() {
        // Grouped tabs?
        $useGroupTabs = $this->bean->getPreference('navigation_paradigm');
        if ( ! isset($useGroupTabs) ) {
            if ( ! isset($GLOBALS['sugar_config']['default_navigation_paradigm']) ) {
                $GLOBALS['sugar_config']['default_navigation_paradigm'] = 'gm';
            }
            $useGroupTabs = $GLOBALS['sugar_config']['default_navigation_paradigm'];
        }
        $this->ss->assign("USE_GROUP_TABS",($useGroupTabs=='gm')?'checked':'');
        
        $user_max_tabs = $this->bean->getPreference('max_tabs');
        if(isset($user_max_tabs) && $user_max_tabs > 0) {
            $this->ss->assign("MAX_TAB", $user_max_tabs);
        } elseif(SugarThemeRegistry::current()->maxTabs > 0) {
            $this->ss->assign("MAX_TAB", SugarThemeRegistry::current()->maxTabs);
        } else {
            $this->ss->assign("MAX_TAB", $GLOBALS['sugar_config']['default_max_tabs']);
        }
        $this->ss->assign("MAX_TAB_OPTIONS", range(1, ((!empty($GLOBALS['sugar_config']['default_max_tabs']) && $GLOBALS['sugar_config']['default_max_tabs'] > 10 ) ? $GLOBALS['sugar_config']['default_max_tabs'] : 10)));
        
        //BEGIN SUGARCRM flav!=sales ONLY
        $user_subpanel_tabs = $this->bean->getPreference('subpanel_tabs');
        if(isset($user_subpanel_tabs)) {
            $this->ss->assign("SUBPANEL_TABS", $user_subpanel_tabs?'checked':'');
        } else {
            $this->ss->assign("SUBPANEL_TABS", $GLOBALS['sugar_config']['default_subpanel_tabs']?'checked':'');
        }
        //END SUGARCRM flav!=sales ONLY
    }

    protected function setupAdvancedTabLocaleSettings() {
        global $locale;
        //// Timezone
        if(empty($this->bean->id)) { // remove default timezone for new users(set later)
            $this->bean->user_preferences['timezone'] = '';
        }
        
        $userTZ = $this->bean->getPreference('timezone');
        
        if(empty($userTZ) && !$this->bean->is_group && !$this->bean->portal_only) {
            $userTZ = TimeDate::guessTimezone();
            $this->bean->setPreference('timezone', $userTZ);
        }
        
        if(!$this->bean->getPreference('ut')) {
            $this->ss->assign('PROMPTTZ', ' checked');
            //BEGIN SUGARCRM flav=sales ONLY
            $this->ss->assign('ut_hidden', "<input type='hidden' name='ut' id='ut' value='true'>");
            //END SUGARCRM flav=sales ONLY
        }
        $this->ss->assign('TIMEZONE_CURRENT', $userTZ);
        $this->ss->assign('TIMEZONEOPTIONS', TimeDate::getTimezoneList());
        
        // FG - Bug 4236 - Managed First Day of Week
        $fdowDays = array();
        foreach ($GLOBALS['app_list_strings']['dom_cal_day_long'] as $d) {
            if ($d != "") {
                $fdowDays[] = $d;
            }
        }
        $this->ss->assign("FDOWOPTIONS", $fdowDays);
        $currentFDOW = $this->bean->get_first_day_of_week();
        
        if (!isset($currentFDOW)) {$currentFDOW = 0;}
        $this->ss->assign("FDOWCURRENT", $currentFDOW);

        //// Numbers and Currency display
        require_once('modules/Currencies/ListCurrency.php');
        $currency = new ListCurrency();
        
        // 10/13/2006 Collin - Changed to use Localization.getConfigPreference
        // This was the problem- Previously, the "-99" currency id always assumed
        // to be defaulted to US Dollars.  However, if someone set their install to use
        // Euro or other type of currency then this setting would not apply as the
        // default because it was being overridden by US Dollars.
        $cur_id = $locale->getPrecedentPreference('currency', $this->bean);
        if($cur_id) {
            $selectCurrency = $currency->getSelectOptions($cur_id);
            $this->ss->assign("CURRENCY", $selectCurrency);
        } else {
            $selectCurrency = $currency->getSelectOptions();
            $this->ss->assign("CURRENCY", $selectCurrency);
        }
        
        $currencyList = array();
        foreach($locale->currencies as $id => $val ) {
            $currencyList[$id] = $val['symbol'];
        }
        $currencySymbolJSON = json_encode($currencyList);
        $this->ss->assign('currencySymbolJSON', $currencySymbolJSON);
        
        
        // fill significant digits dropdown
        $significantDigits = $locale->getPrecedentPreference('default_currency_significant_digits', $this->bean);
        $sigDigits = '';
        for($i=0; $i<=6; $i++) {
            if($significantDigits == $i) {
                $sigDigits .= "<option value=\"$i\" selected=\"true\">$i</option>";
            } else {
                $sigDigits .= "<option value=\"$i\">{$i}</option>";
            }
        }
        
        $this->ss->assign('sigDigits', $sigDigits);
        
        $num_grp_sep = $this->bean->getPreference('num_grp_sep');
        $dec_sep = $this->bean->getPreference('dec_sep');
        $this->ss->assign("NUM_GRP_SEP",(empty($num_grp_sep) ? $GLOBALS['sugar_config']['default_number_grouping_seperator'] : $num_grp_sep));
        $this->ss->assign("DEC_SEP",(empty($dec_sep) ? $GLOBALS['sugar_config']['default_decimal_seperator'] : $dec_sep));
        $this->ss->assign('getNumberJs', $locale->getNumberJs());
        
        //// Name display format
        $this->ss->assign('default_locale_name_format', $locale->getLocaleFormatMacro($this->bean));
        $this->ss->assign('getNameJs', $locale->getNameJs());
        ////	END LOCALE SETTINGS
    }
}