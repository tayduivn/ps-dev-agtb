<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarTestSugarEmailAddressUtilities
{
    private static $_createdEmailAddresses = array();

    private static $_createdContact = null;

    private function __construct() {} // not an instantiated class.

    /**
     * creates a Parent Bean to hang Emails from
     * @param $time
     * @return Contact|null
     */
    private function _createContact($time)
    {
        if (self::$_createdContact === null)
        {
            $name = 'SugarEmailAddressContact';
            $lname = 'LastName';
            $contact = new Contact();
            $contact->first_name = $name . $time;
            $contact->last_name = 'LastName';
            $contact->save();

            $GLOBALS['db']->commit();
            self::$_createdContact = $contact;
        }

        return self::$_createdContact;
    }

    /**
     * @param $contact
     * @param $time
     * @param $id
     * @param $override
     * @return SugarEmailAddress
     */
    private static function _createEmailAddress($contact,$time,$id,$override)
    {
        $params['email_address'] = 'semailaddress@'. $time. 'sugar.com';
        $params['primary'] = true;
        $params['reply_to'] = false;
        $params['invalid'] = false;
        $params['opt_out'] = false;
        foreach($override as $key => $value) {
            $params[$key] = $value;
        }


        $contact->emailAddress->addAddress($params['email_address'], $params['primary'], $params['reply_to'],
                                           $params['invalid'], $params['opt_out'], $id);
        $contact->emailAddress->save($contact->id, $contact->module_dir);
        self::$_createdEmailAddresses[] = $contact->emailAddress;
        return $contact->emailAddress;
    }

    /**
     * Create a SugarEmailAddress
     * - This version doesn't bother attaching a SugarEmailAddress to a parent bean.
     * - As such, save() doesn't work on the email addresses.
     * @access public
     * @param string $address - custom address to pass, otherwise pass null.
     * @param string $id - pass parameter to set a specific uuid for the SugarEmailAddress
     * @param array $override - pass key => value array of parameters to override the defaults
     * @return SugarEmailAddress
     */
    public static function createEmailAddress($address=null,$id = '', $override = array())
    {
        $time = mt_rand();
        $contact = self::_createContact($time);
        if (!empty($address)) {
            $override['email_address'] = $address;
        }
        $address = self::_createEmailAddress($contact, $time, $id, $override);
        return $address;
    }

    /**
     * Clean up after use
     * @access public
     */
    public static function removeAllCreatedEmailAddresses()
    {
        $address_ids = self::getCreatedEmailAddressIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $address_ids) . '\')');
    }

    /**
     * clean up the related bean and the relationship table
     * @access public
     */
    public static function removeCreatedContactAndRelationships(){
        if (self::$_createdContact === null) {
            return;
        }

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".self::$_createdContact->id."'");
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id =\'' . self::$_createdContact->id . '\'');
        self::$_createdContact = null;
    }


    /**
     * Retrieve a list of all ids of SugarEmailAddresses created through this class
     * @access public
     * @return array ids of all SugarEmailAddresses created
     */
    public static function getCreatedEmailAddressIds()
    {
        $address_ids = array();
        foreach (self::$_createdEmailAddresses as $address) {
            $address_ids[] = $address->id;
        }
        return $address_ids;
    }

    /**
     * In case we don't have our bean's UUID - get it via address
     * @param $address - email address
     * @return string|null UUID of bean for email address.
     */
    public static function fetchEmailIdByAddress($address) {
        $email_caps = strtoupper(trim($address));
        $rs = $GLOBALS['db']->query("SELECT id from email_addresses where email_address_caps='$email_caps'");
        $a = $GLOBALS['db']->fetchByAssoc($rs);

        if (!empty($a['id'])) {
            return $a['id'];
        }
        else {
            return null;
        }
    }

    /**
     * get our parent bean
     * @return Contact|null
     */
    public static function getContact() {
        return self::_createContact(mt_rand());
    }

}
