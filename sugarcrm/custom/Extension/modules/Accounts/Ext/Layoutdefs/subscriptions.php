<?php

$layout_defs['Accounts']['subpanel_setup']['subscriptions'] =  array(
            'order' => 73,
            'module' => 'Subscriptions',
            'sort_order' => 'desc',
            'sort_by' => 'expiration_date',
            'get_subpanel_data' => 'subscriptions',
            'add_subpanel_data' => 'subscription_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_SUBSCRIPTIONS_SUBPANEL_TITLE',
            'top_buttons' => array(
                //array('widget_class' => 'SubPanelTopSelectButton'),
            ),
        );

