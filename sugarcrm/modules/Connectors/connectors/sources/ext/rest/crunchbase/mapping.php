<?php
$mapping = array(
	'beans' => array (
		'Leads' => array (
		    'id' => 'id',
			'name' => 'account_name',
			'overview' => 'description',
			'crunchbase_url' => 'website',
		),
		'Accounts' => array (
		    'id' => 'id',
			'name' => 'name',
			'overview' => 'description',
			'crunchbase_url' => 'website',
		),
		'Contacts' => array (
		    'id' => 'id',
			'name' => 'full_name',
			'crunchbase_url' => 'website',	
		),
	),
);
?>