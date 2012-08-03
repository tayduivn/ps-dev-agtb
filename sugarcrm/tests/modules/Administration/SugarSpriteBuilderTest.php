<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * SugarSpriteBuilderTest
 *
 * This test simply checks that we can run the rebuildSprite function which in turn runs SugarSpriteBuilder
 *
 */
class SugarSpriteBuilderTest extends Sugar_PHPUnit_Framework_TestCase
{

var $useSprites;

public function setUp()
{
    if(!function_exists('imagecreatetruecolor'))
    {
        $this->markTestSkipped('imagecreatetruecolor function not found.  skipping test');
        return;
    }
    if (empty($GLOBALS['sugar_config']['use_sprites']))
    {
        $GLOBALS['sugar_config']['use_sprites'] = null;
    }

    $this->useSprites = $GLOBALS['sugar_config']['use_sprites'];
    $GLOBALS['sugar_config']['use_sprites'] = true;

    if(file_exists('cache/sprites'))
    {
        rmdir_recursive('cache/sprites');
    }
}

public function tearDown()
{
    $GLOBALS['sugar_config']['use_sprites'] = $this->useSprites;
}

public function testSugarSpriteBuilder()
{
    require_once('modules/UpgradeWizard/uw_utils.php');
    rebuildSprites(true);
    $this->assertTrue(file_exists('cache/sprites'), 'Assert that we have built the sprites directory');
    $files = glob('cache/sprites/default/*.png');
    $this->assertTrue(!empty($files), 'Assert that we have created .png sprite images');
}

}
