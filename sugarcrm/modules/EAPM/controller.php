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

class EAPMController extends SugarController
{
    /**
     * API implementation
     * @var ExternalAPIPlugin
     */
    protected $api;

    protected function failed($error)
    {
        $_SESSION['administrator_error'] = $error;
        $GLOBALS['log']->error("Login error: $error");
        $url = 'index.php?module=EAPM&action=EditView&record='.$this->bean->id;
        return $this->set_redirect($url);
    }

    public function pre_save()
    {
        parent::pre_save();
        $this->api = ExternalAPIFactory::loadAPI($this->bean->application,true);
        if(empty($this->api) || !$this->api->supports($this->bean->type)) {
            return $this->failed(translate('LBL_AUTH_UNSUPPORTED', $this->bean->module_dir));
        }
        $this->api->loadEAPM($this->bean);
        $this->bean->validated = false;
        $this->bean->save_cleanup();
    }

    protected function post_save()
    {
        if($this->bean->active) {
            // do not load bean here since password is already encoded
            $reply = $this->api->checkLogin();
            if ( !$reply['success'] ) {
                return $this->failed(sprintf(translate('LBL_AUTH_ERROR', $this->bean->module_dir), $reply['errorMessage']));
            } else {
                $this->bean->validated();
            }
        }
        return parent::post_save();
    }

    protected function action_oauth()
    {
        if(empty($this->bean->id)) {
            return $this->set_redirect('index.php');
        }
		if(!$this->bean->ACLAccess('save')){
			ACLController::displayNoAccess(true);
			sugar_cleanup(true);
			return true;
		}
        $this->api = ExternalAPIFactory::loadAPI($this->bean->application,true);
        $reply = $this->api->checkLogin($this->bean);
        if ( !$reply['success'] ) {
            return $this->failed(sprintf(translate('LBL_AUTH_ERROR', $this->bean->module_dir), $reply['errorMessage']));
        } else {
            $this->bean->validated();
            // redirect to detail view, as in save
            return parent::post_save();
        }
    }
}