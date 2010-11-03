<?php
chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Administration/Administration.php');
require_once('include/SugarPHPMailer.php');
require_once('modules/Orders/Orders.php');
require_once('custom/si_custom_files/MoofCartHelper.php');
require_once('XTemplate/xtpl.php');



global $current_user;
$current_user = new User();
$current_user->getSystemUser();

$queries = array();

$queries[] = "SELECT id FROM orders WHERE deleted=0 AND status IN ('pending_contract','pending_po')";


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
			$orders[$row['id']] = $row;
		}
	}
}


foreach($orders AS $id => $stuff) {	

	$o = new Orders();
	$o->retrieve($id);

	$tpl= new XTemplate("custom/si_custom_files/tpls/moofcart_emails/{$o->status}.tpl");

	$subject = "SugarCRM: Order #{$o->order_id} Reminder";
	
	$con = reset($o->get_linked_beans('contacts_orders','Contact',array(),0,-1,0));
	
	$email = reset($con->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));

	$tpl->assign('contact',$con->fetched_row);
	
	$tpl->assign('o', $o->fetched_row);
	
	$tpl->parse('main');
	$email_html = $tpl->text('main');
	
	$to = array();
	$to[] = array('email'=>$email->email_address,'name'=>$con->first_name . ' ' . $con->last_name);

	sendEmail($current_user,$to,$email_html,$subject);
}


function sendEmail(&$user,$to=array(), $body,$subject) {
	if(empty($to)) {
		return false;
	}
	$mail = new SugarPHPMailer();
	$admin = new Administration();
	
	
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

	$mail->From = $user->emailAddress->addresses[0]['email_address'];
	$mail->FromName = $user->first_name . ' ' . $user->last_name;
	$mail->ContentType = "text/html"; //"text/plain"
	
	global $sugar_config;
				
	$mail->Body = $body;

	foreach($to AS $t) {
		$mail->AddAddress($t['email'], $t['name']);
	}

/*
	$to = 'julian@sugarcrm.com';

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Mail it
	mail($to, $subject, $body, $headers);

	exit("a\r\n");
*/

	$mail->AddBCC($user->emailAddress->addresses[0]['email_address'],$user->first_name . ' ' . $user->last_name);
	if (!$mail->send()) {
		$GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
		return false;
	}
	return true;
}

?>
