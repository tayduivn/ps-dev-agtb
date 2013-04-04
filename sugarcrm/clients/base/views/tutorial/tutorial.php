<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['base']['view']['tutorial'] = array(
    'records' => array(
        'version' =>1,
        'content' => array(
            array(
                'name' => '.drawerTrig',
                'text' => 'LBL_TOUR_LIST_INT_TOGGLE',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.choice-related',
                'text' => 'LBL_TOUR_LIST_FILTER1',
                'full' => true,
                'vertAdj' => -15,
            ),
            array(
                'name' => '.choice-filter',
                'text' => 'LBL_TOUR_LIST_FILTER2',
                'full' => true,
                'vertAdj' => -15,
            ),
            array(
                'name' => '.filter-view .search-name',
                'text' => 'LBL_TOUR_LIST_FILTER_SEARCH',
                'full' => true,
                'vertAdj' => -15,
            ),
            array(
                'name' => '[data-view="activitystream"]',
                'text' => 'LBL_TOUR_LIST_ACTIVTYSTREAMLIST_TOGGLE',
                'full' => true,
                'horizAdj' =>5,
                'vertAdj' => -10,
            ),
            array(
                'name' => '[data-event="list:preview:fire"]',
                'text' => 'LBL_TOUR_LIST_FILTER_PREVIEW',
                'full' => true,
                'vertAdj' => -15,
            ),
        )
    ),
    'record' => array(
        'version' =>1,
        'content' => array(
            array(
                'name' => '[data-fieldname="first_name"]',
                'text' => 'LBL_TOUR_RECORD_INLINEEDIT',
                'full' => true,
                'horizAdj' =>-15,
                'vertAdj' => -13,
            ),
            array(
                'name' => '[data-fieldname="name"]',
                'text' => 'LBL_TOUR_RECORD_INLINEEDIT',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '[name="edit_button"]',
                'text' => 'LBL_TOUR_RECORD_ACTIONS',
                'full' => true,
                'horizAdj' =>-1,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.record .record-cell',
                'text' => 'LBL_TOUR_RECORD_INLINEEDITRECORD',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.show-hide-toggle',
                'text' => 'LBL_TOUR_RECORD_SHOWMORE',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '[data-view="subpanel"]',
                'text' => 'LBL_TOUR_RECORD_TOGGLEACTIVITIES',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.preview-headerbar .dropdown-toggle',
                'text' => 'LBL_TOUR_RECORD_DASHBOARDNAME',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.preview-headerbar .btn-toolbar',
                'text' => 'LBL_TOUR_RECORD_DASHBOARDACTIONS',
                'full' => true,
                'horizAdj' =>-11,
                'vertAdj' => -13,
            ),
            array(
                'name' => '.dashlet-cell .icon-cog',
                'text' => 'LBL_TOUR_RECORD_DASHLETCOG',
                'full' => true,
                'horizAdj' =>-18,
                'vertAdj' => -18,
            ),
        )
    ),
);
