<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$viewdefs['base']['layout']['shortcuts'] = array(
    'type' => 'shortcuts',
    'name' => 'shortcuts',
    'components' => array(
        array(
            'view' => array(
                'name' => 'list-headerpane',
                'type' => 'list-headerpane',
                'meta' => array(
                    'fields' => array(
                        array(
                            'name' => 'title',
                            'type' => 'label',
                            'default_value' => 'LBL_KEYBOARD_SHORTCUTS_HELP_TITLE',
                        ),
                    ),
                    'buttons' => array(
                        array(
                            'name' => 'configure_button',
                            'type' => 'button',
                            'label' => ' ',
                            'icon' => 'fa-cog',
                            'tooltip' => 'LBL_DASHLET_CONFIGURE',
                            'events' => array(
                                'click' => 'button:configure_button:click',
                            ),
                        ),
                        array(
                            'name' => 'cancel_button',
                            'type' => 'button',
                            'primary' => true,
                            'label' => 'LBL_CLOSE_BUTTON_LABEL',
                            'events' => array(
                                'click' => 'button:cancel_button:click',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'view' => 'shortcuts-help',
        ),
    ),
);
