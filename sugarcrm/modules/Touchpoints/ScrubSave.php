<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
$touchpoint_id = $_REQUEST['record'];
$temp_tp = new Touchpoint();
$temp_tp->retrieve($touchpoint_id);
if (!empty($_POST['third_party_validation_c']) && ($_POST['third_party_validation_c'] == 'on' || $_POST['third_party_validation_c'] == '1')) {
    if (!empty($temp_tp->id)) {
        $temp_tp->third_party_validation_c = 1;
        $temp_tp->save(false, false);
    }
}

require_once('custom/si_custom_files/custom_functions.php');
$temp_tp->lead_group_c = !empty($_REQUEST['lead_group_c']) ? $_REQUEST['lead_group_c'] : $temp_tp->lead_group_c;
$temp_tp->assigned_user_id = !empty($_REQUEST['assigned_user_id']) ? $_REQUEST['assigned_user_id'] : $temp_tp->assigned_user_id;
$temp_tp->primary_address_country = !empty($_REQUEST['primary_address_country']) ? $_REQUEST['primary_address_country'] : $temp_tp->primary_address_country;
$temp_tp->primary_address_state = !empty($_REQUEST['primary_address_state']) ? $_REQUEST['primary_address_state'] : $temp_tp->primary_address_state;
$return_assignment = siGetSalesAssignmentMap($temp_tp, 'Manual');

if (!empty($return_assignment)) {
    $_REQUEST['assigned_user_id'] = $return_assignment['assigned_user_id'];
    $_POST['assigned_user_id'] = $return_assignment['assigned_user_id'];
    $temp_tp->assigned_user_id = $return_assignment['assigned_user_id'];
    $temp_tp->save(false, false);
}

