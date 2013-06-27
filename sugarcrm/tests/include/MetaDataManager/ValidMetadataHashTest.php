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

require_once 'include/MetaDataManager/MetaDataManager.php';
require_once 'tests/SugarTestACLUtilities.php';

/**
 * Testing valid caches to prevent error 412 loops.
 */
class ValidMetadataHashTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $path = "cache/api/metadata/hashes.php";
    protected $baseHash = "1234asdf";
    protected $portalHash = "zzz123";

    public function setUp()
    {
        $hashes = array (
            'meta_hash_base'  => $this->baseHash,
            'meta_hash_portal_base'  => $this->portalHash,
        );
        sugar_mkdir(dirname($this->path), null, true);
        write_array_to_file("hashes", $hashes, $this->path);
    }

    public function tearDown()
    {
        unlink($this->path);
    }

    public function testHashValid()
    {
        $mm = new MetaDataManager($GLOBALS['current_user']);
        $this->assertTrue(
            $mm->isMetadataHashValid($this->baseHash, "base"),
            "Base metadata hash shoudl have been valid but was not"
        );
        $this->assertFalse(
            $mm->isMetadataHashValid("invalid Hash", "base"),
            "Base metadata hash should have been invalid, but was valid"
        );
        $this->assertTrue(
            $mm->isMetadataHashValid($this->portalHash, "portal"),
            "Portal metadata hash shoudl have been valid but was not"
        );
        $this->assertFalse(
            $mm->isMetadataHashValid($this->baseHash, "portal"),
            "Portal metadata hash should have been invalid, but was valid"
        );
    }
}
