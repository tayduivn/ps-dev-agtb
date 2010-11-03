<?PHP
$dictionary['Task']['audited'] = true;
//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Task']['fields']['leads'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_calls',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen

$dictionary['Task']['fields']['status']['audited'] = true;
$dictionary['Task']['fields']['status']['type'] = 'enum';
$dictionary['Task']['fields']['priority']['default'] = 'Medium';
?>
