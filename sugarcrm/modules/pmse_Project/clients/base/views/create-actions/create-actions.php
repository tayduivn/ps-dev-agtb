<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
$module_name = 'pmse_Project';
$viewdefs[$module_name ]['base']['view']['create-actions'] = array(
    'template' => 'record',
    'buttons' => array(
        array(
            'name'      => 'cancel_button',
            'type'      => 'button',
            'label'     => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'      => 'restore_button',
            'type'      => 'button',
            'label'     => 'LBL_RESTORE',
            'css_class' => 'btn-invisible btn-link',
            'showOn'    => 'select',
        ),
        array(
            'type'    => 'actiondropdown',
            'name'    => 'main_dropdown',
            'primary' => true,
            'switch_on_click' => true,
            'buttons' => array(
                array(
                    'type'   => 'rowaction',
                    'name'   => 'save_open_design',
                    'label'  => 'LBL_PMSE_SAVE_DESIGN_BUTTON_LABEL',
                    'showOn' => 'create',
                ),
                array(
                    'type'  => 'rowaction',
                    'name'  => 'save_button',
                    'label' => 'LBL_SAVE_BUTTON_LABEL',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
