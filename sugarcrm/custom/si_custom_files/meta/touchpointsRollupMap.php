<?php

$touchpointsRollupMap = array(
//** BEGIN CUSTOMIZATION EDDY IT TIX 12706
	// Sadek note: it will NOT override values, only fill them in if the value is empty on the parent that it rolls up to
	//             The exception for this is the score fields, which will be overridden regardless
	'score' => array(
			'Interaction' => 'score',
			'LeadContact' => 'score',
			'LeadAccount' => 'score',
			'Contact' => 'score_c',
			'Account' => 'score_c',
	),
	'prospect_id_c' => array(
			'Interaction' => '',
			'LeadContact' => 'prospect_id_c',
			'LeadAccount' => '',
			'Contact' => 'prospect_id_c',
			'Account' => '',
	),
	'phone_work' => array(
			'Interaction' => '',
			'LeadContact' => 'phone_work',
			'LeadAccount' => '',
			'Contact' => 'phone_work',
			'Account' => '',
	),
	'phone_other' => array(
			'Interaction' => '',
			'LeadContact' => 'phone_other',
			'LeadAccount' => '',
			'Contact' => 'phone_other',
			'Account' => '',
	),
//** END CUSTOMIZATION EDDY IT TIX 12706
);

