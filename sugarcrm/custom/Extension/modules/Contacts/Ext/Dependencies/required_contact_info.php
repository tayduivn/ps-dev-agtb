<?php

$dependencies['Contacts']['required_contact_info'] = array(
    'hooks' => array("edit"), //Optional, defaults to "all". Valid values are combinations of "all", "edit", "save", "retrieve".
    'trigger' => 'and(equal($phone_work, ""), equal($phone_mobile, ""), isEmailEmpty())', //Optional, the trigger for the dependency. Defaults to 'true'.
    'triggerFields' => array('phone_work', 'phone_mobile', 'email1'),
    'onload' => true, //Optional. If true the trigger is evaluated when the edit view is first loaded rather just when trigger fields are changed. 

    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'phone_work',
                'label' => 'phone_work_label', // id of the label to add the required symbol to
                'value' => 'true',
            ),
        ),
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'phone_mobile',
                'label' => 'phone_mobile_label', // id of the label to add the required symbol to
                'value' => 'true',
            ),
        ),
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'Contacts0emailAddress0',
                'label' => 'email1_label', // id of the label to add the required symbol to
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
                'target' => 'phone_work',
                'label' => 'phone_work_label', // id of the label to add the required symbol to
                'value' => 'false',
            ),
        ),
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'phone_mobile',
                'label' => 'phone_mobile_label', // id of the label to add the required symbol to
                'value' => 'false',
            ),
        ),
        array(
            'name' => 'SetRequired',
            //The parameters passed in will depend on the action type set in 'name'
            'params' => array(
                'target' => 'Contacts0emailAddress0',
                'label' => 'email1_label', // id of the label to add the required symbol to
                'value' => 'false',
            ),
        ),
    ),
);

