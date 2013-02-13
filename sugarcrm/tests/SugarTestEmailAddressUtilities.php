<?php
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

require_once 'modules/EmailAddresses/EmailAddress.php';

class SugarTestEmailAddressUtilities
{
    private static $createdAddresses = array();

    private function __construct() {}

    public static function createEmailAddress($address = null)
    {
        if (null === $address)
        {
            $address = 'address-' . mt_rand() . '@example.com';
        }

        $email_address = new EmailAddress();
        $email_address->email_address = $address;
        $email_address->save();

        self::$createdAddresses[] = $email_address;
        return $email_address;
    }

    /**
     * Add specified email address to the person
     *
     * @param Person $person
     * @param string|EmailAddress $address
     * @param array $additional_values
     * @return boolean|EmailAddress
     * @throws InvalidArgumentException
     */
    public static function addAddressToPerson(Person $person, $address, array $additional_values = array())
    {
        if (is_string($address))
        {
            $address = self::createEmailAddress($address);
        }

        if (!$address instanceof EmailAddress)
        {
            throw new InvalidArgumentException(
                'Address must be a string or an instance of EmailAddress, '
                    . gettype($address) . ' given'
            );
        }

        if (!$person->load_relationship('email_addresses'))
        {
            return false;
        }

        // create relation between user and email address
        $person->email_addresses->add(array($address), $additional_values);
        $GLOBALS['db']->commit();
        return $address;
    }

    public static function removeAllCreatedAddresses()
    {
        $ids = self::getCreatedEmailAddressIds();
        if (count($ids) > 0)
        {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $ids) . '\')');
        }
        self::$createdAddresses = array();
    }

    public static function getCreatedEmailAddressIds()
    {
        $ids = array();
        foreach (self::$createdAddresses as $address)
        {
            $ids[] = $address->id;
        }
        return $ids;
    }
}
