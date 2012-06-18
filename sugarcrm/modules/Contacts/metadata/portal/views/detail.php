<?php
$viewdefs ['Contacts']['portal']['view']['detail'] =
    array(
        'buttons' =>
        array(
            array(
                'name' => 'edit_button',
                'type' => 'button',
                'label' => 'Edit',
                'value' => 'edit',
                'class' => 'edit-profile',
                'primary' => true,
                'events' =>
                array(
                    'click' => 'function(e){ this.app.router.navigate("profile/edit", {trigger:true});}'
                ),
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
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'last_name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'title',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'email',
                        'type' => 'email',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'phone_work',
                        'type' => 'text',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array (
                        'name' => 'primary_address_street',
                        'label' => 'LBL_PRIMARY_ADDRESS',
                        'type' => 'address',
                        'displayParams' =>
                        array (
                            'colspan' => 2
                        ),
                    ),
                ),
            ),
        ),
    );
?>
