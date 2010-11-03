<?php
chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Administration/Administration.php');
require_once('include/SugarPHPMailer.php');
require_once('modules/Orders/Orders.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Emails/Email.php');
require_once('modules/Contacts/Contact.php');
require_once('custom/si_custom_files/MoofCartHelper.php');
require_once('XTemplate/xtpl.php');



global $current_user;
$current_user = new User();
$current_user->getSystemUser();

$queries = array();

$queries[] = "SELECT DISTINCT orders.id, contacts.id AS partner_contact_id, accounts.id AS partner_account_id  FROM orders LEFT JOIN accounts_orders_c ON orders.id = accounts_orders_c.accounts_o0f8dsorders_idb AND accounts_orders_c.deleted = 0 LEFT JOIN accounts ON accounts_orders_c.accounts_od749ccounts_ida = accounts.id AND accounts.deleted = 0 LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c and accounts_cstm.account_id_c IS NOT NULL AND accounts_cstm.Partner_Type_c IS NOT NULL LEFT JOIN accounts_contacts ON accounts_cstm.id_c = accounts_contacts.account_id AND accounts_contacts.deleted = 0 LEFT JOIN contacts ON accounts_contacts.contact_id = contacts.id AND contacts.deleted=0 WHERE orders.deleted = 0 AND orders.status IN ('pending_contract','pending_po')";


$orders = array();
foreach ($queries as $query) {
	$res = $GLOBALS['db']->query($query);
	if (!$res) {
		$GLOBALS['db']->checkError('Error with query - ' . $name);
		$GLOBALS['log']->fatal("----->pendingOrderReminder.php query failed: " . $query);
		exit;
	} 
	else {
		while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
			$orders[] = $row;
		}
	}
}


/*
echo "SELECT DISTINCT orders.id, contacts.id AS partner_contact_id, accounts.id AS partner_account_id  FROM orders LEFT JOIN accounts_orders_c ON orders.id = accounts_orders_c.accounts_o0f8dsorders_idb AND accounts_orders_c.deleted = 0 LEFT JOIN accounts ON accounts_orders_c.accounts_od749ccounts_ida = accounts.id AND accounts.deleted = 0 LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c and accounts_cstm.account_id_c IS NOT NULL AND accounts_cstm.Partner_Type_c IS NOT NULL LEFT JOIN accounts_contacts ON accounts_cstm.id_c = accounts_contacts.account_id AND accounts_contacts.deleted = 0 LEFT JOIN contacts ON accounts_contacts.contact_id = contacts.id AND contacts.deleted=0 WHERE orders.deleted = 0 AND orders.status IN ('pending_contract','pending_po')";
exit("\r\n");
*/


foreach($orders AS $partner) {	

	$id = $partner['id'];

	$partner_contact_id = $partner['partner_contact_id'];
	$partner_account_id = $partner['partner_account_id'];

	$o = new Orders();
	$o->retrieve($id);

	$acc = new Account();
	$acc->retrieve($partner_account_id);
	
	$con = new Contact();
	$con->retrieve($partner_contact_id);
	

	$tpl= new XTemplate("custom/si_custom_files/tpls/moofcart_emails/partner_{$o->status}.tpl");

	$subject = "SugarCRM: Order #{$o->order_id} Reminder";
	
	$email = reset($con->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));

	$tpl->assign('contact',$con->fetched_row);
	
	$tpl->assign('o', $o->fetched_row);
	
	$tpl->parse('main');
	$email_html = $tpl->text('main');
	
	unset($tpl);
	
	$to = array();
	$to[] = array('email'=>$email->email_address,'name'=>$con->first_name . ' ' . $con->last_name);

	if($email_id = sendEmail($current_user,$to,$email_html,$subject)!==false) {
		// attach email to order
		$e = new Email();
		$e->retrieve($email_id);
		
		$m = new Email();
		
		$m->parent_type = 'Orders';
		$m->parent_id = $id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
		
		$m->save();
		
		// attach email to partner
		unset($m);
		$m = new Email();
		$m->parent_type = 'Contacts';
		$m->parent_id = $con->id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
				
		$m->save();
		
		// attach email to partner account
		unset($m);
		$m = new Email();
		$m->parent_type = 'Accounts';
		$m->parent_id = $acc->id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
					
		$m->save();
			
		// attach email to customer
		
		unset($m);
		$cust = reset($o->get_linked_beans('contacts_orders', 'Contact',array(),0,-1,0));
		$m = new Email();
		$m->parent_type = 'Contacts';
		$m->parent_id = $cust->id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
		
		$m->save();
			
		// attach email to customer account
		$cust_acc = reset($o->get_linked_beans('accounts_orders', 'Account', array(), 0, -1, 0));

		unset($m);
		$m = new Email();
		$m->parent_type = 'Accounts';
		$m->parent_id = $cust_acc->id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
		
		$m->save();
				
		// attach email to opportunity
		$opp = reset($o->get_linked_beans('orders_opportunities', 'Opportunity',array(),0,-1,0));
		unset($m);
		$m = new Email();
		$m->parent_type = 'Opportunities';
		$m->parent_id = $opp->id;
		
		foreach($e AS $k=>$v) {
			if($k != 'parent_type' && $k != 'parent_id') {
				$m->$k = $v;
			}
		}
		
		$m->save();
		unset($m);
		unset($e);		
	}
}


function sendEmail(&$user,$to=array(), $body,$subject) {
	global $sugar_config;
	if(empty($to)) {
		return false;
	}
	$mail = new SugarPHPMailer();
	$admin = new Administration();
 
 
 	$email = new Email();

	$email->type = 'archived';
	
	
	$admin->retrieveSettings();
	
	if ($admin->settings['mail_sendtype'] == "SMTP") {
		$mail->Host = $admin->settings['mail_smtpserver'];
		$mail->Port = $admin->settings['mail_smtpport'];
		if ($admin->settings['mail_smtpauth_req']) {
			$mail->SMTPAuth = TRUE;
			$mail->Username = $admin->settings['mail_smtpuser'];
			$mail->Password = $admin->settings['mail_smtppass'];
		}
		$mail->Mailer   = "smtp";
		$mail->SMTPKeepAlive = true;
	}
	else {
		$mail->mailer = 'sendmail';
	}

	$email->from_addr = $mail->From = $user->emailAddress->addresses[0]['email_address'];
	$email->from_name = $mail->FromName = $user->first_name . ' ' . $user->last_name;
	$mail->ContentType = "text/html"; //"text/plain"
	
	$email->name = $email->subject = $subject;
				
	$email->description_html = $mail->Body = $body;

	$email->save(FALSE);
	
	foreach($to AS $t) {
		$mail->AddAddress($t['email'], $t['name']);
	}


/*
	$to = 'jbartek@sugarcrm.com';

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Mail it
	echo "EMAIL: ";
	var_dump(mail($to, $subject, $body, $headers));
	echo "\r\n";
	exit();	

*/
	$mail->AddBCC($user->emailAddress->addresses[0]['email_address'],$user->first_name . ' ' . $user->last_name);
	if (!$mail->send()) {
		$GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
		return false;
	}

	$email_id = $email->id;
	unset($email);

	return $email_id;
}

?>
