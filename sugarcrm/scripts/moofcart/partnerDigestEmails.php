<?php
chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Administration/Administration.php');
require_once('include/SugarPHPMailer.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('custom/si_custom_files/MoofCartHelper.php');
require_once('XTemplate/xtpl.php');

$tpl= new XTemplate('custom/si_custom_files/tpls/moofcart_emails/partner_digest.tpl');


global $current_user;
$current_user = new User();
$current_user->getSystemUser();

$queries = array();

$queries[ 'pending' ] = "SELECT count(*) AS total, oc.contact_id_c FROM opportunities o LEFT JOIN opportunities_cstm oc ON o.id = oc.id_c WHERE DATEDIFF(o.date_modified,CURDATE()) >= -7 AND oc.accepted_by_partner_c NOT IN ('Y','R') AND oc.contact_id_c != '' GROUP BY oc.contact_id_c";

$queries[ 'total_past_week' ] = "SELECT count(*) AS total, oc.contact_id_c FROM opportunities o LEFT JOIN opportunities_cstm oc ON o.id = oc.id_c WHERE DATEDIFF(o.date_modified,CURDATE()) >= -7 AND oc.contact_id_c != '' GROUP BY oc.contact_id_c";


$partners = array();
foreach ($queries as $name => $query) {
	$res = $GLOBALS['db']->query($query);
	if (!$res) {
		$GLOBALS['db']->checkError('Error with query - ' . $name);
		$GLOBALS['log']->fatal("----->partnerDigestEmails.php query failed: " . $query);
		exit;
	} 
	else {
		while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
			$partners[$row['contact_id_c']][$name] = $row['total'];
		}
	}
}

$subject = 'SugarCRM: Your Weekly Opportunities Digest';

$status = array(
				'Y' => 'Accepted',
				'R' => 'Rejected',
				'P' => 'Pending',
				'' => 'Pending',
			);

foreach($partners AS $id => $vars) {	

	$con = new Contact();
	$con->retrieve($id);
	
	$email = reset($con->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));

	$tpl->assign('contact',$con->fetched_row);
	
	$tpl->assign('v', $vars);
	
	$opp_query = $GLOBALS['db']->query("SELECT o.id FROM opportunities o LEFT JOIN opportunities_cstm oc ON o.id = oc.id_c WHERE DATEDIFF(o.date_modified,CURDATE()) >= -7 AND oc.contact_id_c = '{$id}'");
		
	if(!$opp_query) {
		$GLOBALS['db']->checkError('Error with opportunity query - ' . $name);
		$GLOBALS['log']->fatal("----->partnerDigestEmails.php query failed: " . $query);
		exit;		
	}
	
	
	while($row = $GLOBALS['db']->fetchByAssoc($opp_query)) {
		$opp = new Opportunity();
		$opp->retrieve($row['id']);

		$acc = new Account();
		$acc->retrieve($opp->account_id);
		
		$product_map = array_flip(MoofCartHelper::$productToOpportunityType);
		
		$product_id = $product_map[$opp->opportunity_type];
		
		$prod = new ProductTemplate();
		$prod->retrieve($product_id);
		
		$array = $opp->fetched_row;
		$array['partner_status'] = $status[$array['accepted_by_partner_c']];
		$array['partner_product'] = $prod->name;
		
		$account = $acc->fetched_row;
		$location = '';
		if(!empty($acc->billing_address_city)) {
			$location .= $acc->billing_address_city . ', <br />';
		}
		if(!empty($acc->billing_address_state)) {
			$location .= $acc->billing_address_state . '<br />';
		}
		
		if(!empty($acc->billing_address_country)) {
			$location .= $acc->billing_address_country;
		}
		
		$account['location'] = $location;
		
		$tpl->assign('o',$array);
		$tpl->assign('a',$account);
		$tpl->parse('main.opportunities.opportunity');
	}

	$tpl->parse('main.opportunities');

	$tpl->parse('main');
	$email_html = $tpl->text('main');
	
	$to = array();
	$to[] = array('email'=>$email->email_address,'name'=>$con->first_name . ' ' . $con->last_name);

	sendEmail($current_user,$to,$email_html);
}


function sendEmail(&$user,$to=array(), $body) {
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
	
	$subject = $mail->Subject = "SugarCRM: Your Weekly Opportunities Digest";

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
