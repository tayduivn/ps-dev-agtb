<?php

$dependencies['Opportunities']['required_comp_buying_behav_c'] = array(
	'trigger' => 'equal($sales_stage, "01")', //Optional, the trigger for the dependency. Defaults to 'true'.
	'onload' => true, //Optional. If true the trigger is evaluated when the edit view is first loaded rather just when trigger fields are changed.

	//Actions is a list of actions to fire when the trigger is true
	'actions' => array(
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'buying_behavior_c',
				'label' => 'buying_behavior_c_label', // id of the label to add the required symbol to
				'value' => 'false',
			),
		),
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'competitor_c',
				'label' => 'competitor_c_label', // id of the label to add the required symbol to
				'value' => 'false',
			),
		),
	),
	//Actions fire if the trigger is false. Optional.
	'notActions' => array(
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'buying_behavior_c',
				'label' => 'buying_behavior_c_label', // id of the label to add the required symbol to
				'value' => 'true',
			),
		),
		array(
			'name' => 'SetRequired',
			//The parameters passed in will depend on the action type set in 'name'
			'params' => array(
				'target' => 'competitor_c',
				'label' => 'competitor_c_label', // id of the label to add the required symbol to
				'value' => 'true',
			),
		),
	),
);
