<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// This function will update all related leads (those with a contact_id corresponding to that of this contact)

class ContactHooks
{
    function updateRelatedLeads(&$bean, $event, $arguments)
    {
        global $current_user;
        if ($event == "before_save") {
            if (!empty($bean->fetched_row)) { // For existing records only
                require_once('modules/LeadContacts/LeadContact.php');
                require_once('modules/LeadAccounts/LeadAccount.php');

                $lead_query =
                        "select leadcontacts.id leadcontact_id, leadaccounts.id leadaccount_id \n" .
                                "from leadcontacts inner  join leadaccounts on leadcontacts.leadaccount_id = leadaccounts.id \n" .
                                "where leadcontacts.contact_id = '{$bean->id}' and leadcontacts.deleted = 0 and leadaccounts.deleted = 0";

                $lead_res = $GLOBALS['db']->query($lead_query);
                while ($lead_row = $GLOBALS['db']->fetchByAssoc($lead_res)) {
                    if (!empty($lead_row['leadcontact_id'])) {
                        $theLead = new LeadContact();
                        $theLead->retrieve($lead_row['leadcontact_id']);
                        if (isset($theLead->assigned_user_id) && isset($theLead->team_id) && ($theLead->assigned_user_id != $bean->assigned_user_id || $theLead->team_id != $bean->team_id)) {
                            $theLead->assigned_user_id = $bean->assigned_user_id;
                            $theLead->team_id = $bean->team_id;
                            $theLead->save(false);
                        }
                    }

                    if (!empty($lead_row['leadaccount_id'])) {
                        $theLeadAccount = new LeadAccount();
                        $theLeadAccount->retrieve($lead_row['leadaccount_id']);
                        if (isset($theLeadAccount->assigned_user_id) && isset($theLeadAccount->team_id) && ($theLeadAccount->assigned_user_id != $bean->assigned_user_id || $theLeadAccount->team_id != $bean->team_id)) {
                            $theLeadAccount->assigned_user_id = $bean->assigned_user_id;
                            $theLeadAccount->team_id = $bean->team_id;
                            $theLeadAccount->save(false);
                        }
                    }
                }
            }
        }
    }


/*
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 110
 * commented out for moofcart, handling support_authorized_c differently

    //DEE CUSTOMIZATION - CHANGING WORKFLOW DEF TO LOGIC HOOK FOR ITREQUEST 6945. FIX FOR ITREQUEST 11452
    function setSupportAuthorized(&$bean, $event, $arguments)
    {
        if ($event == "before_save") {
            if (isset($bean->portal_active) && !empty($bean->portal_active) && isset($bean->portal_name) && !empty($bean->portal_name)) {
                if ($bean->portal_active == '1') {
                    $bean->support_authorized_c = 1;
                }
            }
        }
    }
*/

    /**
     * Backend check on the protal_name just to make sure that it's not already taken
     * this is just a backup case if javascript is disabled
     *
     * @author jwhitcraft
     * @project moofcart
     * @tasknum 96
     * @param Contact $bean
     * @param string $event
     * @param array $arguments
     * @return bool
     */
    function checkPortalUserName(&$bean, $event, $arguments)
    {
        if($event !== "before_save" || $bean->portal_name == '') return false;

        // portal name is the same as it was...just return true;
        if($bean->portal_name == $bean->fetched_row['portal_name']) return true;

        $sql = "SELECT id FROM contacts WHERE portal_name='" . $bean->portal_name . "' and deleted = '0'";

        $db = &DBManagerFactory::getInstance();

        $result = $db->query($sql);
        while($row = $db->fetchByAssoc($result)) {
            if($bean->id == $row['id']) continue;
            sugar_die('Portal Name is already taken.  Please press the back button and try again.');
            break;
        }

        return true;
    }

}
