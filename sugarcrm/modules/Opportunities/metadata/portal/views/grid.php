<?php
$viewdefs['Opportunities']['portal']['view']['grid'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'name',
                    'label' => 'Name',
                    'default' => true,
                    'enabled' => true,
                ),
                1 =>
                array(
                    'name' => 'amount',
                    'label' => 'Opportunity Amount',
                    'default' => true,
                    'enabled' => true,
                    'type' => 'clickToEdit',
                    'cteclass' => 'cteopp',
                ),
                2 =>
                array(
                    'name' => 'opportunity_type',
                    'label' => 'Opp. Type',
                    'default' => true,
                    'enabled' => true,
                ),
                3 =>
                array(
                    'name' => 'lead_source',
                    'label' => 'Lead Source',
                    'default' => true,
                    'enabled' => true,
                    'type' => 'clickToEdit',
                    'cteclass' => 'ctels',
                    'ctetype' => 'chosen',
                ),
                4 =>
                array(
                    'name' => 'assigned_user_id',
                    'label' => 'Assigned User',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);