//echo $touchpoint_id."::".$_REQUEST['parent_lead_action']."::".$_REQUEST['parent_lead_id']."::".$_REQUEST['parent_contact_id']."<BR>";
if (isset($_REQUEST['parent_lead_action'])) {
    require('modules/Touchpoints/ScrubMetaData.php');

    $override_data = array();
    $do_no_override_assigned = array('c15afb6d-a403-b92a-f388-4342a492003e', 'bf6f1e6b-f6bf-01e5-69e3-4a833bf57cfd', '2c780a1f-1f07-23fd-3a49-434d94d78ae5');
    foreach ($_POST as $key => $value) {
        if (in_array($key, $ignore_post_fields)) {
            continue;
        }
        if ($key == 'assigned_user_id' && in_array($_POST['assigned_user_id'], $do_no_override_assigned)) {
            continue;
        }
        $override_data[$key] = $value;
    }

    require_once('modules/Touchpoints/ScrubHelper.php');
    $ScrubHelper = new ScrubHelper();
    $scrubResultAction = '';
    $parent_id = '';
    $parent_type = '';
    $discrepancy_array = array();

    if ($_REQUEST['parent_lead_action'] == 'set_as_parent') {
        $scrubResultAction = 'manual_found_is_parent';
    }
    else if ($_REQUEST['parent_lead_action'] == 'set_as_child_Parent') {
        $scrubResultAction = 'manual_found_lead';
        $parent_id = $_REQUEST['parent_lead_id'];
        $parent_type = 'LeadContact';
    }
    else if ($_REQUEST['parent_lead_action'] == 'set_as_leadaccount') {
        $scrubResultAction = 'manual_found_leadaccount';
        $parent_id = $_REQUEST['parent_leadaccount_id'];
        $parent_type = 'LeadAccount';
    }
    else if ($_REQUEST['parent_lead_action'] == 'set_as_contact') {
        $scrubResultAction = 'manual_found_contact';
        $parent_id = $_REQUEST['parent_contact_id'];
        $parent_type = 'Contact';
    }
    else if ($_REQUEST['parent_lead_action'] == 'set_as_account') {
        $scrubResultAction = 'manual_found_account';
        $parent_id = $_REQUEST['parent_account_id'];
        $parent_type = 'Account';
    }
    else {
        sugar_die('Please make sure you enter a value for the "Parent" field on the previous page.');
    }

    $rescrub = false;
    if (isset($_REQUEST['rescrub']) && $_REQUEST['rescrub'] == 'true') {
        $rescrub = true;
    }

    // If the $parent_type is not empty, then we should test for discrepancies
    if (!empty($parent_type)) {
        $discrepancy_array = $ScrubHelper->getDiscrepancyArray($touchpoint_id, $parent_id, $parent_type, $override_data);
    }
    // If we are not ignoring discrepancies
    if (!isset($_REQUEST['ignore_discrepancy']) || $_REQUEST['ignore_discrepancy'] == 'false') {
        // If we found discrepancies, we display the form
        if (!empty($discrepancy_array)) {
            require_once('modules/Touchpoints/ScrubFunctions.php');
            $form = getDiscrepancyForm($touchpoint_id, $discrepancy_array, $override_data);
            $rescrub_get = ($rescrub ? "&rescrub=true" : "");
            $return_module = (!empty($_REQUEST['return_module']) ? "&return_module={$_REQUEST['return_module']}" : "");
            $return_action = (!empty($_REQUEST['return_action']) ? "&return_action={$_REQUEST['return_action']}" : "");
            $return_id = (!empty($_REQUEST['return_id']) ? "&return_id={$_REQUEST['return_id']}" : "");
            echo "<h3>Scrub Discrepancies</h3>\n";
            echo "There were some discrepancies in the data merge. Please select the appropriate values to go into the parent record in the form below.<BR>\n";
            echo "<form method=post action=index.php?module=Touchpoints&action=ScrubSave&record={$_REQUEST['record']}&ignore_discrepancy=true{$rescrub_get}{$return_module}{$return_action}{$return_id}>\n";
            echo $form;
            echo "</form>";
            sugar_die('');
        }
    }


    $ScrubHelper->manualScrub($touchpoint_id, $scrubResultAction, $parent_id, $override_data, $rescrub);

    // begin jwhitcraft customization
    // run the scrub router
    if($scrubResultAction != "manual_found_is_parent") {
        require_once("custom/si_logic_hooks/Touchpoints/ScrubRouting.php");
        $ScrubRouting = new ScrubRouting();
        $ScrubRouting->startRouting($touchpoint_id, 'after_scrub', array('parent_id' => $parent_id));
        unset($ScrubRouting);
    }
    //end jwhitcraft customization

    // IT REQUEST 12214 - Call this to create interactions from pardot activities
    require_once('scripts/pardot/PardotHelper.php');
    PardotHelper::updateProspectActivities($touchpoint_id);
}

// BEGIN IT Request 8052 - Do an auto scrub after scrubbing a touchpoint in case there is another touchpoint with the same email address that came in
if (!empty($temp_tp->email1) || !empty($temp_tp->portal_name)) {
    $email_condition = !empty($temp_tp->email1) ? "touchpoints.email1 = '{$temp_tp->email1}'" : "";
    $portal_condition = !empty($temp_tp->portal_name) ? "touchpoints.portal_name = '{$temp_tp->portal_name}'" : "";
    if (!empty($email_condition) && !empty($portal_condition))
        $condition = "( $email_condition or $portal_condition )";
    else if (!empty($email_condition))
        $condition = $email_condition;
    else
        $condition = $portal_condition;

    $touchpoint_query = "select touchpoints.id from touchpoints where $condition and scrubbed = 0 and deleted = 0 and touchpoints.id != '{$temp_tp->id}' ";

    $additional_res = $GLOBALS['db']->query($touchpoint_query);
    while ($additional_row = $GLOBALS['db']->fetchByAssoc($additional_res)) {
        $scrub_tp = new Touchpoint();
        $scrub_tp->retrieve($additional_row['id']);
        $scrub_tp->scrub();
    }
}
// END IT Request 8052

