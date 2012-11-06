<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('data/SugarBeanApiHelper.php');

class MeetingsApiHelper extends SugarBeanApiHelper
{
    /**
     * This function adds the Meetings specific saves for leads, contacts, and users on a call also updates the vcal
     * @param SugarBean $bean
     * @param array $submittedData
     * @param array $options
     * @return array
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array())
    {
        $data = parent::populateFromApi($bean, $submittedData, $options);

        if($bean->status != 'Held') {
            $userInvitees = $submittedData['user_invitees'];
            $contactInvitees = $submittedData['contact_invitees'];
            $leadInvitees = $submittedData['lead_invitees'];

            $existingUsers = $submittedData['existing_invitees'];
            $existingContacts = $submittedData['existing_contact_invitees'];
            $existingLeads =  $submittedData['existing_lead_invitees'];

            if (!is_array($userInvitees)) {
                $userInvitees = explode(',', trim($userInvitees, ','));
            }
            if (!is_array($existingUsers)) {
                $existingUsers =  explode(",", trim($existingUsers, ','));
            }

            if (!is_array($contactInvitees)) {
                $contactInvitees = explode(',', trim($contactInvitees, ','));
            }
            if (!is_array($existingContacts)) {
                $existingContacts =  explode(",", trim($existingContacts, ','));
            }

            if (!empty($submittedData['relate_to']) && $submittedData['relate_to'] == 'Contacts') {
                if (!empty($submittedData['relate_id']) && !in_array($submittedData['relate_id'], $contactInvitees)) {
                    $contactInvitees[] = $submittedData['relate_id'];
                }
            }

            //BEGIN SUGARCRM flav!=sales ONLY
            if (!is_array($leadInvitees)) {
                $leadInvitees = explode(',', trim($leadInvitees, ','));
            }
            if (!is_array($existingLeads)) {
                $existingLeads =  explode(",", trim($existingLeads, ','));
            }

            if (!empty($submittedData['relate_to']) && $submittedData['relate_to'] == 'Leads') {
                if (!empty($submittedData['relate_id']) && !in_array($submittedData['relate_id'], $leadInvitees)) {
                    $leadInvitees[] = $submittedData['relate_id'];
                }
            }
            //END SUGARCRM flav!=sales ONLY


            if(!in_array($GLOBALS['current_user']->id, $userInvitees)) {
                $userInvitees[] = $GLOBALS['current_user']->id;
            }


            // Call the Call module's save function to handle saving other fields besides
            // the users and contacts relationships

            $bean->update_vcal = false;    // Bug #49195 : don't update vcal b/s related users aren't saved yet, create vcal cache below

            $bean->users_arr = $userInvitees;
            $bean->contacts_arr = $contactInvitees;
            $bean->leads_arr = $leadInvitees;

            $bean->save(true);

            $bean->setUserInvitees($userInvitees, $existingUsers);
            $bean->setContactInvitees($contactInvitees, $existingContacts);

            //BEGIN SUGARCRM flav!=sales ONLY
            $bean->setLeadInvitees($leadInvitees, $existingLeads);
            //END SUGARCRM flav!=sales ONLY


            vCal::cache_sugar_vcal($GLOBALS['current_user']);
        }


        return $data;
    }


}
