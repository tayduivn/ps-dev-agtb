<?php declare(strict_types=1);
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
 * Class BusinessCenter
 */
class BusinessCenter extends Basic
{
    public $table_name = 'business_centers';
    public $module_name = 'BusinessCenters';
    public $module_dir = 'BusinessCenters';
    public $object_name = 'BusinessCenter';

    // Stored fields
    public $address_street;
    public $address_city;
    public $address_state;
    public $address_country;
    public $address_postalcode;
    public $timezone;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $assigned_user_id;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $team_name;
    public $team_id;

    public $importable = true;

    /**
     * {@inheritDoc}
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
