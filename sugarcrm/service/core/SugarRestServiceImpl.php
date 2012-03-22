<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *
 ********************************************************************************/

/**
 * This class is an implemenatation class for all the rest services
 */
require_once('service/core/SugarWebServiceImpl.php');
class SugarRestServiceImpl extends SugarWebServiceImpl {
	
	function md5($string){
		return md5($string);
	}

    public function show(){
        $GLOBALS['simple_name_value_list'] = true;
        $sessionId = $this->getSession();
        return $this->get_entry($sessionId, $this->getParamFromRequest($_REQUEST, 'module'), $this->getParamFromRequest($_REQUEST, 'id'), $this->getParamFromRequest($_REQUEST, 'fields', 'array', array()), array());
    }

    public function search(){
       $GLOBALS['simple_name_value_list'] = true;
       $sessionId = $this->getSession();
       return $this->get_entry_list($sessionId, $this->getParamFromRequest($_REQUEST, 'module'), $this->getParamFromRequest($_REQUEST, 'query'), $this->getParamFromRequest($_REQUEST, 'orderBy'), $this->getParamFromRequest($_REQUEST, 'offset','', 0), $this->getParamFromRequest($_REQUEST, 'fields', 'array', array()), array(), $this->getParamFromRequest($_REQUEST, 'max_results','', 10), -1);
    }

    public function delete(){
       $GLOBALS['simple_name_value_list'] = true;
       $sessionId = $this->getSession();
       $nameValueList = array(array('name' => 'id', 'value' => $this->getParamFromRequest($_REQUEST, 'id')),array('name' => 'deleted', 'value' => '1'));
       return $this->set_entry($sessionId, $this->getParamFromRequest($_REQUEST, 'module'), $nameValueList);
    }

    public function edit($module_name = '', $name_value_list = array()){
       $GLOBALS['simple_name_value_list'] = true;
       $sessionId = $this->getSession();
       return $this->set_entry($sessionId, $module_name, $name_value_list);
    }
    
    private function getSession(){
        if(!empty($_REQUEST['session'])){
            return $_REQUEST['session'];
        }elseif(can_start_session()){
            session_start();
            $session_id = session_id();
            $this->validateSession();
            return $session_id;
        }else{
            return null;
        }
    }

    private function validateSession(){
        $_SESSION['user_id']= $_SESSION['authenticated_user_id'];
        $_SESSION['is_valid_session']= true;
        $_SESSION['ip_address'] = query_client_ip();
        $_SESSION['type'] = 'user';
        $_SESSION['unique_key'] = $GLOBALS['sugar_config']['unique_key'];
    }

    private function getParamFromRequest($input, $name, $type = '', $default = ''){
        if(empty($input[$name])){
            return $default;
        }else{
            if($type == 'array'){
                return explode(',', $input[$name]);
            }else{
                return $input[$name];
            }
        }
    }
}
require_once('service/core/SugarRestUtils.php');
SugarRestServiceImpl::$helperObject = new SugarRestUtils();
