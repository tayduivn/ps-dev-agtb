<?php
$viewdefs['Opportunities']['portal']['view']['detail'] = array(
    'buttons' =>
    array(
        0 =>
        array(
            'name' => 'edit_button',
            'type' => 'button',
            'label' => 'Edit',
            'value' => 'edit',
            'route' =>
            array(
                'action' => 'edit',
            ),
            'primary' => true,
        ),
    ),
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'Details',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'name',
                    'label' => 'Name',
                ),
                1 =>
                array(
                    'name' => 'amount',
                    'label' => 'Opportunity Amount',
                ),
                2 =>
                array(
                    'name' => 'opportunity_type',
                    'label' => 'Opp. Type',
                ),
                3 =>
                array(
                    'name' => 'lead_source',
                    'label' => 'Lead Source',
                ),
                4 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'Modifed Date',
                ),
            ),
        ),
    ),
);
