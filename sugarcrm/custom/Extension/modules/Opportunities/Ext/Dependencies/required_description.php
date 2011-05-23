<?php

/*

$dependencies['Opportunities']['required_description'] = array(
//    'hooks' => array("edit"), //Optional, defaults to "all". Valid values are combinations of "all", "edit", "save", "retrieve".
    'trigger' => 'contains($name, "bingbong")', //Optional, the trigger for the dependency. Defaults to 'true'.
//    'triggerFields' => array('name'), //Optional, defaults to the fields in the trigger. Either trigger, or trigger fields or both should be set. 
    'onload' => true, //Optional. If true the trigger is evaluated when the edit view is first loaded rather just when trigger fields are changed. 

    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'description',
                'label' => 'description_label', // id of the label to add the required symbol to
                //'value' => 'equal($opportunity_type, "New Business")', //Set required if the type is new
                'value' => 'true',
            ),
        ),
    ),
    //Actions fire if the trigger is false. Optional.
    'notActions' => array(
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'description',
                'label' => 'description_label', // id of the label to add the required symbol to
                'value' => 'false', // set now required for short name
            ),
        ),
    ),
);

*/
