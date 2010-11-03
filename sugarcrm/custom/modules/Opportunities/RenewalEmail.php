<?php
require_once('custom/si_custom_files/MoofCartHelper.php');

$bean = new Opportunity();
$bean->retrieve($_REQUEST['record']);

$account = new Account();
$account->retrieve($bean->account_id);

$assigned_user = new User();
$assigned_user->retrieve($bean->assigned_user_id);

$assigned_user_email = reset($assigned_user->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));

$contacts = $bean->get_linked_beans('contacts','Contact',array(),0,-1,0);

$to = array();

if(empty($bean->date_closed)) {
	$date_closed = date('m-d-Y');
}
else {
	$date_closed = $bean->date_closed;
}

// check if partner
if(!empty($bean->contact_id_c)) {
	$partner_contact = new Contact();
	$partner_contact->retrieve($bean->contact_id_c);
	$email[$partner_contact->id] = reset($partner_contact->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));
	$to[$partner_contact->first_name . ' ' . $partner_contact->last_name] = array('id' => $partner_contact->id, 'email' => $email[$partner_contact->id]->email_address);

	$replacements = array(
		'CUSTOMER_NAME' => $account->name,
		'PARTNER_NAME' => $partner_contact->name,
		'OPPORTUNITY_TYPE' => $bean->opportunity_type,
		'DISCOUNT' => (!empty($bean->discount_code_c)
		? MoofCartHelper::$customer_template_discount : ''),
		'EXPIRATION_DATE' => $date_closed,
		'ASSIGNED_REP_NAME' => $assigned_user->name,
		'ASSIGNED_REP_TITLE' => $assigned_user->title,
		'ASSIGNED_REP_EMAIL' => $assigned_user_email->email_address,
		'ASSIGNED_REP_OFFICE_PHONE' => $assigned_user->phone_work,
		'CART_LINK' => MoofCartHelper::getRenewalCartLink($bean),
		'LINK_TO_PARTNER_ACCOUNT' => 'https://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record='
		. $opportunity->partner_assigned_to_c,
		'MISSING_PARTNER_NOTICE' => '',

	);
	$subject = MoofCartHelper::$partner_subject;
	$body = MoofCartHelper::getRenewalEmail($replacements,'partner');
}
else {
	foreach($contacts AS $contact) {
		$email[$contact->id] = reset($contact->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));
		$to[$contact->first_name . ' ' . $contact->last_name] = array('id' => $contact->id, 'email' => $email[$contact_id]->email_address);
	}
	
	if(!empty($account->contact_id3_c)) {
		$contact = new Contact();
		$contact->retrieve($account->contact_id3_c);
		$email[$contact->id] = reset($contact->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));
		$to[$contact->first_name . ' ' . $contact->last_name] = array('id' => $contact_id, 'email' => $email[$contact->id]->email_address);
	}
	
	$replacements = array(
		'CUSTOMER_NAME' => $account->name,
		'OPPORTUNITY_TYPE' => $opportunity->type,
		'DISCOUNT' => (!empty($opportunity->discount_code_c)
		? $customer_template_discount : ''),
		'EXPIRATION_DATE' => $date_closed,
		'ASSIGNED_REP_NAME' => $assigned_user->name,
		'ASSIGNED_REP_TITLE' => $assigned_user->title,
		'ASSIGNED_REP_EMAIL' => $assigned_user_email->email_address,
		'PARTNER_NAME' => '',
		'ASSIGNED_REP_OFFICE_PHONE' => $assigned_user->phone_work,
		'CART_LINK' => MoofCartHelper::getRenewalCartLink($opportunity),
		'MISSING_PARTNER_NOTICE' => '',
		'LINK_TO_PARTNER_ACCOUNT' => ''
	);

	
	
	$subject = MoofCartHelper::$customer_subject;
	$body = MoofCartHelper::getRenewalEmail($replacements,'html');
}


$email_bean = new Email();
$email_bean->id = create_guid();
$email_bean->new_with_id = true;
$email_bean->parent_type = 'Opportunities';
$email_bean->parent_id = $bean->id;

$email_bean->name = $subject;

$email_bean->from_addr = $assigned_user->email1;
$email_bean->from_name = $assigned_user->first_name . " " . $assigned_user->last_name;
$email_bean->to_addrs_arr = array();
$email_bean->bcc_addrs = $assigned_user->emailAddress->addresses[0]['email_address'];

$string = array();
$emails = array();
$ids = array();
foreach($to AS $name=>$data) {
	$email_bean->to_addrs_arr[] = array('email' => $data['email'],
								    	'name' => $name);
								    	
	$string[] = $data['email'];
	$names[] = $name;
	$ids[] = $data['id'];
	
}

//$email_bean->to_addrs = to_html(implode(',', $to)); 

$email_bean->to_addrs = implode(',',$string);

$email_bean->to_addrs_emails = implode(',', $string);
$email_bean->to_addrs_names = implode(',', $names);
$email_bean->to_addrs_ids = implode(',', $ids);

$email_bean->cc_addrs_arr = array();


$email_bean->description_html = $body;

//$email_bean->description = $text;

$email_bean->date_sent = date('Y-m-d h:i:s');
$email_bean->assigned_user_id = $assigned_user->id;

$email_bean->type = 'draft';
$email_bean->status = 'draft';

$email_bean->save();
$email_bean->new_with_id = false;	

$email_bean->load_relationship('users');
$email_bean->users->add($assigned_user->id);

$email_bean->load_relationship('opportunities');
$email_bean->opportunities->add($bean->id);

$email_bean->load_relationship('accounts');
$email_bean->accounts->add($account->id);

$email_bean->load_relationship('contacts');

foreach($contacts AS $contact) {
	$email_bean->contacts->add($contact->id);
	$email_bean->users->add($contact->id);
}

if(!empty($bean->contact_id_c)) {
	$email_bean->contacts->add($partner_contact->id);
	$email_bean->users->add($partner_contact->id);
}

$email_bean->save(FALSE);


// redirect to the email for modification
header("Location:/index.php?module=Emails&action=Compose&record={$email_bean->id}&replyForward=true&reply=reply" );
exit();

?>
