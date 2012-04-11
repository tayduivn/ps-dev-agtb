<?php
$viewdefs['Leads']['portal']['view']['edit'] = array(
    'templateMeta' => array('maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
        'formId' => 'CaseEditView',
        'formName' => 'CaseEditView',
        'hiddenInputs' => array('module' => 'Cases',
            'returnmodule' => 'Cases',
            'returnaction' => 'DetailView',
            'action' => 'Save',
        )
    ),
    'panels' => array(
        array(
            'label' => 'Details',
            'fields' => array(
                array(
                    'name' => 'salutation',
                    'displayParams' => array(
                        'colspan' => 2
                    )
                ),
                'first_name',
                'last_name',
                'phone_work',
                'phone_mobile',
                'phone_home',
                'do_not_call',
                'email1',
                'email2',
                'email_opt_out',
                '',
                'title',
                'department',
                array(
                    'name' => 'account_name',
                    'displayParams' => array(
                        'colspan' => 2
                    )
                ),
                array(
                    'name' => 'primary_address_street',
                    'displayParams' => array(
                        'colspan' => 2,
                        'size' => 100
                    )
                ),
                'primary_address_city',
                'primary_address_state',
                'primary_address_postalcode',
                'primary_address_country',
            )
        )
    )
);
?>
- 