//** BEGIN CUSTOMIZATION EDDY IT TIX 13018 - assign partner values to related opportunities if partner values exist
//retrieve temp_tp to get updated values from previous scrubbing
/*$temp_tp->retrieve($touchpoint_id);
//now that touchpoint has been saved and scrubbed, check to see if it and all related opportunities should belong to a partner 
if (!empty($temp_tp->partner_assigned_to_c) && (!empty($temp_tp->new_leadcontact_id) || !empty($temp_tp->new_leadaccount_id))) {
    //grab the lead account id if specified
    $nla_id = $temp_tp->new_leadaccount_id;

    //if lead account id is empty, then get contact id to grab lead account id
    if (empty($nla_id) && !empty($temp_tp->new_leadcontact_id)) {
        require_once('modules/LeadContacts/LeadContact.php');
        $nlc = new LeadContact();
        $nlc->retrieve($temp_tp->new_leadcontact_id);
        $nla_id = $nlc->leadaccount_id;
    }

    //if lead account id was found, then process related opportunities
    if (!empty($nla_id)) {
        require_once('modules/LeadAccounts/LeadAccount.php');

        //retrieve the LeadAccount
        $la_opps = array();
        $nla = new LeadAccount();
        $nla->retrieve($nla_id);
        $nla_opps = $nla->get_linked_beans('opportunities', 'Opportunity');

        //iterate through opportunity id's related to this lead account
        foreach ($nla_opps as $relOpp) {
            //if there is already a parnter assigned, then skip this opportunity
            if (!empty($relOpp->partner_assigned_to_c)) continue;
            //retrieve the opportunity, set the partner values, and save
            $relOpp->partner_assigned_to_c = $temp_tp->partner_assigned_to_c;
            $relOpp->accepted_by_partner_c = 'Y';
            $relOpp->save();
        }

        //if the lead account has an account id, then
        //retrieve opportunities related to this account  and set partner values
        if (!empty($nla->account_id)) {
            require_once('modules/Accounts/Account.php');
            $na = new Account();
            $na->retrieve($nla->account_id);
            $la_opps = $na->get_linked_beans('opportunities', 'Opportunity');

            //set partner values
            foreach ($la_opps as $relOpp) {
                if (!empty($relOpp->partner_assigned_to_c)) continue;
                $relOpp->partner_assigned_to_c = $temp_tp->partner_assigned_to_c;
                $relOpp->accepted_by_partner_c = 'Y';
                $relOpp->save();
            }
        }

    }
}*/
//** END CUSTOMIZATION EDDY IT TIX 13018

require_once('include/MVC/SugarApplication.php');
if (!empty($_REQUEST['return_module']) && $_REQUEST['return_module'] != 'LeadQualScoredLead') {
    $module = (!empty($_REQUEST['return_module']) ? "module={$_REQUEST['return_module']}" : "");
    $action = (!empty($_REQUEST['return_action']) ? "&action={$_REQUEST['return_action']}" : "&action=index");
    $record = (!empty($_REQUEST['return_id']) ? "&record={$_REQUEST['return_id']}" : "");
    $user_queue = (!empty($_SESSION['lead_qual_bucket']) && !empty($_SESSION['lead_qual_bucket']['user']) ? "&user={$_SESSION['lead_qual_bucket']['user']}" : "");
    SugarApplication::redirect("index.php?{$module}{$action}{$record}{$user_queue}");
}
else if (!isset($_SESSION['lead_qual_bucket'])) {
    SugarApplication::redirect('index.php?module=Touchpoints&action=LeadQualScoredLead&user=c15afb6d-a403-b92a-f388-4342a492003e');
}
else {
    $user = $_SESSION['lead_qual_bucket']['user'];
    SugarApplication::redirect("index.php?module=Touchpoints&action=LeadQualScoredLead&user=$user");
}
