<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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
/*********************************************************************************

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/json_config.php');
require_once('include/MVC/View/views/view.detail.php');

class ITRequestsViewDetail extends ViewDetail {

    function ITRequestsViewDetail(){
        parent::ViewDetail();
    }

    /**
     * display
     *
     * We are overridding the display method to manipulate the sectionPanels.
     * If portal is not enabled then don't show the Portal Information panel.
     */
    function display() {
        $this->dv->process();

        require_once("include/TimeDate.php");
        if(!empty($this->dv->fieldDefs['development_time']['value']) && !empty($this->dv->fieldDefs['target_date']['value'])){
            $days_prior = floor($this->dv->fieldDefs['development_time']['value'] / 8);
            $start_date = $this->dv->fieldDefs['target_date']['value'];
            if($days_prior != 0){
                $timedate = new TimeDate();
                $current_user_format = $timedate->get_date_format($GLOBALS['current_user']);
                $user_date = $timedate->swap_formats($this->dv->fieldDefs['target_date']['value'],
                        $current_user_format,
                        "Y-m-d");
                $user_date_arr = explode("-", $user_date);
                $user_date_arr[2] -= $days_prior;
                $start_date = implode('-', $user_date_arr);
                $start_date = $timedate->swap_formats($start_date,
                        "Y-m-d",
                        $current_user_format);
            }
            $this->dv->fieldDefs['start_date']['value'] = $start_date;
        }

        // since the resolve date was stored in UTC format we need to adjust it based on the current users
        // time zone
        // ITR: 13889 and 8785
/*        if(!empty($this->dv->fieldDefs['date_resolved']['value'])) {
            $timedate = new TimeDate();
            $current_user_format = $timedate->get_date_time_format(true, $GLOBALS['current_user']);
            $this->dv->fieldDefs['date_resolved']['value'] = $timedate->handle_offset(
                        $this->dv->fieldDefs['date_resolved']['value'],
                        $current_user_format,
                        true,
                        $GLOBALS['current_user']
                );
        }
*/

        // ITR: 14723 - jwhitcraft - This gets done by default on the render dont need to do it here too.
        #$this->dv->fieldDefs['description']['value'] = url2html($this->dv->fieldDefs['description']['value']);

        if(preg_match_all("/itrequest[s]?\s[#]?([0-9]+)/i", $this->dv->fieldDefs['resolution']['value'], $pregmatches, PREG_OFFSET_CAPTURE)){
            for($idx = 0; $idx < count($pregmatches[0]); $idx++){
                $itres = $GLOBALS['db']->query("select id from itrequests where itrequest_number = '{$pregmatches[1][$idx][0]}' and deleted='0'");
                if($itres){
                    $row = $GLOBALS['db']->fetchByAssoc($itres);
                    $itr_id = $row['id'];
                    $this->dv->fieldDefs['resolution']['value'] = str_replace($pregmatches[0][$idx][0], "<a href=\"index.php?module=ITRequests&action=DetailView&record={$itr_id}\">{$pregmatches[0][$idx][0]}</a>", $this->dv->fieldDefs['resolution']['value']);
                }
            }
        }
        if(preg_match_all("/bug[s]?\s[#]?([0-9]+)/i", $this->dv->fieldDefs['resolution']['value'], $pregmatches, PREG_OFFSET_CAPTURE)){
            for($idx = 0; $idx < count($pregmatches[0]); $idx++){
                $bugres = $GLOBALS['db']->query("select id from bugs where bug_number = '{$pregmatches[1][$idx][0]}' and deleted='0'");
                if($bugres){
                    $row = $GLOBALS['db']->fetchByAssoc($bugres);
                    $bug_id = $row['id'];
                    $this->dv->fieldDefs['resolution']['value'] = str_replace($pregmatches[0][$idx][0], "<a href=\"index.php?module=Bugs&action=DetailView&record={$bug_id}\">{$pregmatches[0][$idx][0]}</a>", $this->dv->fieldDefs['resolution']['value']);
                }
            }
        }
        // ITR: 14723 - jwhitcraft - This gets done by default on the render dont need to do it here too.
        //$this->dv->fieldDefs['resolution']['value'] = url2html($this->dv->fieldDefs['resolution']['value']);

        echo $this->dv->display();
    }

}

?>
