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

/**
 * System-wide and User-wide configurations of NotificationCenter.
 *
 * Class NotificationCenterSubscription
 */
class NotificationCenterSubscription extends Basic
{
    /**
     * {@inheritdoc}
     */
    public $id;

    /**
     * Type of emitter - application, bean, module (enum field because we need correct order on this field).
     * @var 'application'|'bean'|'module'
     */
    public $type;

    /**
     * User GUIDs, can be null, null means global config.
     *
     * @var GUIDs|null
     */
    public $user_id;

    /**
     * Name of module from emitter.
     *
     * @var string
     */
    public $emitter_module_name;

    /**
     * Name of event from the emitter.
     *
     * @var string
     */
    public $event_name;

    /**
     * Name of subscription filter (AssignedToMe, Team, Application, can be added more).
     *
     * @var string
     */
    public $filter_name;

    /**
     * Name of carrier.
     *
     * @var string
     */
    public $carrier_name;

    /**
     * Recipient value for carrier.
     *
     * @var string
     */
    public $carrier_option;

    /**
     * {@inheritdoc}
     */
    public $date_entered;

    /**
     * {@inheritdoc}
     */
    public $date_modified;

    /**
     * {@inheritdoc}
     */
    public $modified_user_id;

    /**
     * {@inheritdoc}
     */
    public $modified_by_name;

    /**
     * {@inheritdoc}
     */
    public $created_by;

    /**
     * {@inheritdoc}
     */
    public $created_by_name;

    /**
     * {@inheritdoc}
     */
    public $created_by_link;

    /**
     * {@inheritdoc}
     */
    public $modified_user_link;

    /**
     * {@inheritdoc}
     */
    public $deleted;

    /**
     * {@inheritdoc}
     */
    public $module_dir = 'NotificationCenter';

    /**
     * {@inheritdoc}
     */
    public $module_name = 'NotificationCenterSubscriptions';

    /**
     * {@inheritdoc}
     */
    public $object_name = "NotificationCenterSubscription";

    /**
     * {@inheritdoc}
     */
    public $table_name = 'notification_subscription';
}
