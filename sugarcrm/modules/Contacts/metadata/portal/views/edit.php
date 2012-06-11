<?php
$viewdefs ['Contacts']['portal']['view']['edit'] =
    array(
        'buttons' =>
        array(
            array(
                'name' => 'save_button',
                'type' => 'button',
                'label' => 'Save',
                'value' => 'save',
                'class' => 'save-profile',
                'primary' => true,
            ),
            array(
                'name' => 'cancel_button',
                'type' => 'button',
                'label' => 'Cancel',
                'value' => 'cancel',
                'events' =>
                array(
                    'click' => 'function(){ window.history.back(); }',
                ),
                'primary' => false,
            ),
        ),
        'templateMeta' =>
        array(
            'maxColumns' => '2',
            'widths' =>
            array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'formId' => 'ContactsEditView',
            'formName' => 'ContactsEditView',
            'hiddenInputs' =>
            array(
                'module' => 'Contacts',
                'returnmodule' => 'Contacts',
                'returnaction' => 'DetailView',
                'action' => 'Save',
            ),
            'hiddenFields' =>
            array(
                array(
                    'name' => 'portal_viewable',
                    'operator' => '=',
                    'value' => '1',
                ),
            ),
            'useTabs' => false,
        ),
        'panels' =>
        array(
            array(
                'label' => 'default',
                'fields' =>
                array(
                    array(
                        'name' => 'first_name',
                        'label' => 'LBL_FIRST_NAME',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'last_name',
                        'label' => 'LBL_LAST_NAME',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'title',
                        'label' => 'LBL_TITLE',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'LBL_EMAIL_ADDRESS',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'phone_work',
                        'label' => 'LBL_PHONE_WORK',
                        'type' => 'text',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_street',
                        'label' => 'LBL_PRIMARY_ADDRESS_STREET',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_city',
                        'label' => 'LBL_PRIMARY_ADDRESS_CITY',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_state',
                        'label' => 'LBL_PRIMARY_ADDRESS_STATE',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_postalcode',
                        'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'primary_address_country',
                        'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                ),
            ),
        ),
    );
?>
                
