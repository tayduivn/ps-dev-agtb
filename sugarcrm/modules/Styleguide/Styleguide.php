<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/visibility/OwnerVisibility.php');

/**
 *  Dashboards is used to store dashboard configuration data.
 */
class Styleguide extends Person
{
    public $table_name = 'styleguide';
    public $module_name = 'Styleguide';
    public $module_dir = 'Styleguide';
    public $object_name = 'Styleguide';
    var $id;
    var $name;
    var $date_entered;
    var $date_modified;
    var $modified_user_id;
    var $modified_by_name;
    var $created_by;
    var $created_by_name;
    var $description;
    var $deleted;
    var $created_by_link;
    var $modified_user_link;
    var $activities;
    var $team_id;
    var $team_set_id;
    var $team_count;
    var $team_name;
    var $team_link;
    var $team_count_link;
    var $teams;
    var $assigned_user_id;
    var $assigned_user_name;
    var $assigned_user_link;
    var $salutation;
    var $first_name;
    var $last_name;
    var $full_name;
    var $title;
    var $linkedin;
    var $facebook;
    var $twitter;
    var $googleplus;
    var $department;
    var $do_not_call;
    var $phone_home;
    var $email;
    var $phone_mobile;
    var $phone_work;
    var $phone_other;
    var $phone_fax;
    var $email1;
    var $email2;
    var $invalid_email;
    var $email_opt_out;
    var $primary_address_street;
    var $primary_address_street_2;
    var $primary_address_street_3;
    var $primary_address_city;
    var $primary_address_state;
    var $primary_address_postalcode;
    var $primary_address_country;
    var $alt_address_street;
    var $alt_address_street_2;
    var $alt_address_street_3;
    var $alt_address_city;
    var $alt_address_state;
    var $alt_address_postalcode;
    var $alt_address_country;
    var $assistant;
    var $assistant_phone;
    var $email_addresses_primary;
    var $email_addresses;
    var $picture;


    public $list_price;
    public $currency_id;

    public function __construct()
    {
        parent::__construct();
        $this->addVisibilityStrategy('OwnerVisibility');
    }

    /**
     * This overrides the default retrieve function setting the default to encode to false
     */
    function retrieve($id='-1', $encode=false,$deleted=true)
    {
        return parent::retrieve($id, false, $deleted);
    }

    /**
     * This overrides the default save function setting assigned_user_id
     * @see SugarBean::save()
     */
    function save($check_notify = FALSE)
    {
        $this->assigned_user_id = $GLOBALS['current_user']->id;
        return parent::save($check_notify);
    }
}
