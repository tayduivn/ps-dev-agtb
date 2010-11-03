<?php

require_once('custom/si_custom_files/MoofCartHelper.php');

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 61
 * get all the contacts tied to the partner
 */
$server->register(
    'partner_portal_get_all_contacts',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
//        array('return'=>'tns:get_opportunities_list_result'),
    $NAMESPACE);

function partner_portal_get_all_contacts($portal_name, $session)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $accountquery = "SELECT DISTINCT contacts.*, contacts_cstm.*, accounts.id AS account_id, accounts.name AS account_name
	
FROM  contacts
LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
LEFT JOIN accounts_contacts ON contacts.id=accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts_opportunities jtl0 ON accounts_contacts.account_id=jtl0.account_id  AND jtl0.deleted=0
LEFT JOIN opportunities ON opportunities.id = jtl0.opportunity_id AND opportunities.deleted=0
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts as partner_account_contacts ON partner_account_contacts.account_id=partner_account.id AND partner_account_contacts.deleted=0
LEFT JOIN contacts as partner_contact ON partner_contact.id = partner_account_contacts.contact_id AND partner_contact.deleted =0
LEFT JOIN contacts_cstm as partner_contact_cstm ON partner_contact.id = partner_contact_cstm.id_c
LEFT JOIN email_addr_bean_rel ON email_addr_bean_rel.bean_id = contacts.id AND email_addr_bean_rel.deleted=0
LEFT JOIN email_addresses ON email_addr_bean_rel.email_address_id=email_addresses.id AND email_addresses.deleted = 0


WHERE partner_contact.portal_name = '{$portal_name}' 
AND partner_contact.portal_active = 1 
AND partner_contact.deleted = 0
AND (partner_contact.id = opportunities_cstm.contact_id_c OR partner_contact_cstm.oppq_active_c = 1)
AND contacts.deleted = 0
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND contacts.id = accounts_contacts.contact_id
AND opportunities_cstm.accepted_by_partner_c IN ('Y','R','P')
AND opportunities.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'Finance Closed')

ORDER BY accounts.name
";
    $response = $GLOBALS['db']->query($accountquery);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Contacts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 61
 * get all accounts tied to the partner
 */

$server->register(
    'partner_portal_get_accounts_list',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'account_id' => 'xsd:string'),
    array('return' => 'xsd:string'),
//        array('return'=>'tns:get_opportunities_list_result'),
    $NAMESPACE);

function partner_portal_get_accounts_list($portal_name, $session, $account_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $filterSQL = '';

    if (isValidGUID($account_id)) {
        $filterSQL .= "AND accounts.id = '{$account_id}'";
    }

/*
    $accountquery = "
SELECT DISTINCT accounts.*
FROM accounts
LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c AND account_id_c IS NOT NULL
LEFT JOIN accounts AS partner_accounts ON accounts_cstm.account_id_c = partner_accounts.id AND partner_accounts.deleted = 0
LEFT JOIN accounts_contacts AS partner_account_contacts ON partner_account_contacts.account_id = partner_accounts.id AND partner_account_contacts.deleted=0 
LEFT JOIN contacts AS partner_contact ON partner_account_contacts.contact_id = partner_contact.id
WHERE partner_contact.portal_name = '{$portal_name}'
AND accounts.deleted = 0
{$filterSQL}
ORDER BY accounts.name
";
*/
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name
$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
	$partner_account_id = $row['id'];
	// if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
		return serialize(array());
	}
}

// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

$accountquery = "
SELECT DISTINCT accounts.*
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
WHERE partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND partner_account.id = '{$partner_account_id}'
AND opportunities.sales_stage IN ('Closed Won', 'Sales Ops Closed', 'Finance Closed')
AND opportunities.deleted =0
{$filterSQL}
ORDER BY accounts.name ASC
";

    //mail("jbartek@sugarcrm.com","account sql",$accountquery,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($accountquery);

    $info = array();

    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $id = $row['id'];
        $info[$id] = $row;
        if (isValidGUID($account_id)) {
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

            $opportunities_query = "
SELECT DISTINCT opportunities.*, opportunities_cstm.* 
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
WHERE partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND partner_account.id = '{$partner_account_id}'
AND opportunities.deleted =0
AND accounts.id = '{$account_id}'
";
            //	mail("jbartek@sugarcrm.com","sql",$opportunities_query,"From: Jim <jbartek@sugarcrm.com>");
            $opportunities_response = $GLOBALS['db']->query($opportunities_query);

            $info[$account_id]['opportunities'] = array();

            while ($or = $GLOBALS['db']->fetchByAssoc($opportunities_response)) {
                $info[$account_id]['opportunities'][$or['id']] = $or;
            }

        }
        else {
            //get total pending opps
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

            $pending_opp_query = "SELECT DISTINCT  count(*) as total
	FROM opportunities
	LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
	LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
	LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
	LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
	WHERE partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
	AND partner_account.id = '{$partner_account_id}'
	AND opportunities_cstm.accepted_by_partner_c = 'P'
	AND opportunities.deleted =0
	AND accounts.id = '{$id}'
	";
            //		mail("jbartek@sugarcrm.com","sql",$pending_opp_query,"From: Jim <jbartek@sugarcrm.com>");
            $pending_opp_response = $GLOBALS['db']->query($pending_opp_query);
            $pending_opps = array();
            while ($por = $GLOBALS['db']->fetchByAssoc($pending_opp_response)) {
                $info[$id]['pending_opps'] = $por['total'];
            }

            //get total accepted opps
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

            $accepted_opp_query = "SELECT DISTINCT  count(*) as total
	FROM opportunities
	LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
	LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
	LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
	LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
	WHERE partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
	AND partner_account.id = '{$partner_account_id}'
	AND opportunities_cstm.accepted_by_partner_c ='Y'
	AND opportunities.deleted =0
	AND accounts.id = '{$id}'
	";
            //		mail("jbartek@sugarcrm.com","sql",$accepted_opp_query,"From: Jim <jbartek@sugarcrm.com>");
            $accepted_opp_response = $GLOBALS['db']->query($accepted_opp_query);

            while ($aor = $GLOBALS['db']->fetchByAssoc($accepted_opp_response)) {
                $info[$id]['accepted_opps'] = $aor['total'];
            }
        }
        //get subscriptions


		// jostrow -- See comment in partner_portal_get_subscriptions() -- recently changed this query around a bit
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

		$subscription_query = "SELECT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, accounts.id AS account_id,
		accounts.name, datediff( subscriptions.expiration_date, CURDATE()) AS date_diff, distgroups.name AS product,
		subscriptions_distgroups.quantity, distgroups.id AS distgroup_id

		FROM subscriptions

		JOIN accounts ON subscriptions.account_id = accounts.id AND accounts.deleted=0

		JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id
			AND subscriptions_distgroups.deleted=0 AND subscriptions_distgroups.quantity > 0

		JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted=0

		JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c

		WHERE subscriptions.deleted=0 AND accounts.id = '{$id}'

		ORDER BY accounts.name";

        $subscription_response = $GLOBALS['db']->query($subscription_query);

        if (isValidGUID($account_id)) {
            $info[$account_id]['subscriptions'] = array();
            while ($sr = $GLOBALS['db']->fetchByAssoc($subscription_response)) {
                $info[$account_id]['subscriptions'][$sr['id']] = $sr;
            }
        }
        else {
            $subs = array();
            while ($sr = $GLOBALS['db']->fetchByAssoc($subscription_response)) {
                $subs[] = $sr;
            }
            //figure out the closest expiring subscription [or NULL]
            $return = get_sorted_subscriptions($subs);
            //mail("jbartek@sugarcrm.com", "info", print_r($return,true), "From: Jim <jbartek@sugarcrm.com>");
            $info[$id]['subscriptions'] = $return;
        }


    }
    $info = serialize($info);
    //    mail("jbartek@sugarcrm.com","info",$info,"From: Jim <jbartek@sugarcrm.com>");
    return $info;
}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 61
 * get all accounts tied to the partner
 */

$server->register(
    'partner_portal_get_accounts',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
//        array('return'=>'tns:get_opportunities_list_result'),
    $NAMESPACE);

function partner_portal_get_accounts($portal_name, $session)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $accountquery = "SELECT DISTINCT accounts.*
	
FROM  contacts
LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
LEFT JOIN accounts_contacts ON contacts.id=accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts_opportunities jtl0 ON accounts_contacts.account_id=jtl0.account_id  AND jtl0.deleted=0
LEFT JOIN opportunities ON opportunities.id = jtl0.opportunity_id AND opportunities.deleted=0
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts as partner_account_contacts ON partner_account_contacts.account_id=partner_account.id AND partner_account_contacts.deleted=0
LEFT JOIN contacts as partner_contact ON partner_contact.id = partner_account_contacts.contact_id AND partner_contact.deleted =0
LEFT JOIN contacts_cstm as partner_contact_cstm ON partner_contact.id = partner_contact_cstm.id_c
LEFT JOIN email_addr_bean_rel ON email_addr_bean_rel.bean_id = contacts.id AND email_addr_bean_rel.deleted=0
LEFT JOIN email_addresses ON email_addr_bean_rel.email_address_id=email_addresses.id AND email_addresses.deleted = 0


WHERE partner_contact.portal_name = '{$portal_name}' 
AND partner_contact.portal_active = 1 
AND partner_contact.deleted = 0
AND (partner_contact.id = opportunities_cstm.contact_id_c OR partner_contact_cstm.oppq_active_c = 1)
AND contacts.deleted = 0
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND contacts.id = accounts_contacts.contact_id
AND opportunities_cstm.accepted_by_partner_c IN ('Y','R','P')
AND opportunities.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'Finance Closed')

ORDER BY accounts.name
";
    $response = $GLOBALS['db']->query($accountquery);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $filter = serialize(array('account_id' => $row['id']));
        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Contacts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 58
 * SOAP call to update a lot of contacts
 */

