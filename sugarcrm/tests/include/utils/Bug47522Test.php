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

 
require_once 'include/utils.php';

/*
 This unit test simulates cleaning the key values in a POST/REQUEST when mbstring.encoding_translation is on.
 When you turn on  mbstring.encoding_translation in php.ini, it's supposed to translate the characters in the POST array during an http request.
 It's not supposed to touch the key names though.  This test is for the code that cleans those key names
  */
class Bug47522Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $orig_ini_encoding_val = '1';
    	public function setUp()
	{
        $this->orig_ini_encoding_val = ini_get('mbstring.encoding_translation');

        //set http translation on
        ini_set('mbstring.encoding_translation',1);

    }

    public function tearDown()
    {
        //set back value of ini setting
        ini_set('mbstring.encoding_translation',$this->orig_ini_encoding_val);

        unset($this->orig_ini_encoding_val);
    }

    public function testEncodedKeyCleaning()
    {

        //assert that encoding is set to true.  ini_get returns a string, so cannot use assertTrue()
        $this->assertSame(ini_get('mbstring.encoding_translation'), '1', 'mbstring encoding translation is turned off, the rest of the test is invalid');

        if(ini_get('mbstring.encoding_translation')==='1'){
            //inject bad string into request
            $key = "'you'shall'not'pass!";
            $val = ' must.. not.. die..';
            $_REQUEST[$key] = $val;

            //assert the key is in the request object
            $this->assertsame($_REQUEST[$key], $val, 'request key was not set, rest of test is invalid');

            //clean the string, it should fail but since encoding translation is on, it should only remove the
            //key from request object
            securexsskey($key,false);

            //assert the key is no longer in request
            $this->assertNotContains($key,$_REQUEST,'Key should not hav passed xss security check, but still exists in request, this is wrong.');

        }



    }
}

