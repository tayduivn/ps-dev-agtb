<?php

$dependencies['Opportunities']['required_reason_won_c'] = array(
    'trigger' => 'equal($sales_stage, "05")', //Optional, the trigger for the dependency. Defaults to 'true'.
    'onload' => true, //Optional. If true the trigger is evaluated when the edit view is first loaded rather just when trigger fields are changed.

    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'reason_won_c',
                'label' => 'reason_won_c_label', // id of the label to add the required symbol to
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
                'target' => 'reason_won_c',
                'label' => 'reason_won_c_label', // id of the label to add the required symbol to
                'value' => 'false',
            ),
        ),
    ),
);
