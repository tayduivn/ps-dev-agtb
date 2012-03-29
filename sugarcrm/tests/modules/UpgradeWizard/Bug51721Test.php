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

/**
 * Bug51721Test.php
 *
 */
require_once('modules/UpgradeWizard/uw_utils.php');
require_once('modules/Administration/UpgradeHistory.php');

class Bug51721Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

private $new_upgrade;
private $new_upgrade2;

public function setUp()
{
    global $sugar_config;
    $sugar_config['cache_dir'] = 'cache/';

    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3'");

    $this->new_upgrade = new UpgradeHistory();
    $this->new_upgrade->filename = 'cache/upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade->md5sum = md5('cache/upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade->type = 'patch';
    $this->new_upgrade->version = '6.4.3';
    $this->new_upgrade->status = "installed";
    $this->new_upgrade->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade->save();

    $this->new_upgrade2 = new UpgradeHistory();
    $this->new_upgrade2->filename = 'cache//upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade2->md5sum = md5('cache//upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade2->type = 'patch';
    $this->new_upgrade2->version = '6.4.3';
    $this->new_upgrade2->status = "installed";
    $this->new_upgrade2->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade2->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade2->save();
}

public function tearDown()
{
    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE id IN ('{$this->new_upgrade->id}', '{$this->new_upgrade2->id}')");
}

public function testRepairUpgradeHistoryTable()
{
    repairUpgradeHistoryTable();
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade2->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
}

}