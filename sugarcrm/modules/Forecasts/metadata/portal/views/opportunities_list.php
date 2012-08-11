<?php
$viewdefs['Forecasts']['portal']['view']['opportunities_list'] = array(
    'listNav' =>
    array(
        0 =>
        array(
            'name' => 'show_more_button_back',
            'type' => 'navelement',
            'icon' => 'icon-plus',
            'label' => ' ',
            'route' =>
            array(
                'action' => 'create',
                'module' => 'Opportunities',
            ),
        ),
        1 =>
        array(
            'name' => 'show_more_button_back',
            'type' => 'navelement',
            'icon' => 'icon-chevron-left',
            'label' => ' ',
            'events' =>
            array(
                'click' => 'function(){ var self = this;this.context.state.collection.paginate({page:-1, success:function(){console.log("in paginate success");}});}',
            ),
        ),
        2 =>
        array(
            'name' => 'show_more_button_forward',
            'type' => 'navelement',
            'icon' => 'icon-chevron-right',
            'label' => ' ',
            'events' =>
            array(
                'click' => 'function(){ var self = this;console.log(this); this.context.state.collection.paginate({success:function(){console.log("in paginate success");}});}',
            ),
        ),
    ),
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'id',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);
