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

require_once('tests/rest/RestTestBase.php');

class RestCurrentUserPreferenceTest extends RestTestBase {

    protected static $testPreferences = array(
        'hello' => 'world',
        'test' => 'preference'
    );

    public function setUp()
    {
        parent::setUp();

        // add a test preference to the user
        foreach(self::$testPreferences as $key => $pref) {
            $this->_user->setPreference($key, $pref);
        }

        // save the preferences to the db.
        $this->_user->savePreferencesToDB();
    }
    
    public function tearDown()
    {
        // clean up the preferences
        $this->_user->resetPreferences();

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testGetUserPreferences()
    {
        $reply = $this->_restCall("me/preferences");

        // assert that the reply has our two preferences in it
        $this->assertEquals('world', $reply['reply']['hello']);
        $this->assertEquals('preference', $reply['reply']['test']);
    }

    /**
     * @dataProvider dataProviderGetSpecificPreference
     * @group rest
     *
     * @param $key
     * @param $value
     */
    public function testGetSpecificPreference($key, $value)
    {
        $reply = $this->_restCall('me/preference/' . $key);

        $this->assertEquals($value, $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testUpdatePreferenceReturnsNewKeyValuePair()
    {
        $reply = $this->_restCall('me/preference/hello',  json_encode(array('value' => 'world1')), 'PUT');

        $this->assertEquals(array('hello' => 'world1'), $reply['reply']);
    }

    public function testUpdateMultiplePreferencesReturnsUpdatedKeyValuePair()
    {
        $reply = $this->_restCall('me/preferences',
            json_encode(array('hello' => 'world1', 'test' => 'preference1'))
            , 'PUT');

        $this->assertEquals(array('hello' => 'world1', 'test' => 'preference1'), $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testCreatePreferenceReturnsCreatedKeyValuePair()
    {
        $reply = $this->_restCall('me/preference/create', json_encode(array('value' => 'preference')), 'POST');

        $this->assertEquals(array('create' => 'preference'), $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testDeletePreferenceReturnsDeletedKey()
    {
        $reply = $this->_restCall('me/preference/hello', json_encode(array('value' => 'preference')), 'DELETE');

        $this->assertEquals('hello', $reply['reply']);
    }

    /**
     * @group rest
     */
    public function testGetNonExistantPreferenceReturnsEmptyValue()
    {
        $reply = $this->_restCall('me/preference/this_pref_does_not_exist');

        $this->assertSame("", $reply['reply']);
    }


    public static function dataProviderGetSpecificPreference() {
        $return = array();
        foreach(self::$testPreferences as $key => $pref) {
            $return[] = array($key, $pref);
        }

        return $return;
    }
    
}
