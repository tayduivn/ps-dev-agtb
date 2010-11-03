<?php
//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Email']['fields']['leads_to_emails'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_emails',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS_TO_EMAILS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Email']['fields']['leadaccounts']  = array (
                        'name'                  => 'leadaccounts',
                        'vname'                 => 'LBL_EMAILS_LEADACCOUNTS_REL',
                        'type'                  => 'link',
                        'relationship'  => 'emails_leadaccounts_rel',
                        'module'                => 'LeadAccounts',
                        'bean_name'             => 'LeadAccount',
                        'source'                => 'non-db',
                );
$dictionary['Email']['fields']['leadcontacts']  = array (
                        'name'                  => 'leadcontacts',
                        'vname'                 => 'LBL_EMAILS_LEADCONTACTS_REL',
                        'type'                  => 'link',
                        'relationship'  => 'emails_leadcontacts_rel',
                        'module'                => 'LeadContacts',
                        'bean_name'             => 'LeadContact',
                        'source'                => 'non-db',
                );
			
		

?>
