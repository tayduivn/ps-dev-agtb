<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 ********************************************************************************/

class OpportunityLineBundle extends SugarBean
{
    var $table_name = "opportunity_line_bundles";
    var $module_dir = 'OpportunityLineBundles';
    var $object_name = "OpportunityLineBundle";
    var $rel_opportunity_lines = "opp_line_bundle_opp_line";
    var $rel_opportunities = "opp_line_bundle_opp";

    /**
     * set_opportunitylinebundle_opportunityline_relationship
     *
     * @param $opportunity_line_id
     * @param $opportunity_line_index
     * @param $opportunity_line_bundle_id
     */
    public function set_opportunitylinebundle_opportunityline_relationship($opportunity_line_id, $opportunity_line_index, $opportunity_line_bundle_id)
    {
        if(empty($opportunity_line_bundle_id)){
      	   $opportunity_line_bundle_id = $this->id;
      	}
        $query = "INSERT INTO $this->rel_opportunity_lines (id, opportunity_line_id, opportunity_line_index, bundle_id, deleted, date_modified) VALUES ('".create_guid()."','$opportunity_line_id', $opportunity_line_index, '$opportunity_line_bundle_id', 0, ".db_convert("'".TimeDate::getInstance()->nowDb()."'", 'datetime').")";
        if(!empty($GLOBALS['app_strings']['ERR_DATABSE_RELATIONSHIP_QUERY']))
        {
            $this->db->query($query,true,string_format($GLOBALS['app_strings']['ERR_DATABSE_RELATIONSHIP_QUERY'], array($this->rel_opportunity_lines, $query)));
        } else {
            $this->db->query($query,true);
        }
    }

    public function clear_opportunitylinebundle_opportunityline_relationship($bundle_id)
    {
        $query = "delete from $this->rel_opportunity_lines where (bundle_id='$bundle_id') and deleted=0";
        $this->db->query($query,true,"Error clearing line bundle to line relationship: ");
    }

    public function clear_line_linebundle_relationship($line_id)
    {
        $query = "delete from $this->rel_opportunity_lines where opportunity_line_id='$line_id' and bundle_id = '$this->id' and deleted=0";
        $this->db->query($query,true,"Error clearing line item to oppBundle relationship: ");
    }

    /**
     * set_opportunitylinebundle_opportunity_relationship
     *
     * @param $opportunity_id
     * @param $bundle_id
     * @param $bundle_index
     */
    public function set_opportunitylinebundle_opportunity_relationship($opportunity_id, $bundle_id, $bundle_index)
    {
        if(empty($bundle_id)){
      	   $bundle_id = $this->id;
      	}
        $query = "INSERT INTO $this->rel_opportunities (id, opportunity_id, bundle_id, bundle_index, deleted, date_modified) VALUES ('".create_guid()."','$opportunity_id', '$bundle_id', $bundle_index, 0, ".db_convert("'".TimeDate::getInstance()->nowDb()."'", 'datetime').")";
        if(!empty($GLOBALS['app_strings']['ERR_DATABSE_RELATIONSHIP_QUERY']))
        {
            $this->db->query($query,true,string_format($GLOBALS['app_strings']['ERR_DATABSE_RELATIONSHIP_QUERY'], array($this->rel_opportunities, $query)));
        } else {
            $this->db->query($query,true);
        }
    }

    function clear_opportunitylinebundle_opportunity_relationship($opp_id)
    {
        $query = "DELETE FROM $this->rel_opportunities WHERE (opportunity_id='$opp_id')";
        $this->db->query($query,true,"Error clearing line bundle to opp relationship: ");
    }

    function get_line_items()
    {
        // First, get the list of IDs.
        $query = "SELECT opportunity_line_id AS id FROM  $this->rel_opportunity_lines WHERE bundle_id='$this->id' AND deleted=0 ORDER BY opportunity_line_index";
        return $this->build_related_list($query, new OpportunityLine());
    }
}
