<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/*

Modification information for LGPL compliance

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r56153 - 2010-04-28 15:37:27 -0700 (Wed, 28 Apr 2010) - asandberg - Bug #35808 - Tab ordering for fields is inconsistent specifically for the "Email Address" and "Teams" fields
Modified SugarFieldBase file, displayFromFunc function to propagate the tabindex down the call stack and the email address widget to utilize the value

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r54415 - 2010-02-10 07:58:39 -0800 (Wed, 10 Feb 2010) - jmertic - Bug 35628 - Fixed SQL error showing when importing into a currency field. Also fixes unit test failure of properly rendering the currency field widget.

r54369 - 2010-02-08 16:33:57 -0800 (Mon, 08 Feb 2010) - rob - Bug 35453: Add support for vardef functions to return various html, bypassing the sugar fields


*/


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_field} function plugin
 *
 * Type:     function
 * Name:     sugar_run_helper
 * Purpose:  Runs helper functions as defined in the vardef for specific fields
 * 
 * @author Rob Aagaard {rob at sugarcrm.com}
 * @param array
 * @param Smarty
 */

function smarty_function_sugar_run_helper($params, &$smarty)
{
    $error = false;
    
    if(!isset($params['func'])) {
        $error = true;
        $smarty->trigger_error("sugar_field: missing 'func' parameter");
    }
    if(!isset($params['displayType'])) {
        $error = true;
        $smarty->trigger_error("sugar_field: missing 'displayType' parameter");
    }
    if(!isset($params['bean'])) {
        $params['bean'] = $GLOBALS['focus'];
    }

    if ( $error ) {
        return;
    }

    $funcName = $params['func'];

    if ( !empty($params['include']) ) {
        require_once($params['include']);
    }

    $_contents = $funcName($params['bean'],$params['field'],$params['value'],$params['displayType'],$params['tabindex']);
    return $_contents;
}
?>
