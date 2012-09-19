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

require_once('include/Sugarpdf/sugarpdf_config.php');
require_once('include/tcpdf/tcpdf.php');
require_once('include/SugarCache/SugarCacheFile.php');
require_once('modules/Import/sources/ImportFile.php');
require_once('Zend/Http/Response.php');
require_once('Zend/Http/Response/Stream.php');

class SerializeEvilTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testSugarCacheFile()
    {
        if(file_exists(sugar_cached("testevil.php"))) @unlink(sugar_cached("testevil.php"));
        $obj = 'test';
        try {
            $obj = unserialize('O:14:"SugarCacheFile":3:{s:13:"_cacheChanged";b:1;s:14:"_cacheFileName";s:12:"testevil.php";s:11:"_localStore";b:1;}');
        } catch(Exception $e) {
            $obj = null;
        }
        $this->assertNull($obj);
        unset($obj); // call dtor if object created
        $this->assertFileNotExists(sugar_cached("testevil.php"));
    }

    public function getDestructors()
    {
        return array(
            array("SugarCacheFile"),
            array("SugarTheme"),
            array("tcpdf"),
            array("ImportFile"),
            array("Zend_Http_Response_Stream"),
        );
    }

    /**
     * @dataProvider getDestructors
     */
    public function testUnserializeExcept($name)
    {
        $len = strlen($name);
        $obj = 'test';
        try {
            $obj = unserialize("O:$len:\"$name\":1:{s:4:\"test\";b:1;}");
        } catch(Exception $e) {
            $obj = null;
        }

        $this->assertEmpty($obj);
    }
}
