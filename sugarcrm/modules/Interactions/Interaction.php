<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/SugarObjects/templates/basic/Basic.php');

class Interaction extends Basic {
    /**
     * table fields
     */
    public $id;
    public $touchpoint_id;
    public $touchpoint_name;
    public $name;
    public $type;
    public $parent_id;
    public $parent_type;
    public $start_date;
    public $end_date;
    public $description;
    public $score;
    public $scrub_complete_date;
    public $created_by;
    public $modified_user_id;
    public $team_id;

    /**
     * properties
     */
    public $table_name = "interactions";
    public $object_name = "Interaction";
    public $object_names = "Interactions";
    public $module_dir = "Interactions";
    public $importable = false;
    public $new_schema = true;

    /**
     * Constructor
     */
    public function __construct() {
        parent::SugarBean();
    }

    /**
     * @see SugarBean::fill_in_additional_list_fields()
     */
    public function fill_in_additional_list_fields() {
        $this->fill_in_additional_detail_fields();
    }

    /**
     * @see SugarBean::fill_in_additional_detail_fields()
     */
    public function fill_in_additional_detail_fields() {
        if (isset($this->source_id)
                && ($sourceBean = loadBean($this->source_module)) != FALSE) {
            $sourceBean->retrieve($this->source_id);
            $this->source_name = $sourceBean->name;
            if ($this->source_module == 'Touchpoints')
                $this->touchpoint_title = $sourceBean->title;
        }
        if (isset($this->parent_id)
                && ($sourceBean = loadBean($this->parent_type)) != FALSE) {
            $sourceBean->retrieve($this->parent_id);
            $this->parent_name = $sourceBean->name;
        }
    }

    /**
     * @see SugarBean::get_list_view_data()
     */
    public function get_list_view_data() {
        $temp_array = $this->get_list_view_array();

        $this->fill_in_additional_list_fields();

        return $temp_array;
    }

    /**
     * @see SugarBean::save()
     */
    public function save(
        $check_notify = false
    ) {
        // Handle scoring
        require_once('modules/Score/Score.php');
        Score::scoreBean($this);
        // Finished with scoring

        // BEGIN SADEK CUSTOMIZATION: IT REQUEST 13338 - PARDOT - WE NEED TO CREATE A TASK ON THE OPP
        $check_for_task = false;
        if ((empty($this->fetched_row['campaign_id']) || $this->fetched_row['campaign_id'] != '27c5bb36-a021-0835-7d82-43742c76164d') &&
                $this->campaign_id == '27c5bb36-a021-0835-7d82-43742c76164d' && !empty($this->source_id)) {
            $check_for_task = true;
        }
        // END SADEK CUSTOMIZATION: IT REQUEST 13338 - PARDOT - WE NEED TO CREATE A TASK ON THE OPP

        $my_id = parent::save($check_notify);

        // BEGIN SADEK CUSTOMIZATION: IT REQUEST 13338 - PARDOT - WE NEED TO CREATE A TASK ON THE OPP
        if ($check_for_task) {
            require_once('modules/Touchpoints/Touchpoint.php');
            $touchpoint = new Touchpoint();
            $touchpoint->disable_row_level_security = true;
            $touchpoint->retrieve($this->source_id);
            if (!empty($touchpoint->id) && !empty($touchpoint->new_leadcontact_id)) {
                createOppTaskFromTouchpoint($touchpoint->id, $touchpoint->new_leadcontact_id);
            }
            unset($touchpoint);
        }
        // END SADEK CUSTOMIZATION: IT REQUEST 13338 - PARDOT - WE NEED TO CREATE A TASK ON THE OPP

        // SUGARINTERNAL CUSTOMIZATION - jwhitcraft 6.8.10 to make sure that each touchpoint has at least one open opportunity on it
        // ITR: 14213
        if (empty($this->campaign_id) === false && empty($this->source_id) === false && $this->type == "Form Handler" && $this->deleted == 0) {
            syslog(LOG_DEBUG, 'ScrubRouter - Starting Scrub Routing to create a new opp');
            require_once("custom/si_logic_hooks/Touchpoints/ScrubRouting.php");
            $ScrubRouting = new ScrubRouting();
            $ScrubRouting->startRouting($this->source_id, 'after_scrub', array('ignorePartnerCampaign' => true));
            unset($ScrubRouting);
        }
        // END SUGARINTERNAL CUSTOMIZATION

        return $my_id;
    }

    /**
     * @see SugarBean::bean_implements()
     */
    public function bean_implements(
        $interface
    ) {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

}
