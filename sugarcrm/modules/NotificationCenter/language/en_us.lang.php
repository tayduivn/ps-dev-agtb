<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$mod_strings = array(
    'LBL_MODULE_NAME' => 'Notification Center',
    'LBL_CONFIG_TITLE_MODULE_SETTINGS' => 'Notification Center Settings',
    'LBL_CONFIG_TITLE_MODULE_SETTINGS_ADMIN' => 'Notification Center System Settings',
    'LBL_CARRIER_DELIVERY_OPTION_TITLE' => 'Notification Delivery Methods',
    'LBL_CARRIER_DELIVERY_ADMIN_DESC' => 'Enable how your users can receive notifications. ' .
        'The delivery methods selected will determine the subset of options available to users.',
    'LBL_CARRIER_DELIVERY_USER_DESC' => 'Select how you would like to receive notifications.',
    'LBL_CARRIER_DELIVERY_OPTION_HELP' =>
        'Configure notification delivery methods.<br/><br/>Please note, the delivery methods selected will determine '.
        'the subset of options available to you. Enabling/disabling a notification method affects '.
        'all modules utilizing the Notification Center.<br/><br/>' .
        'You can enable or disable a notification method by clicking the corresponding checkbox.<br/><br/>' .
        'Some notification methods (e.g. Email) provide the option to specify multiple delivery addresses.'
        . ' All email addresses provided will receive notification email.',
    'LBL_CARRIER_DELIVERY_OPTION_HELP_ADMIN' =>
        'Configure notification delivery methods.<br/><br/>Please note, the delivery methods selected will determine '.
        'the subset of options available to your users. Enabling/disabling a notification method affects '.
        'all modules utilizing the Notification Center.<br/><br/>' .
        'You can enable or disable a notification method by clicking the corresponding checkbox.<br/><br/>' .
        'Some notification methods (e.g. Email) provide the option to specify multiple delivery addresses.'
        . ' All email addresses provided will receive notification email.',

    'LBL_APPLICATION_EMITTER_TITLE' => 'SugarCRM Application Notifications',
    'LBL_APPLICATION_EMITTER_DESC' => 'As an Administrator get application specific notifications.',
    'LBL_APPLICATION_EMITTER_HELP' =>
        'System administrators can configure notification delivery for system-wide events.<br/><br/>' .
        '<i>{{sendFor}}</i> column represents system events (system failure, non-configured service, etc.).<br/>' .
        'You can configure Notification Center to receive notifications of particular event by clicking checkbox ' .
        'in the corresponding delivery method column.<br/>' .
        'It is also possible to disable all notification methods for a particular event by clicking the leftmost ' .
        'event\'s checkbox.<br/><br/>' .
        'Please note, that some notification methods can be unavailable for an event, ' .
        'because they were not enabled in <i>{{deliverySection}}</i> section.',

    'LBL_BEAN_EMITTER_TITLE' => 'General Notifications',
    'LBL_BEAN_EMITTER_DESC_ADMIN' => 'Chose what types of notifications your users receive. You can also change the delivery methods for each notification. Notifications apply to the users that are assigned to a record, on a team with belonging to a record, or invited to a call or meeting.',
    'LBL_BEAN_EMITTER_DESC_USER' => 'Chose what types of notifications your receive. You can also change the delivery methods for each notification. Notifications apply to the users that are assigned to a record, on a team with belonging to a record, or invited to a call or meeting.',
    'LBL_BEAN_EMITTER_HELP' =>
        'You can configure notification delivery for events that happen on SugarCRM records level.<br/><br/>' .
        '<i>{{sendFor}}</i> column represents record-level events (record creation, update, reassign, etc.).<br/>' .
        'You can configure Notification Center to receive notifications of particular event by clicking checkbox ' .
        'in the corresponding delivery method column.<br/>' .
        'It is also possible to disable all notification methods for some event by clicking the leftmost ' .
        'event\'s checkbox.<br/><br/>' .
        'Please note, that some notification methods can be unavailable for an event, ' .
        'because they were not enabled in <i>{{deliverySection}}</i> section.',

    'LBL_LINK_CARRIER_CONFIGURE' => 'Configure',
    'LBL_LINK_CARRIER_EDIT' => 'Edit',
    'LBL_SEND_NOTIFICATION_FOR' => 'Send Notification for:',
    'LBL_ASK_ADMIN_TO_ENABLE' => 'Not enabled. Ask your admin to enable.',

    'LBL_RESET_SETTINGS_ALL_CONFIRMATION' => 'Are you sure you want to reset all your Notification Center settings to the system default?',
    'LBL_RESET_SETTINGS_EMITTER_CONFIRMATION' => 'Are you sure you want to reset your % settings to the system default?',
    'LBL_RESET_SETTINGS_SUCCESS' => 'Settings successfully have been reset to the system default.',
    'LBL_SELECT_SEND_ADDRESS' => 'Select address',
);

$mod_list_strings = array(
    'emitter_types' => array(
        'application' => 'Application',
        'module'      => 'Module',
        'bean'        => 'Bean',
    )
);
