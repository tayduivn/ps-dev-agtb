<?php
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

/**
 * DeleteTestCampaigns.php
 *
 * This is a class to encapsulate deleting test campaigns
 * @author Collin Lee
 */
class DeleteTestCampaigns {

/**
 * deleteTestRecords
 *
 * This method deletes the test records for a given Campaign instance
 * @param Campaign $focus The Campaign instance
 */
function deleteTestRecords($focus)
{
    if(empty($focus) || empty($focus->id))
    {
        return;
    }

    if($focus->db->getScriptName() == 'mysql')
    {
        $query = "update emails
                inner join campaign_log on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}'
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                set emails.deleted=1";

//BEGIN SUGARCRM flav=ent ONLY
    } else if ($focus->db->getScriptName() == 'ibm_db2') {
        $query = "update emails e
                set deleted = 1
                where id in (
                select campaign_log.related_id from campaign_log
                left join emails on campaign_log.related_id = e.id and campaign_log.campaign_id = '{$focus->id}'
                left join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test')";
    } else if ($focus->db->getScriptName() == 'oci8') {
        $query = "update emails
                set deleted = 1
                where id in (
                select campaign_log.related_id from campaign_log
                left join emails on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}'
                left join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test')";
//END SUGARCRM flav=ent ONLY
    } else {
        $query = "update emails
                set emails.deleted=1
                from emails inner join campaign_log
                on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}'
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'";
    }

    $focus->db->query($query);

    if($focus->db->getScriptName() == 'mysql')
    {
        $query = "delete emailman.* from emailman
                inner join prospect_lists on emailman.list_id = prospect_lists.id and prospect_lists.list_type='test'
                WHERE emailman.campaign_id = '{$focus->id}'";
    } else {
        $query = "delete from emailman
                where list_id IN (
                select prospect_list_id from prospect_list_campaigns
                inner join prospect_lists on prospect_list_campaigns.prospect_list_id = prospect_lists.id
                where prospect_lists.list_type='test' and prospect_list_campaigns.campaign_id = '{$focus->id}')";
    }

    $focus->db->query($query);

    if($focus->db->getScriptName() == 'mysql')
    {
        $query = "update campaign_log
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                set campaign_log.deleted=1
                where campaign_log.campaign_id='{$focus->id}'";
//BEGIN SUGARCRM flav=ent ONLY
    } else if ($focus->db->getScriptName() == 'ibm_db2' || $focus->db->getScriptName() == 'oci8') {
        $query = "update campaign_log c
                set c.deleted=1
                where c.list_id in (select prospect_lists.id
                from prospect_lists
                left join campaign_log on campaign_log.list_id = prospect_lists.id
                and prospect_lists.list_type='test'
                where campaign_log.campaign_id='{$focus->id}')";
//END SUGARCRM flav=ent ONLY
    } else {
        $query = "update campaign_log
                set campaign_log.deleted=1
                from campaign_log inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                where campaign_log.campaign_id='{$focus->id}'";
    }

    $focus->db->query($query);
}

}