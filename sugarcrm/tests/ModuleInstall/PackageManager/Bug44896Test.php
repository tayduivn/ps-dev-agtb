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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once 'ModuleInstall/PackageManager/PackageManager.php';


class Bug44896Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!is_dir(dirname(Bug44896PackageManger::$location))) {
            sugar_mkdir(dirname(Bug44896PackageManger::$location));
        }
        if (!is_dir(Bug44896PackageManger::$location))
        {
            sugar_mkdir(Bug44896PackageManger::$location);
        }

        $manage = new Bug44896PackageManger();
        $manage->createTempModule();
    }

    public function tearDown()
    {
        if (is_dir(Bug44896PackageManger::$location)) {
            rmdir_recursive(dirname(Bug44896PackageManger::$location));
        }
    }

    public function testCheckedArrayKey()
    {
        $package = new PackageManager();
        $returnJson = $package->getPackagesInStaging('module');
        foreach ($returnJson as $module) {
            $this->assertArrayHasKey('unFile', $module, 'Key "unFile" is missing in return array');
        }
    }

}

class Bug44896PackageManger
{
	static $manifest_location = "upload://upgrades/module/Bug44896-manifest.php";
    static $zip_location = "upload://upgrades/module/Bug44896.zip";
    static $location = "upload://upgrades/module/";

	public function __construct()
    {
	   $this->manifest_content = <<<EOQ
<?php
\$manifest = array (
         'acceptable_sugar_versions' =>
          array (
            '6.4.0'
          ),
          'acceptable_sugar_flavors' =>
          array(
            'ENT'
          ),
          'readme'=>'',
          'key'=>'tf1',
          'author' => '',
          'description' => '',
          'icon' => '',
          'is_uninstallable' => false,
          'name' => 'Bug44896',
          'published_date' => '2010-10-20 22:10:01',
          'type' => 'module',
          'version' => '1287612601',
          'remove_tables' => 'prompt',
          );
\$installdefs = array (
  'id' => 'asdfqq',
  'copy' =>
  array (
     0 => array (
      'from' => '<basepath>/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
      'to' => 'custom/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
    ),
  ),
);

EOQ;
	}

	public function createTempModule()
    {
	   if (!is_file(self::$manifest_location))
       {
            file_put_contents(self::$manifest_location, $this->manifest_content);
            zip_files_list(self::$zip_location, array(self::$manifest_location));
       }
	}
}