<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layouts = array(
	 'Proposal'=>'custom/modules/Quotes/layouts/Proposal.php'
	,'Proposal_Terms'=>'custom/modules/Quotes/layouts/Proposal_Terms.php'
	);

// SUGARINTERNAL CUSTOMIZATION ITR: 16052 - jwhitcraft
global $current_user;
if($current_user->check_role_membership('Accounting')) {
    $layouts['Invoice'] = 'custom/modules/Quotes/layouts/Invoice.php';
}
// END SUGARINTERNAL CUSTOMIZATION
