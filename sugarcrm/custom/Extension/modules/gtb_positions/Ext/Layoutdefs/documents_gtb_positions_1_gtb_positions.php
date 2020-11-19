<?php

$layout_defs["gtb_positions"]["subpanel_setup"]['documents_gtb_positions_1'] = array (
    'order' => 100,
    'module' => 'Documents',
    'subpanel_name' => 'default',
    'sort_order' => 'asc',
    'sort_by' => 'id',
    'title_key' => 'LBL_DOCUMENTS_GTB_POSITIONS_1_FROM_DOCUMENTS_TITLE',
    'get_subpanel_data' => 'documents_gtb_positions_1',
    'top_buttons' =>
        array (
            0 =>
                array (
                    'widget_class' => 'SubPanelTopButtonQuickCreate',
                ),
            1 =>
                array (
                    'widget_class' => 'SubPanelTopSelectButton',
                    'mode' => 'MultiSelect',
                ),
        ),
);
