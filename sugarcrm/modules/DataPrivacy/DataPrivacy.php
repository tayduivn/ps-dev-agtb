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

/**
 *  Class for data privacy.
 */
class DataPrivacy extends Issue
{
    public $table_name = 'data_privacy';
    public $module_name = 'DataPrivacy';
    public $module_dir = 'DataPrivacy';
    public $object_name = 'DataPrivacy';

    /**
     * @var string
     */
    public $fields_to_erase;

    /**
     * {@inheritDoc}
     * @see SugarBean::bean_implements()
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     * @see SugarBean::save()
     */
    public function save($check_notify = false)
    {
        if (!isset($this->system_id) || empty($this->system_id)) {
            $admin = Administration::getSettings();
            $system_id = $admin->settings['system_system_id'];
            if (!isset($system_id)) {
                $system_id = 1;
            }
            $this->system_id = $system_id;
        }
        return parent::save($check_notify);
    }
}
