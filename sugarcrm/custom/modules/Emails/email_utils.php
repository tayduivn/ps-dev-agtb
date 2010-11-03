<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


function get_case_contacts($my_case) {
	$contacts = $my_case->get_linked_beans('contacts','Contact');
	$emails = '';
	$ids = '';
	foreach ($contacts as $contact) {
		if (!empty($contact->email1)) {
			$emails .= $contact->email1.';';
			$ids .= $contact->id.';';
		}
	}
	return (array('ids'=>$ids,'emails'=>$emails));
}

?>