<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 ********************************************************************************/

/**
 * This class simply loads the sugar security object for the system.
 * It will load either from being told to load a security object of a particular type or by loading from the current session
 */
class SugarSecurityFactory {
    /**
     * This function returns a SugarSecurity object of the requested type
     *
     * @param String $type -- What type of SugarSecurity object do you want, Sugar Users should use type 'User', Support portal users should use type 'SupportPortal'
     * @return SugarSecurity -- A SugarSecurity object of the requested type.
     */
    static public function loadClassFromType($type = 'User' ) {
        $cleanType = basename($type);
        
        $className = 'SugarSecurity'.$cleanType;

        if ( file_exists('custom/include/SugarSecurity/'.$className.'Cstm.php') ) {
            $className = $className.'Cstm';
            require_once('custom/include/SugarSecurity/'.$className.'Cstm.php');
        } else if ( file_exists('custom/include/SugarSecurity/'.$className.'.php') ) {
            require_once('custom/include/SugarSecurity/'.$className.'.php');
        } else if ( file_exists('include/SugarSecurity/'.$className.'.php') ) {
            require_once('include/SugarSecurity/'.$className.'.php');
        }

        if ( ! class_exists($className) ) {
            // Tried everywhere to find the class, couldn't find anything.
            return null;
        }

        $securityClass = new $className();
        
        return $securityClass;
    }

    /**
     * This function returns a SugarSecurity object by looking at the current user's session to determine what object type to return. Also calls the SugarSecurity->restoreFromSession() call to initialize the SugarSecurity object
     *
     * @return SugarSecurity -- A SugarSecurity object
     */
    static public function loadClassFromSession($sessionId = null) {
        if ( $sessionId != null ) {
            session_id($sessionId);
            session_start();
        }

        if ( isset($_SESSION['sugarSec']['type']) ) {
            $type = $_SESSION['sugarSec']['type'];
        } else {
            // No type set in the session, probably a normal user
            $type = 'User';
        }

        $securityClass = self::loadClassFromType($type);
        
        if ( $securityClass != null && $securityClass->loadFromSession() ) {
            return $securityClass;
        } else {
            return null;
        }
    }
    
}