$server->register(
    'partner_portal_mass_update_contacts',
    array('name_value_list' => 'tns:name_value_list', 'session' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function partner_portal_mass_update_contacts($name_value_list, $session)
{
    require_once('modules/Contacts/Contact.php');
    global $beanFiles;

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
        return 'partner_portal_update_opportunities error: invalid session';
    }

    $id = 0;


    foreach ($name_value_list as $item => $value) {

        $contact_array = unserialize($value['value']);


        unset($id);
        $id = $contact_array['id'];
        unset($contact_array['id']);

        $qry = "UPDATE contacts, contacts_cstm SET ";

        $set = array();
        foreach ($contact_array AS $var => $val) {
            $set[] = "{$var}='" . $GLOBALS['db']->quote($val) . "'";
        }

        $where = " WHERE id='{$id}' AND id=id_c";

        $qry = $qry . implode(',', $set) . $where;


        if (!empty($id)) {
            $GLOBALS['db']->query($qry);
        }
    }
    return 1;
}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 7
 * SOAP call to create a contact during the Purchase Now of the Partner Portal Opportunities process
 */

$server->register(
    'partner_portal_create_contact',
    array('name_value_list' => 'tns:name_value_list', 'session' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function partner_portal_create_contact($name_value_list, $session)
{
    require_once('modules/Contacts/Contact.php');
    require_once('modules/Opportunities/Opportunity.php');
    require_once('modules/Accounts/Account.php');
    require_once('modules/EmailAddresses/EmailAddress.php');
    global $beanFiles;
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
        return array('id' => -1, 'error' => $error->get_soap_array());
    }

    $contact_array = array();

    foreach ($name_value_list as $value) {
        $contact_array[$value['name']] = $value['value'];
    }

    $opp_id = $contact_array['opportunity_id'];
    $acc_id = $contact_array['account_id'];


    unset($contact_array['opportunity_id'], $contact_array['account_id']);

    $acc = new Account;
    $acc->retrieve($acc_id);

    $bean = new Contact;

    foreach ($contact_array AS $var => $value) {
        $bean->$var = $value;
    }

	$bean->team_id = 1;
	$bean->team_set_id = 1;
	$bean->assigned_user_id = $acc->assigned_user_id;

    $id = $bean->save(TRUE);
    if (!empty($id)) {
        $acc->load_relationship('contacts');
        $acc->contacts->add($bean->id);

        if ($opp_id != 0) {
            $opp = new Opportunity;
            $opp->retrieve($opp_id);
            $opp->load_relationship('contacts');
            $opp->contacts->add($bean->id);
        }


        return $id;
    }

    return false;

}


/*** END BARTEK CUSTOMIZATION **/

$server->register(
    'partner_portal_get_contacts_by_account',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'account_id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function partner_portal_get_contacts_by_account($portal_name, $session,$account_id) {
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (partner_portal_check($portal_name) == -2) {
        $error->set_error('invalid_portal_user');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }


$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $partner_account_id = $row['id'];
        // if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
                return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
        }
}

	$accountquery = "
SELECT DISTINCT contacts.*
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0 
LEFT JOIN accounts_contacts ON accounts_contacts.account_id = accounts.id AND accounts_contacts.deleted = 0
LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id AND contacts.deleted = 0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0 
WHERE partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent') 
AND partner_account.id = '{$partner_account_id}' 
AND opportunities.deleted =0 
AND accounts.id = '{$account_id}' 
ORDER BY accounts.name ASC

";

        $response = $GLOBALS['db']->query($accountquery);
        $fields = $GLOBALS['db']->getFieldsArray($response);

        $field_list = array();
        foreach ($fields as $value) {
                $field_list[] = array("name"=>$value);
        }

        $output_list = array();
        while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

                $name_value_list = array();
                foreach ($row as $field => $value) {
                        $name_value_list[$field] = array("name"=>$field,"value"=>$value);
                }

                $output_list[] = array(
                                'id'=>$row['id'],
                                'module_name'=> "Contacts",
                                'name_value_list'=> $name_value_list,

                        );
        }


        return array('field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());



}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 32
 * SOAP call to get subscriptions by portal_name
 */


$server->register(
    'partner_portal_get_subscriptions',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'filter' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function partner_portal_get_subscriptions($portal_name, $session, $filter, $soap = true)
{
    $error = new SoapError();

    $filter = unserialize($filter);

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (partner_portal_check($portal_name) == -2) {
        $error->set_error('invalid_portal_user');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $filterSQL = '';
    if (isset($filter['account_id'])) {
        $filterSQL .= "AND accounts.id = '{$filter['account_id']}'";
    }

$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $partner_account_id = $row['id'];
        // if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
	        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
	}
}

// jostrow
// grab a list of Accounts that this partner has access to (taken from partner_portal_get_accounts_list)
$accounts_query = "
SELECT accounts.id
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
WHERE partner_account.id = '{$partner_account_id}'
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND opportunities.sales_stage IN ('Closed Won', 'Sales Ops Closed', 'Finance Closed')
AND opportunities.deleted =0
ORDER BY accounts.name ASC
";

$accounts_res = $GLOBALS['db']->query($accounts_query);
$visible_accounts = array();
while ($accounts_row = $GLOBALS['db']->fetchByAssoc($accounts_res)) {
	$visible_accounts[] = "'" . $accounts_row['id'] . "'";
}

$accounts_filter_string = implode(',', $visible_accounts);

// This query does not verify that the Partner Contact actually has access to view the Account's Subscriptions
// However, the Partner Contact will see not the Account through the Partner Portal unless we've already verified that they're supposed to see it...
// So it's safe!
$subscription_query = "SELECT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, accounts.id AS account_id,
accounts.name, datediff( subscriptions.expiration_date, CURDATE()) AS date_diff, distgroups.name AS product,
subscriptions_distgroups.quantity, distgroups.id AS distgroup_id, subscriptions.status, subscriptions_cstm.term_end_date_c

FROM subscriptions

JOIN accounts ON subscriptions.account_id = accounts.id AND accounts.deleted=0

JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id
	AND subscriptions_distgroups.deleted=0 AND subscriptions_distgroups.quantity > 0

JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted=0

RIGHT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
LEFT JOIN subscriptions_cstm ON subscriptions.id = subscriptions_cstm.id_c

WHERE subscriptions.deleted=0
	AND subscriptions.account_id IN ({$accounts_filter_string})
	{$filterSQL}

ORDER BY accounts.name";

    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();

    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        if ($soap===true) {
            $name_value_list = array();
            foreach ($row as $field => $value) {
                $name_value_list[$field] = array("name" => $field, "value" => $value);
            }

            $output_list[] = array(
                'id' => $row['id'],
                'module_name' => "Subscriptions",
                'name_value_list' => $name_value_list,

            );
        }
    }
    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());

}

/*** END Bartek Customization ***/


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 31
 * SOAP call to get orders by portal_name
 */


$server->register(
    'partner_portal_get_orders',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function partner_portal_get_orders($portal_name, $session)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (partner_portal_check($portal_name) == -2) {
        $error->set_error('invalid_portal_user');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $partner_account_id = $row['id'];
        // if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
                return serialize(array());
        }
}





// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name

$subscription_query = "
SELECT DISTINCT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
FROM orders
LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c
LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
WHERE orders.deleted = 0
AND orders_cstm.account_id_c = '{$partner_account_id}'
ORDER BY orders.order_id DESC
";


    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Subscriptions",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 31
 * SOAP call to get an order by portal_name
 */


$server->register(
    'partner_portal_get_order',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'order_id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function partner_portal_get_order($portal_name, $session, $order_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (partner_portal_check($portal_name) == -2) {
        $error->set_error('invalid_portal_user');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($order_id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }

// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name
$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $partner_account_id = $row['id'];
        // if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
                return serialize(array());
        }
}






$subscription_query = "
SELECT DISTINCT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
FROM orders
LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c
LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
WHERE orders.deleted = 0
AND orders_cstm.account_id_c = '{$partner_account_id}'
AND orders.id = '{$order_id}'
";



    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Orders",
            'name_value_list' => $name_value_list,

        );
    }

	//mail("jbartek@sugarcrm.com","output_list orders",print_r($output_list,true),"From: Jim <jbartek@sugarcrm.com>");
    
    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 31
 * SOAP call to get products by  by portal_name
 */


