<?php

/* * *******************************************************************************
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once('modules/Users/User.php');
/**
 * @ticket 51371
 */
class Bug51371Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getHashes()
    {
        return array(
            // plain MD5
            array("my passw0rd", "0db22d09a263d458c79581aefcbdb300"),
            // extended crypt
            array("my passw0rd", "_.012saltIO.319ikKPU"),
            // md5 crypt
            array("my passw0rd", '$1$F0l3iEs7$sT3th960AcuSzp9kiSmxh/'),
            // blowfish
            array("my passw0rd", '$2a$07$usesomesillystringforeETvnK0/TgBVIVHViQjGDve4qlnRzeWS'),
            // sha-256
            array("my passw0rd", '$5$rounds=5000$usesomesillystri$aKwd34p0LSvMZdW1LolZOPCCsx1mYdTynQn9ZrWrO87'),
            // whatever User has
            array("my passw0rd", User::getPasswordHash("my passw0rd")),
        );
    }

    /**
     * @dataProvider getHashes
     */
    public function testUserhash($password, $user_hash)
    {
        $this->assertTrue(User::checkPassword($password, $user_hash));
    }
}