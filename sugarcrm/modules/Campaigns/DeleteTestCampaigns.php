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

    if($focus->db->getScriptName() == 'mysql'
       //BEGIN SUGARCRM flav=ent ONLY
        || $focus->db->getScriptName() == 'IBM_DB2'
       //END SUGARCRM flav=ent ONLY
    )
    {
        $query= "update emails
                inner join campaign_log on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}'
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                set emails.deleted=1";
    } else {
        $query = "update emails
                set emails.deleted=1
                from emails inner join campaign_log
                on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}'
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'";
    }

    $focus->db->query($query);

    if($focus->db->getScriptName() == 'mysql'
       //BEGIN SUGARCRM flav=ent ONLY
        || $focus->db->getScriptName() == 'IBM_DB2'
       //END SUGARCRM flav=ent ONLY
    )
    {
        $query = "delete emailman.* from emailman
                inner join prospect_lists on emailman.list_id = prospect_lists.id and prospect_lists.list_type='test'
                WHERE emailman.campaign_id = '{$focus->id}'";
    } else {
        $query = "delete from emailman
                from emailman
                inner join prospect_lists on emailman.id = prospect_lists.id and prospect_lists.list_type='test'
                and emailman.campaign_id = '{$focus->id}'";
    }

    $focus->db->query($query);

    if($focus->db->getScriptName() == 'mysql'
       //BEGIN SUGARCRM flav=ent ONLY
        || $focus->db->getScriptName() == 'IBM_DB2'
       //END SUGARCRM flav=ent ONLY
    )
    {
        $query = "update campaign_log
                inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                set campaign_log.deleted=1
                where campaign_log.campaign_id='{$focus->id}'";
    } else {
        $query = "update campaign_log
                set campaign_log.deleted=1
                from campaign_log inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test'
                where campaign_log.campaign_id='{$focus->id}'";
    }

    $focus->db->query($query);
}

}