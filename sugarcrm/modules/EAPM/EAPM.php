<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('data/SugarBean.php');
require_once('include/SugarObjects/templates/basic/Basic.php');
require_once('include/externalAPI/ExternalAPIFactory.php');
require_once('include/SugarOauth.php');

class EAPM extends Basic {
	var $new_schema = true;
	var $module_dir = 'EAPM';
	var $object_name = 'EAPM';
	var $table_name = 'eapm';
	var $importable = false;
		var $id;
		var $type;
		var $name;
		var $date_entered;
		var $date_modified;
		var $modified_user_id;
		var $modified_by_name;
		var $created_by;
		var $created_by_name;
		var $description;
		var $deleted;
		var $created_by_link;
		var $modified_user_link;
		var $assigned_user_id;
		var $assigned_user_name;
		var $assigned_user_link;
		var $password;
		var $url;
		var $validated = false;
		var $active;
		var $oauth_token;
		var $oauth_secret;
		var $application;
		var $disable_row_level_security = true;

	function bean_implements($interface){
		switch($interface){
			case 'ACL': return true;
		}
		return false;
}

   static function getLoginInfo($application){
       global $current_user;

       $eapmBean = new EAPM();
       $eapmBean = $eapmBean->retrieve_by_string_fields(array('assigned_user_id'=>$current_user->id, 'application'=>$application, 'active' => 1));

       /*
        $results = $GLOBALS['db']->query("SELECT * FROM eapm WHERE assigned_user_id = '{$GLOBALS['current_user']->id}' AND application='$application' AND deleted = 0");
        $row = $GLOBALS['db']->fetchByAssoc($results);
        if(isset($row['password'])){
        	require_once("include/utils/encryption_utils.php");
        	$row['password'] = blowfishDecode(blowfishGetKey('encrypt_field'),$row['password']);;
        }
        return $row;
       */

       if(isset($eapmBean->password)){
           require_once("include/utils/encryption_utils.php");
           $eapmBean->password = blowfishDecode(blowfishGetKey('encrypt_field'),$eapmBean->password);;
       }

       return $eapmBean;
    }

//   function save($check_notify = FALSE) {
//       // Now time to test if the login info they typed in actually works.
//       $api = ExternalAPIFactory::loadAPI($this->application,true);
//       $reply = $api->checkLogin($this);
//
//       if ( !$reply['success'] ) {
//           // FIXME: Translate
//           $_SESSION['administrator_error'] = 'Error during login: '.$reply['errorMessage'];
//           return;
//       }
//
//       $id = parent::save($check_notify);
//
//   }

   function validated()
   {
       if(empty($this->id)) {
           return false;
       }
        // FIXME: use save?
       $GLOBALS['db']->query("UPDATE eapm SET validated=1 WHERE id = '{$this->id}' AND deleted = 0");
       if($this->active && !empty($this->application)) {
           // deactivate other EAPMs with same app
           $GLOBALS['db']->query("UPDATE eapm SET active=0 WHERE application = '{$this->application}' AND id != '{$this->id}' AND active=1 AND deleted = 0");
       }
   }

   protected function getOauth()
   {
        $oauth = new SugarOAuth($this->oauthData->consumer_key, $this->oauthData->consumer_secret);
        return $oauth;
   }

   public function oauthLogin($oauthReq, $authReq, $accReq)
   {
        global $sugar_config;
        $oauth = $this->getOauth();
        if(isset($_SESSION['oauth_secret']) && isset($_SESSION['oauth_token']) && isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier'])) {
            $stage = 1;
        } else {
            $stage = 0;
        }
        if($stage == 0) {
            $callback_url = urlencode($sugar_config['site_url'].'/index.php?module=EAPM&action=OAuth&record='.$this->id);
            $GLOBALS['log']->debug("OAuth request token: {$oauthReq} callback: $callback_url");
            $request_token_info = $oauth->getRequestToken($oauthReq."?oauth_callback=$callback_url");
            $GLOBALS['log']->debug("OAuth token: ".var_export($request_token_info, true));
            $_SESSION['oauth_secret'] = $request_token_info['oauth_token_secret'];
            $_SESSION['oauth_token'] = $request_token_info['oauth_token'];
            header("Location: {$authReq}?oauth_token={$request_token_info['oauth_token']}");
            exit();
        } else {
            $oauth->setToken($_SESSION['oauth_token'],$_SESSION['oauth_secret']);
            $GLOBALS['log']->debug("OAuth access token: {$accReq}");
            $access_token_info = $oauth->getAccessToken($accReq);
            $GLOBALS['log']->debug("OAuth token: ".var_export($access_token_info, true));
            $this->oauth_token = $access_token_info['oauth_token'];
            $this->oauth_secret = $access_token_info['oauth_token_secret'];
            $this->validated = 1;
            $this->save();
            unset($_SESSION['oauth_secret']);
            unset($_SESSION['oauth_token']);
        }
	}

	protected function fillInName()
	{
	    if(empty($this->name) && $this->type == "oauth") {
	        $this->name = sprintf(translate('LBL_OAUTH_NAME', $this->module_dir), $this->application);
	    }
	}

	public function fill_in_additional_detail_fields()
	{
	    $this->fillInName();
	    parent::fill_in_additional_detail_fields();
	}

	public function fill_in_additional_list_fields()
	{
	    $this->fillInName();
	    parent::fill_in_additional_list_fields();
	}

}

// External API integration, for the dropdown list of what external API's are available
function getEAPMExternalApiDropDown() {
    $apiList = ExternalAPIFactory::getModuleDropDown('',true);

    return $apiList;

}