$server->register(
    'partner_portal_get_products_by_order',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'order_id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function partner_portal_get_products_by_order($portal_name, $session, $order_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (partner_portal_check($portal_name) == -2) {
        $error->set_error('invalid_portal_user');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($order_id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }


    $subscription_query = "
SELECT products.*, products_cstm.*

FROM products
LEFT JOIN orders_products_c ON products.id = orders_products_c.orders_pro2902roducts_idb AND orders_products_c.deleted = 0
LEFT JOIN orders ON orders.id = orders_products_c.orders_prob569sorders_ida AND orders.deleted = 0
LEFT JOIN accounts_orders_c ON orders.id = accounts_orders_c.accounts_o0f8dsorders_idb AND accounts_orders_c.deleted = 0
LEFT JOIN accounts ON accounts_orders_c.accounts_od749ccounts_ida = accounts.id AND accounts.deleted=0
JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
JOIN accounts AS partner_account ON partner_account.id = accounts_cstm.account_id_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts AS partner_account_contacts ON partner_account_contacts.account_id = partner_account.id AND partner_account_contacts.deleted=0
LEFT JOIN contacts AS partner_contact ON partner_contact.id = partner_account_contacts.contact_id AND partner_contact.deleted=0
LEFT JOIN products_cstm ON products.id = products_cstm.id_c

WHERE partner_contact.portal_name = '{$portal_name}' AND
products.deleted=0 AND
orders.id = '{$order_id}'
ORDER BY products.name
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Subscriptions",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/*** END Bartek Customization ***/


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 59
 * SOAP call to update a contact
 */

$server->register(
    'portal_update_contact',
    array('name_value_list' => 'tns:name_value_list', 'session' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_update_contact($name_value_list, $session)
{
    require_once('modules/Contacts/Contact.php');
    global $beanFiles;
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
        return array('id' => -1, 'error' => $error->get_soap_array());
    }

    $set = array();
    $id = 0;
    foreach ($name_value_list as $item) {
        foreach ($item AS $value) {
            if ($value['name'] != 'id') {
                $set[] = $value['name'] . "='" . $GLOBALS['db']->quote($value['value']) . "'";
            }
            else {
                $id = $value['value'];
            }
        }
    }
    if ($id===0) {
        return 0;
    }

    $qry = "UPDATE contacts, contacts_cstm SET ";
    $where = " WHERE id='{$id}' AND id=id_c";

    $qry = $qry . implode(',', $set) . $where;

    if (!empty($id)) {
        $GLOBALS['db']->query($qry);
    }

    return 1;
}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 58
 * SOAP call to update a lot of contacts
 */

$server->register(
    'portal_mass_update_contacts',
    array('name_value_list' => 'tns:name_value_list', 'session' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_mass_update_contacts($name_value_list, $session)
{
    require_once('modules/Contacts/Contact.php');
    global $beanFiles;

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
        return 'partner_portal_update_opportunities error: invalid session';
    }

    $id = 0;
//mail("jbartek@sugarcrm.com","name_value_list",print_r($name_value_list,true),"From: Jim <jbartek@sugarcrm.com>");
    foreach ($name_value_list as $item => $value) {
            $contact_array = unserialize($value['value']);
            $id = $contact_array['id'];
            unset($contact_array['id']);

            $qry = "UPDATE contacts, contacts_cstm SET ";

            $set = array();
            foreach ($contact_array AS $var => $val) {
                $set[] = "{$var}='" . $GLOBALS['db']->quote($val) . "'";
            }

            $where = " WHERE id='{$id}' AND id=id_c";

            $qry = $qry . implode(',', $set) . $where;

	    
            if (!empty($id)) {
                $GLOBALS['db']->query($qry);
            }

    }
    return 1;
}


$server->register(
    'portal_update_account',
    array('name_value_list' => 'tns:name_value_list', 'session' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_update_account($name_value_list, $session)
{
    global $beanFiles;
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
        return array('id' => -1, 'error' => $error->get_soap_array());
    }

    $account_array = array();
    $id = 0;
    foreach ($name_value_list as $item) {
        foreach ($item AS $value) {
            if ($value['name'] != 'id') {
                $account_array[$value['name']] = $value['value'];
            }
            else {
                $id = $value['value'];
            }
        }
    }
    if ($id===0) {
        return 0;
    }


    $bean = new Account;

    $bean->retrieve($id);

    foreach ($account_array AS $var => $value) {
        $bean->$var = $value;
    }


    $bean->save();

    return 1;

}


/*** END BARTEK CUSTOMIZATION **/


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 56
 * SOAP call to get subscriptions by portal_name
 */


$server->register(
    'portal_get_subscriptions',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string','enabled' => 'xsd:int','active'=>'xsd:int'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_subscriptions($portal_name, $session,$enabled=0,$active=0)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }


    $filter_sql = '';

    if($enabled === 1) {
	$filter_sql .= " AND subscriptions.status = 'enabled'";
    }
	
    if($active == 1) {
	$filter_sql .= " AND datediff(subscriptions.expiration_date,CURDATE()) > 0";
    }

    $subscription_query = "
SELECT DISTINCT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, accounts.id AS account_id, accounts.name, datediff( subscriptions.expiration_date, CURDATE()) AS date_diff, distgroups.name AS product, subscriptions_distgroups.quantity, distgroups.id AS distgroup_id, subscriptions.status, subscriptions_cstm.term_end_date_c

FROM subscriptions
JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id AND subscriptions_distgroups.deleted = 0
JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted = 0
LEFT JOIN subscriptions_cstm ON subscriptions.id = subscriptions_cstm.id_c
LEFT JOIN accounts ON subscriptions.account_id = accounts.id AND accounts.deleted=0
LEFT JOIN accounts_contacts ON accounts_contacts.account_id = accounts.id AND accounts_contacts.deleted=0
LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id AND contacts.deleted=0

WHERE contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}' AND
subscriptions.deleted=0 
$filter_sql
ORDER BY subscriptions.expiration_date DESC
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Subscriptions",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/*** END Bartek Customization ***/

/**
 * @author jostrow
 * @project moofcart
 * @tasknum 20
 * SOAP call to retrieve a given Subscription without considering ownership of Accounts/Subs, etc. based on portal_name
 */


$server->register(
    'get_subscription',
    array('session' => 'xsd:string', 'sub_id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function get_subscription($session, $sub_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $subscription_query = "SELECT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, datediff( subscriptions.expiration_date, CURDATE()) AS date_diff,
distgroups.name AS product, subscriptions_distgroups.quantity, distgroups.id AS distgroup_id, subscriptions.account_id, subscriptions.status, subscriptions_cstm.term_end_date_c

FROM subscriptions
JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id AND subscriptions_distgroups.deleted = 0
JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted = 0
LEFT JOIN subscriptions_cstm ON subscriptions.id = subscriptions_cstm.id_c
WHERE subscriptions.deleted=0 AND subscriptions.id = '" . $GLOBALS['db']->quote($sub_id) . "'";

    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Subscriptions",
            'name_value_list' => $name_value_list,

        );
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}

$server->register(
    'get_subscription_by_name',
    array('session' => 'xsd:string', 'sub_name' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function get_subscription_by_name($session, $sub_name)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $subscription_query = "
SELECT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, datediff( subscriptions.expiration_date, CURDATE()) AS date_diff, distgroups.name AS product, subscriptions_distgroups.quantity, distgroups.id AS distgroup_id, subscriptions.status, subscriptions_cstm.term_end_date_c

FROM subscriptions
JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id AND subscriptions_distgroups.deleted = 0
JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted = 0
LEFT JOIN subscriptions_cstm ON subscriptions.id = subscriptions_cstm.id_c
WHERE subscriptions.deleted=0 AND subscriptions.subscription_id = '" . $GLOBALS['db']->quote($sub_name) . "'";

    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Subscriptions",
            'name_value_list' => $name_value_list,

        );
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 56
 * SOAP call to get contact record by portal_name
 */


$server->register(
    'portal_get_current_contact',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_current_contact($portal_name, $session)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $subscription_query = "
        SELECT contacts.*, contacts_cstm.*, accounts.id AS account_id, accounts.name AS account_name
        FROM contacts
        	LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
        	LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted = 0
        	LEFT JOIN accounts ON accounts_contacts.account_id = accounts.id AND accounts.deleted = 0
        	LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
        WHERE contacts.deleted = 0 AND 
        	contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}'
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($subscription_query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Contacts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/*** END Bartek Customization ***/

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 58
 * SOAP call to get all contacts related to portal_name
 */


$server->register(
    'portal_get_contacts',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_contacts($session, $portal_name)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $account_id_qry = "
        SELECT accounts_contacts.account_id
        FROM accounts_contacts
        	JOIN contacts ON accounts_contacts.contact_id = contacts.id AND contacts.deleted=0 AND accounts_contacts.deleted=0
        WHERE contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}'
        LIMIT 1";

    $qry = "
        SELECT contacts.*, contacts_cstm.*
        FROM contacts
        	LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
        	LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted = 0
        	LEFT JOIN accounts ON accounts_contacts.account_id = accounts.id AND accounts.deleted = 0
        	LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
        WHERE contacts.deleted = 0 AND 
        	accounts.id = ({$account_id_qry})
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Contacts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 56
 * SOAP call to get current account realted to portal_name
 */


$server->register(
    'portal_get_current_account',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_current_account($session, $portal_name)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $account_id_qry = "
        SELECT accounts_contacts.account_id
        FROM accounts_contacts
        	JOIN contacts ON accounts_contacts.contact_id = contacts.id AND contacts.deleted=0 AND accounts_contacts.deleted=0
        WHERE contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}'
        ";

    $qry = "
        SELECT accounts.*, accounts_cstm.*
        FROM contacts
        	LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
        	LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted = 0
        	LEFT JOIN accounts ON accounts_contacts.account_id = accounts.id AND accounts.deleted = 0
        	LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
        WHERE contacts.deleted = 0 AND 
        	accounts.id = ({$account_id_qry})
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Contacts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


/*** END Bartek Customization ***/

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * SOAP call to get orders
 */


$server->register(
    'portal_get_orders',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_orders($portal_name, $session)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $qry = "
        SELECT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
        FROM orders
		LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c
		LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
        	LEFT JOIN contacts_orders_c ON orders.id = contacts_orders_c.contacts_o95f4sorders_idb AND orders.deleted = 0
        	LEFT JOIN contacts ON contacts.id = contacts_orders_c.contacts_o7603ontacts_ida AND contacts.deleted = 0
        WHERE contacts.portal_active = 1 AND contacts.portal_name = '" . $GLOBALS['db']->quote($portal_name) . "'
	AND orders_cstm.contact_id_c = ''
        ORDER BY orders.order_id DESC
";


	

    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Orders",
			'order_number' => $row['name'],
            'name_value_list' => $name_value_list,
        );
    }

	// grab Orders where this Portal Name is the "Partner Contact"
	$query2 = "SELECT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
		FROM orders
		RIGHT JOIN orders_cstm ON orders.id = orders_cstm.id_c
		LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
		LEFT JOIN contacts ON contacts.id = orders_cstm.contact_id_c AND contacts.deleted = 0
		WHERE contacts.portal_active = 1 AND contacts.portal_name = '" . $GLOBALS['db']->quote($portal_name) . "'
		AND orders.deleted = 0
		ORDER BY orders.name DESC";

	$response2 = $GLOBALS['db']->query($query2);

	while ($row2 = $GLOBALS['db']->fetchByAssoc($response2)) {
        $name_value_list = array();
        foreach ($row2 as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row2['id'],
            'module_name' => "Orders",
			'order_number' => $row2['name'],
            'name_value_list' => $name_value_list,

        );
	}

	// since we're doing two queries, we need to use a custom sort function...
	require_once('custom/si_custom_files/MoofCartHelper.php');

	usort($output_list, array('MoofCartHelper', 'sortByOrderNumberDesc'));

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * SOAP call to get orders
 */


$server->register(
    'portal_get_subscription',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_subscription($portal_name, $session, $id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }


    $qry = "
        SELECT subscriptions.id, subscriptions.subscription_id, subscriptions.expiration_date, subscriptions_distgroups.quantity, distgroups.name AS product, distgroups.id AS distgroup_id
        FROM subscriptions
        	LEFT JOIN subscriptions_distgroups ON subscriptions.id = subscriptions_distgroups.subscription_id AND subscriptions_distgroups.deleted = 0
        	LEFT JOIN distgroups ON subscriptions_distgroups.distgroup_id = distgroups.id AND distgroups.deleted = 0
        	LEFT JOIN accounts ON subscriptions.account_id = accounts.id AND accounts.deleted = 0
        	LEFT JOIN accounts_contacts ON accounts.id = accounts_contacts.account_id AND accounts_contacts.deleted = 0
        	LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id AND contacts.deleted=0
        WHERE contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}' AND
        	subscriptions.id = '{$id}'
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Orders",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * SOAP call to get specific order
 */


$server->register(
    'portal_get_order',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_order($portal_name, $session, $id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }


    $qry = "
        SELECT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
        FROM orders
		LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c
        	LEFT JOIN contacts_orders_c ON orders.id = contacts_orders_c.contacts_o95f4sorders_idb
        	LEFT JOIN contacts ON contacts.id = contacts_orders_c.contacts_o7603ontacts_ida AND contacts.deleted = 0
		LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
        WHERE contacts.portal_active = 1 AND contacts.portal_name = '" . $GLOBALS['db']->quote($portal_name) . "'
        	AND orders.deleted = 0 
        	AND orders.id = '" . $GLOBALS['db']->quote($id) . "'
		AND orders_cstm.contact_id_c = ''
";


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Orders",
            'name_value_list' => $name_value_list,

        );
    }

	// grab Orders where this Portal Name is the "Partner Contact"
	$query2 = "SELECT orders.*, orders_cstm.*, orders_subscriptions_c.orders_subb9eaiptions_idb AS subscription_id
		FROM orders
		RIGHT JOIN orders_cstm ON orders.id = orders_cstm.id_c
		LEFT JOIN contacts ON contacts.id = orders_cstm.contact_id_c AND contacts.deleted = 0
		LEFT JOIN orders_subscriptions_c ON (orders.id = orders_subscriptions_c.orders_subef4esorders_ida AND orders_subscriptions_c.deleted = 0)
		WHERE contacts.portal_active = 1 AND contacts.portal_name = '" . $GLOBALS['db']->quote($portal_name) . "'
		AND orders.deleted = 0
		AND orders.id = '" . $GLOBALS['db']->quote($id) . "'";

	$response2 = $GLOBALS['db']->query($query2);

	while ($row2 = $GLOBALS['db']->fetchByAssoc($response2)) {
        $name_value_list = array();
        foreach ($row2 as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row2['id'],
            'module_name' => "Orders",
            'name_value_list' => $name_value_list,

        );
	}

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


$server->register(
    'portal_get_products_by_order',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_products_by_order($portal_name, $session, $id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }


    $qry = "
        SELECT products.*, cost_price*quantity AS total, products_cstm.*
        FROM products
        	LEFT JOIN orders_products_c ON products.id = orders_products_c.orders_pro2902roducts_idb AND orders_products_c.deleted = 0
			LEFT JOIN products_cstm ON products.id = products_cstm.id_c
        WHERE 
        	 orders_products_c.orders_prob569sorders_ida = '{$id}'
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Orders",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


/*** END Bartek Customization ***/


/*
 *	Moofcart soap function for handling PO's for orders
 *
 */

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * SOAP Method for uploading a document and attaching it to an order as a Note
 */


$server->register(
    'portal_moofcart_upload_documents',
    array('session' => 'xsd:string',
        'order_id' => 'xsd:string',
        'document_type' => 'xsd:string',
        'document_name' => 'xsd:string',
        'document' => 'xsd:string',
    ),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_moofcart_upload_documents($session, $order_id, $document_type, $document_name, $document)
{
    require_once('modules/Documents/DocumentSoap.php');
    require_once('modules/DocumentRevisions/DocumentRevision.php');



	$attach_to_order = TRUE;
	if ($order_id == '') {
		$attach_to_order = FALSE;
	}
	elseif (is_numeric($order_id)) {
		// MoofCart passed an order number, rather than a GUID ... let's look up the GUID
		$real_order_id = FALSE;

		$order_id_sql = "SELECT id FROM orders WHERE order_id = '" . $GLOBALS['db']->quote($order_id) . "' LIMIT 1";
		$order_id_res = $GLOBALS['db']->query($order_id_sql);
		while ($order_id_row = $GLOBALS['db']->fetchByAssoc($order_id_res)) {
			$real_order_id = $order_id_row['id'];
		}

		if ($real_order_id === FALSE) {
	        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
		}

		$order_id = $real_order_id;
	}
    elseif (!isValidGUID($order_id)) {
    }

	if ($attach_to_order) {
	    $oid = false;	    
	    
	    $qry = $GLOBALS['db']->query("SELECT order_id FROM orders WHERE id = '{$order_id}'");

	    while ($row = $GLOBALS['db']->fetchByAssoc($qry)) {
	        $oid = $row['order_id'];
	    }

	    if ($oid == false) {
	        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
	    }
	}
	else {
		$oid = "TBD"; // jostrow TODO -- hopefully we can replace this with the order number later on
	}

    $d = new Document();
    if ($document_type == 'PO') {
        $d->document_name = "{$document_type} for Order #{$oid} ({$document_name})";
        $d->category_id = 'po';
    }
    else {
        $d->document_name = $document_type;
        $d->category_id = 'agreements';
    }
    $d->status_id = 'Under Review';
    $d->active_date = date('Y-m-d');
    $d->team_set_id = 1;
    $d->team_id = 1;

    //get new document id
    $d_id = $d->save(FALSE);
    $d->retrieve($d_id);

    $dr = new DocumentSoap();
    $document_revision = array('file' => $document, 'filename' => $document_name, 'id' => $d->id, 'revision' => '1.0');
    $id = $dr->saveFile($document_revision);

    $d->document_revision_id = $id;

    $d->save(FALSE);

	if ($attach_to_order) {
	    $GLOBALS['db']->query("INSERT INTO orders_documents_c SET id='" . create_guid() . "', date_modified = NOW(), deleted=0, orders_docd099sorders_ida = '{$order_id}', orders_doc3babcuments_idb='{$d->id}', document_revision_id = '{$id}'");

	    $GLOBALS['db']->query("UPDATE orders SET status = 'pending_salesops' WHERE id = '{$order_id}'");
	}


    /*
        $seed->name = "{$document_type} {$document_name}";
        // the description is just the document_type
        $seed->description = "{$document_type} {$document_name}";
        // set the parent type
        $seed->parent_type = 'Orders';
        // set the parent id to the order id
        $seed->parent_id = $order_id;

        if (!key_exists('team_id', $values_set) && isset($_SESSION['team_id'])) {
            $seed->team_id = $_SESSION['team_id'];
        }

        if (!key_exists('team_set_id', $values_set) && isset($_SESSION['team_set_id'])) {
            $seed->team_set_id = $_SESSION['team_set_id'];
        }

        if (isset($_SESSION['assigned_user_id']) && (!key_exists('assigned_user_id', $values_set) || empty($values_set['assigned_user_id']))) {
            $seed->assigned_user_id = $_SESSION['assigned_user_id'];
        }
        if (isset($_SESSION['account_id']) && (!key_exists('account_id', $values_set) || empty($values_set['account_id']))) {
            // BEGIN Internal Sugar customization -- jostrow
            // 'Account Name' should not be updated for Leads
            if ($module_name != 'Leads') {
                require_once("modules/Accounts/Account.php");
                $seed_account = new Account();
                $seed_account->disable_row_level_security = TRUE;
                $seed_account->retrieve($_SESSION['account_id']);

                $seed->account_id = $_SESSION['account_id'];
                $seed->account_name = $seed_account->name;

                unset($seed_account);
            }
            // END Internal Sugar customization
        }

        $seed->portal_flag = 1;
        $seed->portal_viewable = true;

        $seed->disable_row_level_security = true;

        $id = $seed->save();

        // attach the file
        require_once('modules/Notes/NoteSoap.php');
        $ns = new NoteSoap();
        $note['id'] = $id;
        $note['filename'] = $document_name;
        $note['file'] = $document;
        $id = $ns->saveFile($note, true);

        // do some order awesomeness and trigger some sweet hooks
        //$order->status = 'some status';
        //$order->save(true);
    */

    return $d->id;
}



$server->register(
    'portal_moofcart_upload_contracts',
    array('session' => 'xsd:string',
        'order_id' => 'xsd:string',
        'document_name' => 'xsd:string',
        'document' => 'xsd:string',
        'document_type'	=>	'xsd:string',
		'signer_full_name' => 'xsd:string',
		'start_date' => 'xsd:string',
		'end_date' => 'xsd:string',
    ),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_moofcart_upload_contracts($session, $order_id, $document_name, $document, $document_type, $signer_full_name, $start_date, $end_date)
{
    require_once('modules/Documents/DocumentSoap.php');
    require_once('modules/DocumentRevisions/DocumentRevision.php');

	$attach_to_order = TRUE;
	if ($order_id == '') {
		$attach_to_order = FALSE;
	}
	elseif (is_numeric($order_id)) {
		// MoofCart passed an order number, rather than a GUID ... let's look up the GUID
		$real_order_id = FALSE;

		$order_id_sql = "SELECT id FROM orders WHERE order_id = '" . $GLOBALS['db']->quote($order_id) . "' LIMIT 1";
		$order_id_res = $GLOBALS['db']->query($order_id_sql);
		while ($order_id_row = $GLOBALS['db']->fetchByAssoc($order_id_res)) {
			$real_order_id = $order_id_row['id'];
		}

		if ($real_order_id === FALSE) {
	        return -2;
		}

		$order_id = $real_order_id;
	}
    elseif (!isValidGUID($order_id)) {
        return -1;
    }


	global $current_user;
	$current_user = new User();
	$current_user->getSystemUser();

	$products = array();
	$term = 1;
	if ($attach_to_order) {
	
		$o = new Orders();
		$o->retrieve($order_id);
		
		$order = $o->fetched_row;
		
		$p = $o->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);
		$products = array();
		foreach($p AS $product) {
			$products[] = $product->fetched_row;
		}

		
/*
		$query = "
			SELECT orders.*, orders_cstm.*, accounts_orders_c.accounts_od749ccounts_ida AS account_id, orders_opportunities_c.orders_opp02e0unities_idb AS opportunity_id
			FROM orders
			LEFT JOIN orders_cstm ON orders.id = orders_cstm.id_c
			LEFT JOIN accounts_orders_c ON orders.id = accounts_orders_c.accounts_o0f8dsorders_idb AND accounts_orders_c.deleted=0
			LEFT JOIN orders_opportunities_c ON orders.id = orders_opportunities_c.orders_opp69easorders_ida AND orders_opportunities_c.deleted = 0
			WHERE orders.id = '{$order_id}'
			AND orders.deleted = 0
		";

		$qry = $GLOBALS['db']->query($query);


		$order = array();
	    while ($row = $GLOBALS['db']->fetchByAssoc($qry)) {
	    	foreach($row AS $k => $v) {
				$order[$k] = $v;
			}
	    }

	    if (empty($order)) {
	        return -1;
	    }

	    $query = "
		SELECT products.*, products_cstm.*
		FROM products
		LEFT JOIN products_cstm ON products.id = products_cstm.id_c
		LEFT JOIN orders_products_c ON products.id = orders_products_c.orders_pro2902roducts_idb AND orders_products_c.deleted = 0
		WHERE products.deleted = 0
			AND orders_products_c.orders_prob569sorders_ida = '{$order['id']}'
		";
	
     	    $qry = $GLOBALS['db']->query($query);
	    $counter=0;
	    while($row = $GLOBALS['db']->fetchByAssoc($qry)) {
	    	foreach($row AS $k => $v) {
			if($k == 'term_c' && $term < $v) {
				$term = $v;
			}
			$products[$counter][$k] = $v;
		}
      }
*/
	}
	else {
		$order['order_id'] = "TBD"; // jostrow TODO: hopefully we can replace this with the order number later on
		$order['account_id'] = '';
		$order['opportunity_id'] = '';
		$order['assigned_user_id'] = 1;
	}
	
	$fields = array();
	
	$fields['name'] = "Contract for Order #{$order['order_id']} ({$document_name})"; 
	$fields['start_date'] = $start_date;
	$fields['end_date'] = $end_date;
	$fields['customer_signed_date'] = date('Y-m-d');
	$fields['status'] = 'with_sales_opps';
	$fields['account_id'] = $order['account_id'];
	$fields['assigned_user_id'] = $order['assigned_user_id'];	
	$fields['team_id'] = 1;
	$fields['team_set_id'] = 1;
	$fields['date_modified'] = date('Y-m-d H:i:s');
	$fields['date_entered'] = date('Y-m-d H:i:s');
	$fields['created_by'] = 1;
	$fields['modified_user_id'] = 1;
	$fields['agreement_type_c'] = 'msa';
	$fields['execution_status_c'] = 'Fully Executed';
	$fields['moofcart_agreement_type_c'] = $document_type;

	if (!empty($signer_full_name)) {
		$fields['description'] = "Signer Full Name: {$signer_full_name}";
	}

	$c = new Contract;
	foreach($fields AS $k => $v) {
		$c->$k = $v;
	}
	
	$c->save();
	
	$fields['id'] = $c->id;

/*
	$fields_cstm = array();
	$fields_cstm['id_c'] = $fields['id'];
	$fields_cstm['agreement_type_c'] = 'msa';
	$fields_cstm['execution_status_c'] = 'Fully Executed';

	$set = array();
	
	foreach($fields AS $k => $v) {
		$set[] = "{$k}='{$v}'";
	}

	$GLOBALS['db']->query("INSERT INTO contracts SET " . implode(',', $set));

//	mail("jbartek@sugarcrm.com","Insert 1","INSERT INTO contracts SET " . implode(',', $set),"From: Jim <jbartek@sugarcrm.com>");
	
	$set = array();
	
	foreach($fields_cstm AS $k => $v) {
		$set[] = "{$k}='{$v}'";
	}
	
	$GLOBALS['db']->query("INSERT INTO contracts_cstm SET " . implode(',', $set));

	if(isset($products) && !empty($products)) {
		foreach($products AS $product) {
			$insert_product = "INSERT INTO products_contracts_c SET products_cf11broducts_ida = '{$product['id']}', products_c25e9ntracts_idb = '{$fields['id']}', deleted=0, date_modified = NOW(), id='".create_guid()."'";
//			mail("jbartek@sugarcrm.com","Insert Product",$insert_product,"From: Jim <jbartek@sugarcrm.com>");
			$GLOBALS['db']->query($insert_product);
		}
	}

	if(!empty($order['opportunity_id'])) {
		$GLOBALS['db']->query("INSERT INTO contracts_opportunities SET opportunity_id = '{$order['opportunity_id']}, contract_id='{$fields['id']}',deleted=0,date_modified=NOW(),id='".create_guid()."'");
	}
*/
//	mail("jbartek@sugarcrm.com","Insert 2","INSERT INTO contracts_cstm SET " . implode(',', $set),"From: Jim <jbartek@sugarcrm.com>");

		
    $d = new Document();
    $d->document_name = "Contract for Order #{$order['order_id']} ({$document_name})";
    $d->category_id = 'agreements';
    
    $d->status_id = 'Under Review';
    $d->active_date = date('Y-m-d');
    $d->team_set_id = 1;
    $d->team_id = 1;

    //get new document id
    $d_id = $d->save(FALSE);
    $d->retrieve($d_id);

    $dr = new DocumentSoap();
    $document_revision = array('file' => $document, 'filename' => $document_name, 'id' => $d->id, 'revision' => '1.0');
    $id = $dr->saveFile($document_revision);

    $d->document_revision_id = $id;

    $d->save(FALSE);
	$d->load_relationship('contracts');
	$d->contracts->add($fields['id']);    
    
	if ($attach_to_order) {
	
		// don't think we need this anymore
	    $GLOBALS['db']->query("INSERT INTO orders_documents_c SET id='" . create_guid() . "', date_modified = NOW(), deleted=0, orders_docd099sorders_ida = '{$order['id']}', orders_doc3babcuments_idb='{$d->id}', document_revision_id = '{$id}'");
		
	    $GLOBALS['db']->query("INSERT INTO orders_contracts_c SET id='" . create_guid() . "', date_modified = NOW(), deleted=0, orders_con055dsorders_ida = '{$order['id']}', orders_cone780ntracts_idb='{$fields['id']}'");
	    
	    $c->save();

    	// don't need this
	    //$GLOBALS['db']->query("UPDATE orders SET status = 'pending_salesops' WHERE id = '{$order_id}'");
	}

    return $fields['id'];
}


/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 57
 * SOAP Method for get documents attached to an order as a Note
 */


$server->register(
    'portal_moofcart_get_documents_for_orders',
    array(
    	'session' => 'xsd:string',
    	'portal_name' => 'xsd:string',
        'order_id' => 'xsd:string',
    ),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function portal_moofcart_get_documents_for_orders($session, $portal_name, $order_id)
{

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return -1;
    }


    if (!isValidGUID($order_id)) {
        return -2;
    }

        global $current_user;
        $current_user = new User();
        $current_user->getSystemUser();


    $response = $GLOBALS['db']->query("
    SELECT DISTINCT documents.*
    
    FROM documents
    LEFT JOIN orders_documents_c ON documents.id = orders_documents_c.orders_doc3babcuments_idb AND orders_documents_c.deleted = 0
    LEFT JOIN orders ON orders_documents_c.orders_docd099sorders_ida = orders.id AND orders.deleted = 0
    LEFT JOIN contacts_orders_c ON orders.id = contacts_orders_c.contacts_o95f4sorders_idb
    LEFT JOIN contacts ON contacts.id = contacts_orders_c.contacts_o7603ontacts_ida AND contacts.deleted = 0
    
    WHERE orders_documents_c.orders_docd099sorders_ida = '{$order_id}'
    AND documents.deleted = 0
    AND contacts.portal_active = 1 AND contacts.portal_name = '{$portal_name}'
    ORDER BY documents.date_entered DESC
	");
	
	$documents = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
    	foreach($row AS $k => $v) {
			$documents[$row['id']][$k] = $v;
		}
    }
   
    $o = new Orders();
    $o->retrieve($order_id);
    
    $contracts = $o->get_linked_beans('orders_contracts', 'Contract',array(),0,-1,0);
    foreach($contracts AS $contract) {
	$ds = $contract->get_linked_beans('contracts_documents', 'Document', array(), 0, -1, 0);
	foreach($ds AS $d) {
		$documents[$d->id] = $d->fetched_row;
	}
    }

 
    //mail("jbartek@sugarcrm.com","Documents",print_r($documents,true),"From: Jim <jbartek@sugarcrm.com>");

    
    return serialize($documents);
}


$server->register(
    'moofcart_create_order',
    array('session' => 'xsd:string', 'order_details' => 'tns:name_value_list'),
    array('return' => 'tns:set_entry_result'),
    $NAMESPACE);

function moofcart_create_order($session, $order_details)
{
    $error = new SoapError();
    if (!validate_authenticated($session)) {
        $error->set_error('invalid_login');
        return array('id' => -1, 'error' => $error->get_soap_array());
    }

    $order = new Orders();

    foreach ($order_details as $v) {
        $order->$v['name'] = $v['value'];
    }

    $order->save();

    return array('id' => $order->id, 'error' => $error->get_soap_array());
}


$server->register(
    'portal_update_contact_account',
    array('id' => 'xsd:string', 'account_id' => 'xsd:string', 'session' => 'xsd:string'),
    array('return' => 'tns:set_entry_result'),
    $NAMESPACE);

function portal_update_contact_account($id, $account_id, $session)
{
    $error = new SoapError();
    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }


    if (empty($id) || empty($account_id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }

    if (!isValidGUID($id) || !isValidGUID($account_id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }


    $query = $GLOBALS['db']->query("SELECT * FROM accounts_contacts WHERE contact_id = '{$id}'");

    //	$create_new = true;
    // set all the old ones to deleted
    while ($row = $GLOBALS['db']->fetchByAssoc($query)) {
        $GLOBALS['db']->query("UPDATE accounts_contacts SET deleted=1 WHERE account_id = '{$row['account_id']}' AND contact_id='{$id}'");
    }
    // insert the new one
    $GLOBALS['db']->query("INSERT INTO accounts_contacts SET id = '" . create_guid() . "', contact_id='{$id}', account_id='{$account_id}', date_modified=NOW(), deleted=0");

    return array('id' => $id, 'error' => $error->get_soap_array());
}


$server->register(
    'portal_get_new_contact_account',
    array('session' => 'xsd:string', 'id' => 'xsd:string'),
    array('return' => 'tns:get_entry_list_result'),
    $NAMESPACE);


function portal_get_new_contact_account($session, $id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (!isValidGUID($id)) {
        return array('id' => -1, 'error' => array('error' => 'No valid IDs'));
    }

    $qry = "
        SELECT accounts.*
        FROM accounts
        LEFT JOIN accounts_contacts ON accounts.id = accounts_contacts.account_id
        WHERE accounts_contacts.contact_id = '{$id}' AND accounts_contacts.deleted = 0 
";

    //mail("jbartek@sugarcrm.com","sql",$subscription_query,"From: Jim <jbartek@sugarcrm.com>");


    $response = $GLOBALS['db']->query($qry);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

        $name_value_list = array();
        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "Accounts",
            'name_value_list' => $name_value_list,

        );
    }


    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());


}


function isValidGUID($value)
{
    if (!preg_match("/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/i", $value)) {
        return FALSE;
    }
    return TRUE;
}


/**
 * @author Julian Ostrow
 * @project moofcart
 * @tasknum 20
 * SOAP method that retrieves a list of SugarInstallations related to a given Subscription
 */

$server->register(
    'portal_get_sugarinstallations_from_sub',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'sub_id' => 'xsd:string', 'id' => 'xsd:string'
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function portal_get_sugarinstallations_from_sub($session, $sub_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $query = "SELECT * FROM sugar_installations WHERE license_key = '" . $GLOBALS['db']->quote($sub_id) . "' AND deleted = 0";

    $response = $GLOBALS['db']->query($query);
    $fields = $GLOBALS['db']->getFieldsArray($response);

    $field_list = array();
    foreach ($fields as $value) {
        $field_list[] = array("name" => $value);
    }

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $name_value_list = array();

        foreach ($row as $field => $value) {
            $name_value_list[$field] = array("name" => $field, "value" => $value);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => "SugarInstallations",
            'name_value_list' => $name_value_list,
        );

    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}

/**
 * @author Julian Ostrow
 * @project moofcart
 * @tasknum 20
 * SOAP method that retrieves a list of DiscountCode records (even though there should only be one), given a DiscountCode name
 */

$server->register(
    'get_discount_codes_by_name',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'discount_code_name' => 'xsd:string', 'id' => 'xsd:string'
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function get_discount_codes_by_name($session, $discount_code_name)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $query = "SELECT id FROM discountcodes WHERE discount_code = '" . $GLOBALS['db']->quote($discount_code_name) . "' AND deleted = 0";

    $response = $GLOBALS['db']->query($query);

    $seedDiscountCode = new DiscountCodes();
    $field_list = get_field_list($seedDiscountCode, FALSE);

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $name_value_list = array();

        $discountCode = new DiscountCodes();
        $discountCode->disable_row_level_security = TRUE;
        $discountCode->retrieve($row['id']);

        foreach ($field_list as $field_name => $field_details) {
            $name_value_list[$field_name] = array("name" => $field_name, "value" => $discountCode->$field_name);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => 'DiscountCodes',
            'name_value_list' => $name_value_list,
        );

        unset($discountCode);
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}

/**
 * @author Julian Ostrow
 * @project moofcart
 * @tasknum 20
 * SOAP method that retrieves a single Opportunity, given an Opportunity ID -- we also disable row-level security in this method
 */

$server->register(
    'moofcart_get_opportunity',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'opp_id' => 'xsd:string', 'id' => 'xsd:string'
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function moofcart_get_opportunity($session, $opp_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $query = "SELECT id FROM opportunities WHERE id = '" . $GLOBALS['db']->quote($opp_id) . "' AND deleted = 0";

    $response = $GLOBALS['db']->query($query);

    $seedOpp = new Opportunity();
    $field_list = get_field_list($seedOpp, FALSE);

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $name_value_list = array();

        $opp = new Opportunity();
        $opp->disable_row_level_security = TRUE;
        $opp->retrieve($row['id']);

        foreach ($field_list as $field_name => $field_details) {
            $name_value_list[$field_name] = array("name" => $field_name, "value" => $opp->$field_name);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => 'Opportunities',
            'name_value_list' => $name_value_list,
        );

        unset($opp);
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}

// jostrow
// gets a list of all Opportunities related to the given Account

$server->register(
    'moofcart_get_opportunities',
    array(
        'session' => 'xsd:string',
        'account_id' => 'xsd:string',
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function moofcart_get_opportunities($session, $account_id) {
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

	$query = "SELECT opportunities.id FROM opportunities
		LEFT JOIN accounts_opportunities ON accounts_opportunities.opportunity_id = opportunities.id
		WHERE accounts_opportunities.deleted = 0
		AND opportunities.deleted = 0
		AND accounts_opportunities.account_id = '" . $GLOBALS['db']->quote($account_id) . "'";

    $response = $GLOBALS['db']->query($query);

    $seedOpp = new Opportunity();
    $field_list = get_field_list($seedOpp, FALSE);

    $output_list = array();
    while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $name_value_list = array();

        $opp = new Opportunity();
        $opp->disable_row_level_security = TRUE;
        $opp->retrieve($row['id']);

        foreach ($field_list as $field_name => $field_details) {
            $name_value_list[$field_name] = array("name" => $field_name, "value" => $opp->$field_name);
        }

        $output_list[] = array(
            'id' => $row['id'],
            'module_name' => 'Opportunities',
            'name_value_list' => $name_value_list,
        );

        unset($opp);
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}


$server->register(
    'moofcart_get_users_roles',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'user_id' => 'xsd:string', 'id' => 'xsd:string'
    ),
    array(
        'return' => 'xsd:string'
    ),
    $NAMESPACE
);
function moofcart_get_users_roles($session, $user_id)
{
    $error = new SoapError();

    if (!validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $roles = ACLRole::getUserRoleNames($user_id);

    return serialize($roles);
}

$server->register(
    'moofcart_get_order',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'order_id' => 'xsd:string', 'id' => 'xsd:string'
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);
function moofcart_get_order($session, $order_id)
{
    $error = new SoapError();

    if (!validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $arrFields = get_entry($session, 'Orders', $order_id, array());

    $arrFields['entry_list'][0]['assigned_user_roles'] = ACLRole::getUserRoleNames($arrFields['entry_list'][0]['assigned_user_id']);


    $output_list[] = array(
        'id' => $arrFields['entry_list'][0]['id'],
        'module_name' => "Orders",
        'name_value_list' => $arrFields['entry_list'][0]['name_value_list'],

    );
    $account_id = get_relationships($session, 'Orders', $order_id, 'Accounts', null, 0);
    if (isset($account_id['ids'][0])) {
        $account_id = $account_id['ids'][0]['id'];
        $account = get_entry($session, 'Accounts', $account_id, array());
        $output_list[] = array(
            'id' => $account['entry_list'][0]['id'],
            'module_name' => "Accounts",
            'name_value_list' => $account['entry_list'][0]['name_value_list'],

        );
    }
    $contact_id = get_relationships($session, 'Orders', $order_id, 'Contacts', null, 0);
    if (isset($contact_id['ids'][0])) {
        $contact_id = $contact_id['ids'][0]['id'];
        $contact = get_entry($session, 'Contacts', $contact_id, array());
        $output_list[] = array(
            'id' => $contact['entry_list'][0]['id'],
            'module_name' => "Contacts",
            'name_value_list' => $contact['entry_list'][0]['name_value_list'],

        );
    }
    $opp_id = get_relationships($session, 'Orders', $order_id, 'Opportunities', null, 0);
    if (isset($opp_id['ids'][0])) {
        $opp_id = $opp_id['ids'][0]['id'];
        $opp = get_entry($session, 'Opportunities', $opp_id, array());
        $output_list[] = array(
            'id' => $opp['entry_list'][0]['id'],
            'module_name' => "Opportunities",
            'name_value_list' => $opp['entry_list'][0]['name_value_list']
        );
    }

    $prod_ids = get_relationships($session, 'Orders', $order_id, 'Products', null, 0);
    if (count($prod_ids['ids']) > 0) {
        foreach ($prod_ids['ids'] as $prod_id) {
            $prod = get_entry($session, 'Products', $prod_id['id']);
            $output_list[] = array(
                'id' => $prod_id['id'],
                'module_name' => "Products",
                'name_value_list' => $prod['entry_list'][0]['name_value_list']
            );
        }
    }

    // get the documents that are assigned to the order as well
    $doc_ids = get_relationships($session, 'Orders', $order_id, 'Documents', null, 0);
    if (count($doc_ids['ids']) > 0) {
        foreach ($doc_ids['ids'] as $doc_id) {
            $doc = get_entry($session, 'Documents', $doc_id['id']);
            $output_list[] = array(
                'id' => $doc_id['id'],
                'module_name' => 'Documents',
                'name_value_list' => $doc['entry_list'][0]['name_value_list']
            );
        }
    }

    $contract_ids = get_relationships($session, 'Orders', $order_id, 'Contracts', null, 0);
    if (count($contract_ids['ids']) > 0) {
        foreach ($contract_ids['ids'] as $c_id) {
            $contact = get_entry($session, 'Contracts', $c_id['id']);
            $output_list[] = array(
                'id' => $c_id['id'],
                'module_name' => 'Contracts',
                'name_value_list' => $contact['entry_list'][0]['name_value_list']
            );
        }
    }

    $sub_ids = get_relationships($session, 'Orders', $order_id, 'Subscriptions', null, 0);
    if(count($sub_ids['id']) > 0) {
        foreach($sub_ids as $sub_id) {
            $sub = get_entry($session, 'Subscriptions', $sub_id['id']);
            $output_list[] = array(
                'id' => $sub_id['id'],
                'module_name' => 'Subscriptions',
                'name_value_list' => $sub['entry_list']['0']['name_value_list'],
            );
        }
    }

    return array('field_list' => -1, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}


function get_sorted_subscriptions($subs = array())
{
    $subscriptions = array();
    
    foreach ($subs AS $sub) {
        if (!isset($subscriptions[$sub['account_id']])) {
            $subscriptions[$sub['account_id']] = $sub;
        }
        else {
            if ($sub['quantity'] > $subscriptions[$sub['account_id']]['quantity']) {
                $subscriptions[$sub['account_id']] = $sub;
            }
        }
    }

    $expired = array();
    $almost_expired = array();
    $all_good = array();

    foreach ($subscriptions AS $sub) {
        if ($sub['date_diff'] < 0) {
            $expired[$sub['name']] = $sub['account_id'];
        }
        elseif ($sub['date_diff'] > 0 && $sub['date_diff'] < 90) {
            $almost_expired[$sub['name']] = $sub['account_id'];
        }
        else {
            $all_good[$sub['name']] = $sub['account_id'];
        }
    }

    ksort($expired);
    ksort($almost_expired);
    ksort($all_good);

    $return = array();
    $return['expired'] = $expired;
    $return['almost_expired'] = $almost_expired;
    $return['all_good'] = $all_good;
    $return['subscriptions'] = $subscriptions;

    return $return;
}

$server->register(
    'get_preapplied_discounts',
    array(
        'session' => 'xsd:string', 'id' => 'xsd:string',
        'account_id' => 'xsd:string', 'id' => 'xsd:string',
        'opportunity_id' => 'xsd:string', 'id' => 'xsd:string',
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function get_preapplied_discounts($session, $account_id, $opportunity_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    if (empty($account_id) && empty($opportunity_id)) {
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    $field_list = array();
    $output_list = array();

    if (!empty($account_id)) {

        // jostrow NOTE: funky column names:
        //		accounts_cstm.producttemplate_id_c, // discount applies when this product is in cart
        //		accounts_cstm.producttemplate_id1_c, // discount applies TO this product in the cart

        $acc_query = "SELECT
			accounts.id,
			accounts_cstm.producttemplate_id_c,
			accounts_cstm.producttemplate_id1_c,
			accounts_cstm.discount_amount_c,
			accounts_cstm.discount_approval_status_c,
			accounts_cstm.discount_approved_c,
			accounts_cstm.discount_no_expiration_c,
			accounts_cstm.discount_pending_c,
			accounts_cstm.discount_percent_c,
			accounts_cstm.discount_perpetual_c,
			accounts_cstm.discount_to_c,
			accounts_cstm.discount_to_prodcat_c,
			accounts_cstm.discount_valid_from_c,
			accounts_cstm.discount_valid_to_c,
			accounts_cstm.discount_when_c,
			accounts_cstm.discount_when_dollars_c,
			accounts_cstm.discount_when_prodcat_c
		FROM accounts RIGHT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
		WHERE accounts.deleted = 0 AND accounts.id = '" . $GLOBALS['db']->quote($account_id) . "'
		LIMIT 1";

        $acc_res = $GLOBALS['db']->query($acc_query);

        while ($acc_row = $GLOBALS['db']->fetchByAssoc($acc_res)) {
            $name_value_list = array();

            foreach ($acc_row as $k => $v) {
                $name_value_list[$k] = array('name' => $k, 'value' => $v);
            }

            $name_value_list['module_name'] = array('name' => 'module_name', 'value' => 'Accounts');

            $output_list[] = array(
                'id' => 'Accounts',
                'module_name' => 'Accounts',
                'name_value_list' => $name_value_list,
            );
        }
    }

    if (!empty($opportunity_id)) {

        // jostrow NOTE: funky column names:
        //		opportunities_cstm.producttemplate_id_c, // discount applies when this product is in cart
        //		opportunities_cstm.producttemplate_id1_c, // discount applies TO this product in the cart

        $opp_query = "SELECT
			opportunities.id,
			opportunities_cstm.producttemplate_id_c,
			opportunities_cstm.producttemplate_id1_c,
			opportunities_cstm.discount_amount_c,
			opportunities_cstm.discount_approval_status_c,
			opportunities_cstm.discount_approved_c,
			opportunities_cstm.discount_no_expiration_c,
			opportunities_cstm.discount_pending_c,
			opportunities_cstm.discount_percent_c,
			opportunities_cstm.discount_perpetual_c,
			opportunities_cstm.discount_to_c,
			opportunities_cstm.discount_to_prodcat_c,
			opportunities_cstm.discount_valid_from_c,
			opportunities_cstm.discount_valid_to_c,
			opportunities_cstm.discount_when_c,
			opportunities_cstm.discount_when_dollars_c,
			opportunities_cstm.discount_when_prodcat_c
		FROM opportunities RIGHT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c
		WHERE opportunities.deleted = 0 AND opportunities.id = '" . $GLOBALS['db']->quote($opportunity_id) . "'
		LIMIT 1";

        $opp_res = $GLOBALS['db']->query($opp_query);

        while ($opp_row = $GLOBALS['db']->fetchByAssoc($opp_res)) {
            $name_value_list = array();

            foreach ($opp_row as $k => $v) {
                $name_value_list[$k] = array('name' => $k, 'value' => $v);
            }

            $name_value_list['module_name'] = array('name' => 'module_name', 'value' => 'Opportunities');

            $output_list[] = array(
                'id' => 'Opportunities',
                'module_name' => 'Opportunities',
                'name_value_list' => $name_value_list,
            );
        }
    }

    return array('field_list' => $field_list, 'entry_list' => $output_list, 'error' => $error->get_soap_array());
}

// jostrow

$server->register(
    'moofcart_get_purchase_opportunity',
    array(
        'session' => 'xsd:string',
		'account_id' => 'xsd:string',
		'cart_action' => 'xsd:string',
		'product_ids' => 'tns:select_fields',
    ),
    array(
        'return' => 'tns:get_entry_list_result'
    ),
    $NAMESPACE
);

function moofcart_get_purchase_opportunity($session, $account_id, $cart_action, $product_ids) {
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

	$field_list = array();
	$entry_list = array();

	require_once('custom/si_custom_files/MoofCartHelper.php');

	// get the priorities and flip'em so its easier to deal with
	$priority = array_flip(MoofCartHelper::$productToOpportunityPriority);

	// initialize the current priority
	$current_priority = array();

	// loop over the products
	foreach ($product_ids AS $product_id) {
		$current_priority[$product_id] = $priority[$product_id];
	}

	// sort the array maintaining the keys
	asort($current_priority);

	// get the first one
	reset($current_priority);

	// get the opp type from the first ones key
	$filters['opportunity_type'] = MoofCartHelper::$productToOpportunityType[key($current_priority)];

	if (isset(MoofCartHelper::$cartActionToRevenueType[$cart_action])) {
		$filters['Revenue_Type_c'] = MoofCartHelper::$cartActionToRevenueType[$cart_action];
	}
	else {
		$filters['Revenue_Type_c'] = 'New';
	}

	$acc = new Account;
	$acc->retrieve($account_id);
	
	//mail("jbartek@sugarcrm.com","Filters",print_r($filters,true),"From: Jim <jbartek@sugarcrm.com>");



	$res = $GLOBALS['db']->query("SELECT opportunities.id FROM opportunities
		LEFT JOIN accounts_opportunities ON accounts_opportunities.opportunity_id = opportunities.id
		WHERE accounts_opportunities.deleted = 0
		AND opportunities.deleted = 0
		AND accounts_opportunities.account_id = '" . $GLOBALS['db']->quote($account_id) . "'");

	/*mail("jbartek@sugarcrm.com","Query","SELECT opportunities.id FROM opportunities
		LEFT JOIN accounts_opportunities ON accounts_opportunities.opportunity_id = opportunities.id
		WHERE accounts_opportunities.deleted = 0
		AND opportunities.deleted = 0
		AND accounts_opportunities.account_id = '" . $GLOBALS['db']->quote($account_id) . "'","From: Jim <jbartek@sugarcrm.com>");
	*/
	$opps = array();
	while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
		$opps[$row['id']] = new Opportunity;
		$opps[$row['id']]->disable_row_level_security = TRUE;
		$opps[$row['id']]->retrieve($row['id']);
	}

	$valid_opps = array();
	foreach ($opps as $opp) {
		if (
			$opp->opportunity_type == $filters['opportunity_type']
			&& $opp->Revenue_Type_c == $filters['Revenue_Type_c']
			&& in_array($opp->sales_stage, MoofCartHelper::$openOpportunitySalesStages)
		) {
			$valid_opps[] = $opp;
		}
	}

        //mail("jbartek@sugarcrm.com","Valid Opps",print_r($valid_opps,true),"From: Jim <jbartek@sugarcrm.com>");


	//$valid_opps = array();
	//$valid_opps[] = $opps['ba7c0971-46ac-2552-fb9c-4c333d44b647'];

        //mail("jbartek@sugarcrm.com","Valid Opps - Jim",print_r($valid_opps,true),"From: Jim <jbartek@sugarcrm.com>");

	if (count($valid_opps) == 1) {
		//mail("jbartek@sugarcrm.com","Valid Opps - Jim",print_r($valid_opps[0]->id,true),"From: Jim <jbartek@sugarcrm.com>");

		$name_value_list['id'] = array('name' => 'id', 'value' => $valid_opps[0]->id);

		$entry_list[] = array(
			'id' => $valid_opps[0]->id,
			'module_name' => 'Opportunities',
			'name_value_list' => $name_value_list,
		);

	    return array('field_list' => $field_list, 'entry_list' => $entry_list, 'error' => $error->get_soap_array());
	}

    return array('field_list' => $field_list, 'entry_list' => $entry_list, 'error' => $error->get_soap_array());
}



$server->register(
        'portal_get_document_revision',
        array('session'=>'xsd:string','id'=>'xsd:string'),
        array('return'=>'tns:return_document_revision'),
        $NAMESPACE);

/**
 * This method is used as a result of the .htaccess lock down on the cache directory. It will allow a
 * properly authenticated user to download a document that they have proper rights to download.
 *
 * @param String $session -- Session ID returned by a previous call to login.
 * @param String $id      -- ID of the document revision to obtain
 * @return return_document_revision - this is a complex type as defined in SoapTypes.php
 */
function portal_get_document_revision($session,$id)
{
    global $sugar_config;

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }

    
    $dr = new DocumentRevision();
    $dr->retrieve($id);
    if(!empty($dr->filename)){
        $filename = $sugar_config['upload_dir']."/".$dr->id;
        $handle = sugar_fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        $contents = base64_encode($contents);

        return array('document_revision'=>array('id' => $dr->id, 'document_name' => $dr->document_name, 'revision' => $dr->revision, 'filename' => $dr->filename, 'file' => $contents), 'error'=>$error->get_soap_array());
    }else{
        $error->set_error('no_records');
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }

}

$server->register(
        'moofcart_find_opportunity',
        array('session'=>'xsd:string','order_id'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);

function moofcart_find_opportunity($session, $order_id)
{
    $error = new SoapError();

    if (!validate_authenticated($session)) {
        return -1;
    }
	
	//mail("jbartek@sugarcrm.com","Find Opportunity Called","Find Called","From: Jim <jbartek@sugarcrm.com>");
    
    require_once('custom/si_logic_hooks/Orders/findOpportunity.php');

    $find = new findOpportunity();

    $order = new Orders();
    $order->retrieve($order_id);

	//mail("jbartek@sugarcrm.com","Order ID: {$order_id}",print_r($order,true),"From: Jim <jbartek@sugarcrm.com>");


    $ret = $find->find($order, 'after_save', array());

    return (string) $ret;
}


$server->register(
        'moofcart_close_order',
        array('session'=>'xsd:string','order_id'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);

function moofcart_close_order($session, $order_id)
{
    $error = new SoapError();

    if (!validate_authenticated($session)) {
        return -1;
    }

    $order = new Orders();
    $order->retrieve($order_id);

    $ret = MoofCartHelper::completeOrder($order, true, false);

    return (string) $ret;
}

//determineOrderStatus
$server->register(
        'moofcart_determineOrderStatus',
        array('session'=>'xsd:string','order_id'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);

function moofcart_determineOrderStatus($session, $order_id)
{
    $error = new SoapError();

    if (!validate_authenticated($session)) {
        return -1;
    }

    $order = new Orders();
    $order->retrieve($order_id);

    $ret = MoofCartHelper::determineOrderStatus($order);

    $order->status = $ret;
    $order->save(false);

    return (string) true;
}

$server->register(
        'moofcart_get_agreement_types',
        array(
			'session'=>'xsd:string',
			'products'=>'xsd:string',
			'cart_action'=>'xsd:string',
			'account_id' => 'xsd:string',
			'is_reseller_purchase'=>'xsd:string',
		),
	    array('return' => 'xsd:string'),
        $NAMESPACE);

function moofcart_get_agreement_types($session, $products, $cart_action, $account_id, $is_reseller_purchase) {
    global $sugar_config;

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
		return -1;
    }

	$products = unserialize($products);

	if(empty($products)) {
		return serialize(array());
	}

	$agreements = array();
	foreach($products AS $product_id) {
		$agreements[$product_id] = ( isset( MoofCartHelper::$products_to_agreements[$product_id] ) ) ? MoofCartHelper::$products_to_agreements[$product_id] : array();
	}

	if($cart_action == 'add_users' || $cart_action == 'upgrade_enterprise' || $cart_action == 'renew') {
		foreach($agreements AS $product_id => $a) {
			$pt = new ProductTemplate();
			$pt->retrieve($product_id);
            $category = MoofCartHelper::$product_template_categories[$pt->category_id];
			if($category == 'Partnerships' || $category == 'Subscriptions') {
				unset($agreements[$product_id]);
			}
		}
	}

	if (!empty($is_reseller_purchase) && !empty($account_id)) {
		$acc = new Account();
		$acc->disable_row_level_security = TRUE;
		$acc->retrieve($account_id);

		if (!empty($acc->customer_msa_not_required_c)) {
			foreach($agreements AS $product_id => $a) {
				$pt = new ProductTemplate();
				$pt->retrieve($product_id);
	            $category = MoofCartHelper::$product_template_categories[$pt->category_id];

				if ($category == 'Subscriptions') {
					unset($agreements[$product_id]);
				}
			}

		}
	}

    return serialize($agreements);
}


$server->register(
        'moofcart_get_needed_agreements',
        array('session'=>'xsd:string','order_id'=>'xsd:string'),
            array('return' => 'xsd:string'),
        $NAMESPACE);

function moofcart_get_needed_agreements($session, $order_id)
{
    global $sugar_config;

    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
                return -1;
    }
        global $current_user;
        $current_user = new User();
        $current_user->getSystemUser();

	if(!isValidGuid($order_id)) {
		return false;
	}

	$agreements = MoofCartHelper::checkOrderContracts($order_id);
	

    return serialize($agreements);
}

// jostrow
// had to copy portal_moofcart_upload_contracts() above and make it a little more generic... hopefully we can combine the two at some point

$server->register(
	'portal_moofcart_upload_contract_without_order',
	array(
		'session' => 'xsd:string',
		'document_name' => 'xsd:string',
		'document' => 'xsd:string',
		'document_type'	=>	'xsd:string',
		'account_id' => 'xsd:string',
		'start_date' => 'xsd:string',
		'end_date' => 'xsd:string',
	),
	array('return' => 'xsd:string'),
	$NAMESPACE);

function portal_moofcart_upload_contract_without_order($session, $document_name, $document, $document_type, $account_id, $start_date, $end_date) {
	require_once('modules/Documents/DocumentSoap.php');
	require_once('modules/DocumentRevisions/DocumentRevision.php');
	require_once('custom/si_custom_files/MoofCartHelper.php');

	if (!portal_validate_authenticated($session)) {
		return 0;
	}

	global $current_user;
	$current_user = new User();
	$current_user->getSystemUser();

	$fields = array();
	
	$fields['name'] = $document_name;
	$fields['start_date'] = date('Y-m-d');
	$fields['customer_signed_date'] = date('Y-m-d');
	$fields['status'] = 'with_sales_opps';
	$fields['account_id'] = $account_id;
	$fields['assigned_user_id'] = MoofCartHelper::$salesop_id;
	$fields['team_id'] = 1;
	$fields['team_set_id'] = 1;
	$fields['start_date'] = $start_date;
	$fields['end_date'] = $end_date;
	$fields['date_modified'] = date('Y-m-d H:i:s');
	$fields['date_entered'] = date('Y-m-d H:i:s');
	$fields['created_by'] = 1;
	$fields['modified_user_id'] = 1;
	$fields['agreement_type_c'] = $document_type;
	$fields['execution_status_c'] = 'Fully Executed';

	$c = new Contract;
	foreach($fields AS $k => $v) {
		$c->$k = $v;
	}

	$c->save();

	$fields['id'] = $c->id;

	$d = new Document();
	$d->document_name = $document_name;
	$d->category_id = 'agreements';
    
	$d->status_id = 'Under Review';
	$d->active_date = date('Y-m-d');
	$d->team_set_id = 1;
	$d->team_id = 1;

	//get new document id
	$d_id = $d->save(FALSE);
	$d->retrieve($d_id);

	$dr = new DocumentSoap();
	$document_revision = array('file' => $document, 'filename' => $document_name, 'id' => $d->id, 'revision' => '1.0');
	$id = $dr->saveFile($document_revision);

	$d->document_revision_id = $id;

	$d->save(FALSE);
	$d->load_relationship('contracts');
	$d->contracts->add($fields['id']);    

	return $fields['id'];
}

// jostrow
// generic function to create a Subscription via SOAP

$server->register(
	'moofcart_create_subscription',
	array(
		'session' => 'xsd:string',
		'account_id' => 'xsd:string',
		'end_date' => 'xsd:string',
		'distgroup_id'	=>	'xsd:string',
		'users' => 'xsd:string',
	),
	array('return' => 'xsd:string'),
	$NAMESPACE);

function moofcart_create_subscription($session, $account_id, $end_date, $distgroup_id, $users) {
	require_once('modules/Subscriptions/Subscription.php');
	require_once('custom/si_custom_files/MoofCartHelper.php');

	if (!portal_validate_authenticated($session)) {
		return 0;
	}

	global $current_user;
	$current_user = new User();
	$current_user->getSystemUser();

	$sub = new Subscription;
	$sub->subscription_id = md5($session . $account_id . $end_date . $distgroup_id . $users . rand(100, 999));
	$sub->expiration_date = $end_date;
	$sub->account_id = $account_id;
	$sub->status = 'enabled';
	$sub->assigned_user_id = MoofCartHelper::$salesop_id;
	$sub->save();

	$sub->load_relationship('distgroups');
	$sub->distgroups->add($distgroup_id, array('quantity' => $users));

	// note: returning the Subscription itself, not the internal GUID
	return $sub->subscription_id;
}





/**
	ITR 19937 and ITR 20001
	jbartek
	By removing the partner portal_name check I had to create a new get opportunity for purchase_by_opportunity
	to use the account and not portal_name
*/

$server->register(
    'partner_portal_get_opportunity_to_purchase',
    array('portal_name' => 'xsd:string', 'session' => 'xsd:string', 'opportunity_id' => 'xsd:string'),
    array('return' => 'xsd:string'),
    $NAMESPACE);

function partner_portal_get_opportunity_to_purchase($portal_name, $session, $opportunity_id)
{
    $error = new SoapError();

    if (!portal_validate_authenticated($session)) {
        $error->set_error('invalid_session');
        return array('field_list' => -1, 'entry_list' => array(), 'error' => $error->get_soap_array());
    }


    if (!isValidGUID($opportunity_id)) {
        return serialize(array());
    }

/*
    $accountquery = "
SELECT DISTINCT accounts.*
FROM accounts
LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c AND account_id_c IS NOT NULL
LEFT JOIN accounts AS partner_accounts ON accounts_cstm.account_id_c = partner_accounts.id AND partner_accounts.deleted = 0
LEFT JOIN accounts_contacts AS partner_account_contacts ON partner_account_contacts.account_id = partner_accounts.id AND partner_account_contacts.deleted=0 
LEFT JOIN contacts AS partner_contact ON partner_account_contacts.contact_id = partner_contact.id
WHERE partner_contact.portal_name = '{$portal_name}'
AND accounts.deleted = 0
{$filterSQL}
ORDER BY accounts.name
";
*/
// ITR 19937 and 20001 jbartek - Changing the My Customers to use the partner account not contact.portal_name
$partner_account_id_query = "SELECT accounts.id
FROM contacts
LEFT JOIN accounts_contacts ON contacts.id = accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = 0
WHERE contacts.portal_name = '{$portal_name}'
AND contacts.portal_active = 1
AND accounts.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.deleted = 0
AND contacts.deleted = 0
";

$response = $GLOBALS['db']->query($partner_account_id_query);

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
        $partner_account_id = $row['id'];
        // if partner account isn't valid return an empty array
        if (!isValidGUID($partner_account_id)) {
                return serialize(array());
        }
}

        $opp_query = "SELECT DISTINCT opportunities.id, opportunities.name,opportunities.description,accounts.name account_name, jtl0.account_id account_id , opportunities.amount_usdollar, opportunities.amount, opportunities_cstm.accepted_by_partner_c,accounts.account_type,DATE_FORMAT(opportunities.date_entered,'%m/%d/%y') as created, opportunities_cstm.opportunity_type type,DATE_FORMAT(opportunities.date_closed,'%m/%d/%y') as decision,opportunities_cstm.users subscriptions,opportunities_cstm.current_solution current_solution, accounts.billing_address_street street, accounts.billing_address_city city, accounts.billing_address_state state, accounts.billing_address_country country, accounts.website, opportunities.sales_stage,  opportunities.next_step, DATE_FORMAT(opportunities_cstm.next_step_due_date,'%m/%d/%y') as next_step_due_date, opportunities_cstm.competitor_1, opportunities.description,opportunities_cstm.closed_lost_reason_c, opportunities_cstm.closed_lost_reason_detail_c, opportunities_cstm.primary_reason_competitor_c, opportunities_cstm.closed_lost_description
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
WHERE 
partner_account.id = '{$partner_account_id}'
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND opportunities.deleted =0
AND opportunities.id = '{$opportunity_id}'
";

//mail("jbartek@sugarcrm.com","opp sql",$opp_query,"From: Jim <jbartek@sugarcrm.com>");

$response = $GLOBALS['db']->query($opp_query);

$return = array();

while ($row = $GLOBALS['db']->fetchByAssoc($response)) {
	$return[] = $row;
}
//mail("jbartek@sugarcrm.com","opp return",print_r($return,true),"From: Jim <jbartek@sugarcrm.com>");
return serialize($return);

}

// jostrow
// needed a method to grab an Account's Support Service Level, regardless of record visibility rules

$server->register(
	'moofcart_get_account_support_service_level',
	array(
		'session' => 'xsd:string',
		'account_id' => 'xsd:string',
	),
	array(
		'return' => 'xsd:string',
	),
	$NAMESPACE
);

function moofcart_get_account_support_service_level($session, $account_id) {
	if (!portal_validate_authenticated($session)) {
		return 0;
	}

	if (!isValidGUID($account_id)) {
		return 0;
	}

	require_once('modules/Users/User.php');
	require_once('modules/Accounts/Account.php');

	$current_user = new User();
	$current_user->getSystemUser();

	$account = new Account();
	$account->disable_row_level_security = TRUE;
	$account->retrieve($account_id);

	if ($account->id != $account_id) {
		return 0;
	}

	return $account->Support_Service_Level_c;
}
