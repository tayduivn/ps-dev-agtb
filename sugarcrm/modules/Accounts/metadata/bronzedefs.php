<?php
//FILE SUGARCRM flav=int ONLY
$GLOBALS['bronzedefs']['Accounts']=array(
	'edit'=>array(
		'type'=>'edit',
		'label'=>'Edit',
		'module'=>'Accounts',

	),
	'Subpanels'=>array(
		'type'=>'subpanel',
		'label'=>'Subpanels',
		'module'=>'Accounts',

	),
	'news'=>array(
		'type'=>'newsfeed',
		'params'=>array('q'=>'name'),
		'label'=>'News',
		'module'=>'Accounts',

	),
	'reports'=>array(
	
		'type'=>'group',
		'label'=>'Reports',
		'tabs'=>array( 
				
		
			'case_load'=>array(
				'type'=>'report',
				'record'=> 'c0d80c55-3992-07de-52d1-486a8948bf4d',
				'runtime_params'=>array(
					'id'=>'$this->id'
				),
				'label'=>'Cases',
				'module'=>'Opportunities',
				
			),
	
			'quarter_opps'=>array(
				'type'=>'report',
				'record'=> '46c35db4-db8f-0619-e9ca-4862c7493847',
				'runtime_params'=>array(
					'id'=>'$this->id'
				),
				'label'=>'Quarterly Report',
				'module'=>'Opportunities',
				
			),
		),
	),
	'maps'=>array(
		'type'=>'map',
		'addresses'=>array('billing', 'shipping'),
		'module'=>'Accounts',
		'label'=>'Map',
	
	),

	'contacts' => array(
			'order' => 30,
			'module' => 'Contacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'ForAccounts',
			'get_subpanel_data' => 'contacts',
			'add_subpanel_data' => 'contact_id',
			'label' => 'LBL_CONTACTS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateAccountNameButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),

	),
	'dashlets'=>array(
		'type'=>'dashlets',
		'label'=>'Dashlets',
		'dashlets'=>array(
					'JotPadDashlet'=>'',
					'InvadersDashlet'=>'',
					/*'GadgetDashlet'=>'',*/
				),
	),

	
	
);
?>