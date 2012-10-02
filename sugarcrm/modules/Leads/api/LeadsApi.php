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

require_once('modules/Campaigns/utils.php');
require_once('include/api/ModuleApi.php');

class LeadsApi extends ModuleApi {
    public function registerApiRest() {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Leads'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new Lead record with option to add Target & Email relationships',
                'longHelp' => 'modules/Leads/api/help/LeadsApi.html',
            ),
        );
    }

    /**
     * Create the lead record and optionally perform post-save actions for Convert Target & Lead from Email cases
     */
    public function createRecord($api, $args) {
        //create the lead using the ModuleApi
        $data = parent::createRecord($api, $args);

        $leadId = null;
        if (isset($data['id']) && !empty($data['id'])) {
            $leadId = $data['id'];
        } else {
            //lead not created, can't do post-processes - bail out
            return $data;
        }

        //handle Convert Target/Prospect use case
        if (isset($args['prospect_id']) && !empty($args['prospect_id'])) {
            $campaignId = (isset($data['campaign_id'])) ? $data['campaign_id'] : null;
            $this->convertProspect($args['prospect_id'], $leadId, $campaignId);
        }

        //handle Create Lead from Email use case
        if (isset($args['inbound_email_id']) && !empty($args['inbound_email_id'])) {
            $this->linkLeadToEmail($args['inbound_email_id'], $leadId);
        }

        return $data;
    }

    /**
     * Convert Target/Prospect to a Lead by linking the lead to the prospect
     * and creating a campaign log entry if newly created Lead is related to a campaign.
     * TODO: This logic is brought over from LeadFormBase->handleSave() - need refactoring to use Link2?
     *
     * @param $prospectId
     * @param $leadId
     * @param null $campaignId
     */
    protected function convertProspect($prospectId, $leadId, $campaignId = null) {
        $prospect = new Prospect();
        $prospect->retrieve($prospectId);
        $prospect->lead_id = $leadId;
        // Set to keep email in target
        $prospect->in_workflow = true;
        $prospect->save();

        if (!empty($campaignId)) {
            $lead = new Lead();
            $lead->id = $leadId;
            campaign_log_lead_or_contact_entry($campaignId, $prospect, $lead, 'lead');
        }
    }

    /**
     * Link the Lead to the Email from which the lead was created
     * Also set the assigned user to current user and mark email as read.
     * TODO: This logic is brought over from LeadFormBase->handleSave() - need refactoring to use Link2?
     *
     * @param $emailId
     * @param $leadId
     */
    protected function linkLeadToEmail($emailId, $leadId) {
        global $current_user;

        $email = new Email();
        $email->retrieve($emailId);
        $email->parent_type = 'Leads';
        $email->parent_id = $leadId;
        $email->assigned_user_id = $current_user->id;
        $email->status = 'read';
        $email->save();

        $email->load_relationship('leads');
        $email->leads->add($leadId);
    }
}
