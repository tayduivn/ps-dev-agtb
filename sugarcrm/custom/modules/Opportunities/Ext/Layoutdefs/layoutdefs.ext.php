<?php 
 //WARNING: The contents of this file are auto-generated


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Opportunities']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";

// created: 2009-02-13 17:29:10
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_accounts"] = array (
  'order' => 100,
  'module' => 'Accounts',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_OPPORTUNITIES_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'get_subpanel_data' => 'opportunities_accounts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'Accounts',
      'mode' => 'MultiSelect',
    ),
  ),
);



// created: 2009-09-14 16:18:10 #ITR 10318
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_leadaccounts"] = array (
 			'order' => 30,
			'module' => 'LeadAccounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'leadaccounts',
			'add_subpanel_data' => 'leadaccount_id',
			'title_key' => 'LBL_LEADACCOUNTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'Opportunities',
					'mode' => 'MultiSelect', 
				),
			),
);



//subpanel to show related interactions in opportunity detail
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_interactions"] = array (
 			'order' => 110,
			'module' => 'Interactions',
			'sort_order' => 'desc',
			'sort_by' => 'score',
			'subpanel_name' => 'default',
			'get_subpanel_data'=>"function:get_related_interactions_query",
			'function_parameters'=>array('bean_id'=>$this->_focus->id,'import_function_file'=>'custom/modules/Opportunities/get_related_interactions_query.php'),
			'title_key' => 'LBL_OPPORTUNITY_LEADCOMPANY_INTERACTION_SUBPANEL_TITLE',
			'top_buttons' => array(),
		);

//subpanel to show related interactions in OppQ screen
$layout_defs["Opportunities"]["subpanel_setup"]["for_opp_q"] = array (
                        'order' => 110,
                        'module' => 'Interactions',
                        'sort_order' => 'desc',
                        'sort_by' => 'score',
                        'subpanel_name' => 'forOppQ',
                        'get_subpanel_data'=>"function:get_related_interactions_query",
                        'function_parameters'=>array('bean_id'=>$this->_focus->id,'import_function_file'=>'custom/modules/Opportunities/get_related_interactions_query.php'),
                        'title_key' => 'LBL_OPPORTUNITY_LEADCOMPANY_INTERACTION_SUBPANEL_TITLE',
                        'top_buttons' => array(),
                );


//** BEGIN EDDY CUSTOMIZATION  ITTix 12567 
//this layout def is used for history subpanel in oppq screen
$layout_defs["Opportunities"]["subpanel_setup"]["history_for_oppq"] = array (
                        'order' => 25,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_modified',
                        'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
                        'type' => 'collection',
                        'subpanel_name' => 'history',   //this values is not associated with a physical file.
                        'module'=>'History',

                        'top_buttons' => array(
            array('widget_class' => 'SubPanelTopSummaryButton'),
                        ),

                        'collection_list' => array(
                                'meetings' => array(
                                        'module' => 'Meetings',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'meetings',
					'override_subpanel_name'=>'ForHistoryOppQ',
                                ),
                                'tasks' => array(
                                        'module' => 'Tasks',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'tasks',
					'override_subpanel_name'=>'ForHistoryOppQ',

                                	
				),
                                'calls' => array(
                                        'module' => 'Calls',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'calls',
                                	'override_subpanel_name'=>'ForHistoryOppQ',
				),
                                'notes' => array(
                                        'module' => 'Notes',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'notes',
					'override_subpanel_name'=>'ForHistoryOppQ',
                                ),
                                'emails' => array(
                                        'module' => 'Emails',
                                        'subpanel_name' => 'ForHistory',
                                        'get_subpanel_data' => 'emails',
					'override_subpanel_name'=>'ForHistoryOppQ',

                                ),
                        )
                );
//** END EDDY CUSTOMIZATION  ITTix 12567 




$layout_defs['Opportunities']['subpanel_setup']['history']['collection_list']['linkedemails_contacts'] =  array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForUnlinkedEmailHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query_via_link',
	    		    'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'link' => 'contacts'),
	                'generate_select'=>true,
				    'get_distinct_data' => true,
);


// created: 2010-07-23 10:59:27
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-23 11:06:23
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-23 11:14:22
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-23 11:45:10
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-23 11:48:19
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-23 11:50:22
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


// created: 2010-07-27 10:20:48
$layout_defs["Opportunities"]["subpanel_setup"]["sales_seticket_opportunities"] = array (
  'order' => 100,
  'module' => 'sales_SETicket',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_SALES_SETICKET_OPPORTUNITIES_FROM_SALES_SETICKET_TITLE',
  'get_subpanel_data' => 'sales_seticket_opportunities',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);



//auto-generated file DO NOT EDIT
$layout_defs['Opportunities']['subpanel_setup']['opportunities_accounts']['override_subpanel_name'] = 'Opportunitydefault';


//auto-generated file DO NOT EDIT
$layout_defs['Opportunities']['subpanel_setup']['contacts']['override_subpanel_name'] = 'OpportunityForOpportunities';


//auto-generated file DO NOT EDIT
$layout_defs['Opportunities']['subpanel_setup']['leads']['override_subpanel_name'] = 'Opportunitydefault';

?>