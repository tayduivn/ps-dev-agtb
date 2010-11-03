<?php 
 //WARNING: The contents of this file are auto-generated


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
			
		




// created: 2010-07-27 10:20:48
$dictionary["Email"]["fields"]["sales_seticket_activities_emails"] = array (
  'name' => 'sales_seticket_activities_emails',
  'type' => 'link',
  'relationship' => 'sales_seticket_activities_emails',
  'source' => 'non-db',
);


$dictionary['Email']['fields']['meetings'] = array(
                        'name'                  => 'meetings',
                        'vname'                 => 'LBL_EMAILS_MEETINGS_REL',
                        'type'                  => 'link',
                        'relationship'  => 'emails_meetings_rel',
                        'module'                => 'Meetings',
                        'bean_name'             => 'Meeting',
                        'source'                => 'non-db',
);
$dictionary['Email']['relationships']['emails_meetings_rel'] = array(
                       'lhs_module'    		 => 'Emails',
                        'lhs_table'          => 'emails',
                        'lhs_key'            => 'id',
                        'rhs_module'         => 'Meetings',
                        'rhs_table'          => 'meetings',
                        'rhs_key'            => 'parent_id',
                        'relationship_type'  => 'one-to-many',
);

// created: 2010-07-27 14:43:43
$dictionary["Email"]["fields"]["orders_activities_emails"] = array (
  'name' => 'orders_activities_emails',
  'type' => 'link',
  'relationship' => 'orders_activities_emails',
  'source' => 'non-db',
);

?>