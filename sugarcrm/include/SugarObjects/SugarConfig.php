<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
 ********************************************************************************/
/**
 * Config manager
 * @api
 */
class SugarConfig
{
    var $_cached_values = array();

    function getInstance() {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new SugarConfig();
        }
        return $instance;
    }

    function get($key, $default = null) {
        if (!isset($this->_cached_values[$key])) {
            if (!class_exists('SugarArray', true)) {
				require 'include/utils/array_utils.php';
			}
            if ($key == "logger.file.ext") {
                @session_start();
                if (isset($GLOBALS['sugar_config'])) {
                    if (isset($_POST['logger_file_ext'])) {
                        $new_ext = $_POST['logger_file_ext'];
                        $trim_new_ext = preg_replace('/^\./', '', $new_ext);
                        if(in_array($trim_new_ext, $GLOBALS['sugar_config']['upload_badext'])) {
                            $this->_cached_values[$key] = SugarArray::staticGet($GLOBALS['sugar_config'], $key, $default);
                            $_SESSION['old_ext'] = $this->_cached_values[$key];
                            $GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
                            $GLOBALS['log']->security("Invalid log file extension: trying to use invalid file extension '$trim_new_ext'.");
                        }
                        else {
                            $this->_cached_values[$key] = $new_ext;
                            if (isset($_SESSION['old_ext'])) unset($_SESSION['old_ext']);
                        }
                    }
                    else {
                        $this->_cached_values[$key] = isset($_SESSION['old_ext']) ?
                            $_SESSION['old_ext'] :
                            SugarArray::staticGet($GLOBALS['sugar_config'], $key, $default);
                    }
                }
                else
                    $this->_cached_values[$key] = $default;
            }
            else {
                $this->_cached_values[$key] = isset($GLOBALS['sugar_config']) ?
                    SugarArray::staticGet($GLOBALS['sugar_config'], $key, $default) :
                    $default;
            }
        }
        return $this->_cached_values[$key];
    }

    function clearCache($key = null) {
        if (is_null($key)) {
            $this->_cached_values = array();
        } else {
            unset($this->_cached_values[$key]);
        }
    }
}

