<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: tabConfig.php 53116 2009-12-10 01:24:37Z mitani $

$GLOBALS['tabStructure'] = array(
    "LBL_TABGROUP_SALES" => array(
        'label' => 'LBL_TABGROUP_SALES',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Opportunities",
            //BEGIN SUGARCRM flav!=sales ONLY
            "Leads",
            "Contracts",
            "Quotes",
            //END SUGARCRM flav!=sales ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            "Products",
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav!=sales ONLY
            "Forecasts",
            //END SUGARCRM flav!=sales ONLY
        )
    ),
    //BEGIN SUGARCRM flav!=sales ONLY
    "LBL_TABGROUP_MARKETING" => array(
        'label' => 'LBL_TABGROUP_MARKETING',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Leads",    
            "Campaigns",
            "Prospects",
            "ProspectLists",
        )
    ),
    "LBL_TABGROUP_SUPPORT" => array(
        'label' => 'LBL_TABGROUP_SUPPORT',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Cases",
            "Bugs",
    //END SUGARCRM flav!=sales ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            "KBDocuments",
            //END SUGARCRM flav=pro ONLY
    //BEGIN SUGARCRM flav!=sales ONLY
        )
    ),
    //END SUGARCRM flav!=sales ONLY
    "LBL_TABGROUP_ACTIVITIES" => array(
        'label' => 'LBL_TABGROUP_ACTIVITIES',
        'modules' => array(
            "Home",
            "Calendar",
            "Calls",
            "Meetings",
            "Emails",
            "Tasks",
            "Notes",
        )
    ),
    "LBL_TABGROUP_COLLABORATION"=>array(
        'label' => 'LBL_TABGROUP_COLLABORATION',
        'modules' => array(
            "Home",
            "Emails",
            "Documents",
            //BEGIN SUGARCRM flav!=sales ONLY
            "Project",
    	    //END SUGARCRM flav!=sales ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            "KBDocuments",
            //END SUGARCRM flav=pro ONLY
        )
    ),
//BEGIN SUGARCRM flav=pro || flav=sales ONLY
    "LBL_TABGROUP_REPORTS"=>array(
        'label' => 'LBL_TABGROUP_REPORTS',
        'modules' => array(
            "Home",
            "Reports",
            //BEGIN SUGARCRM flav!=sales ONLY
            "Forecasts",
            //END SUGARCRM flav!=sales ONLY
        )
    ),
//END SUGARCRM flav=pro || flav=sales ONLY
);

if(file_exists('custom/include/tabConfig.php')){
	require_once('custom/include/tabConfig.php');
}
?>
