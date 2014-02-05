<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once('modules/Versions/Version.php');
require_once('modules/Versions/CheckVersions.php');

/*
* RS-17: Prepare Versions Module
*/

class RS17Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test assert that we don't get any db error in the function
     */
    public function testGetInvalidVersions()
    {
    	$invalidVersions = get_invalid_versions();
    	$this->assertInternalType('array', $invalidVersions);
    }

    public function dataProviderDefaultVersions()
    {
        static $versions;
        if (!$versions) {
            include 'modules/Versions/DefaultVersions.php';
            $versions = $default_versions;
        }
        return $versions;
    }

    /**
     * Test assert that we don't get any db error in the method
     * @dataProvider dataProviderDefaultVersions
     * @covers Versions::mark_upgraded
     */
    public function testMarkUpdated($name, $db_version, $file_version)
    {
        $version = new Version();
        $this->assertEmpty($version->mark_upgraded($name, $db_version, $file_version));
    }

}
