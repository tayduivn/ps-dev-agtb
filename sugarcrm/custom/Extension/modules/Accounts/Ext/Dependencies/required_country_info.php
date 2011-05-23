<?php

$dependencies['Accounts']['required_country_info'] = array(
	'hooks' => array("edit"), //Optional, defaults to "all". Valid values are combinations of "all", "edit", "save", "retrieve".
	'triggerFields' => array('parent_id', 'parent_name'), //Optional, the trigger for the dependency. Defaults to 'true'.
	'onload' => true, //Optional. If true the trigger is evaluated when the edit view is first loaded rather just when trigger fields are changed.

	//Actions is a list of actions to fire when the trigger is true
	'actions' => array(
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'billing_address_street',
				'label' => 'billing_address_street_label', // id of the label to add the required symbol to
				'value' => 'not(equal($parent_name, ""))',
			),
		),
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'billing_address_city',
				'label' => 'billing_address_city_label', // id of the label to add the required symbol to
				'value' => 'not(equal($parent_name, ""))',
			),
		),
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'billing_address_state',
				'label' => 'billing_address_state_label', // id of the label to add the required symbol to
				'value' => 'not(equal($parent_name, ""))',
			),
		),
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'billing_address_postalcode',
				'label' => 'billing_address_postalcode_label', // id of the label to add the required symbol to
				'value' => 'not(equal($parent_name, ""))',
			),
		),
	),
);
