<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once 'include/DashletContainer/Containers/DCAbstract.php';
require_once 'include/DashletContainer/Containers/DCMenu.php';


/**
 * Bug48092Test
 *
 * This test simulates enabling the Lotus Live connectors and checking the HTML links that are created in DCMenu.  We use
 * a mock object for DCMenu since the function getDynamicMenuItem is protected.
 */
class Bug48092Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $useSprites;

    public function setUp()
    {
        $this->useSprites = $GLOBALS['sugar_config']['use_sprites'];
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config']['use_sprites'] = $this->useSprites;
    }

    /**
     * providerLotusLiveLinks
     *
     */
    public function providerLotusLiveLinks()
    {
        $links = array(
            array(
                array(
                        'module' => 'Meetings',
                        'label' => 'View Upcoming LotusLive� Meetings',
                        'action' => "DCMenu.hostMeetingUrl=''; DCMenu.loadView('Upcoming LotusLive� Meetings','index.php?module=Meetings&action=listbytype&type=LotusLive',undefined,undefined,undefined,'');",
                        'icon' => 'icon_LotusMeetings_bar_32.png',
                ),
            ),
            array(
                array(
                        'module' => 'Documents',
                        'label' => 'View LotusLive� Files',
                        'action' => "DCMenu.loadView('LotusLive� Files','index.php?module=Documents&action=extdoc&type=LotusLive');",
                        'icon' => 'icon_LotusDocuments_bar_32.png',
                ),
            ),
        );

        return $links;
    }

    /**
     * testLotusLiveLinksWithSprites
     *
     * This function tests the getDynamicMenuItem method of DCMenu when given a set of LotusLive links
     *
     * @dataProvider providerLotusLiveLinks
     *
     */
    public function testLotusLiveLinksWithSprites($def)
    {
        if(!function_exists('imagecreatetruecolor'))
        {
            $this->markTestSkipped('Skipping test. Test environment does not have GD library extension support');
        }

        $mock = new DCMenuMock2();
        $GLOBALS['sugar_config']['use_sprites'] = true;
        $html = $mock->getDynamicMenuItem($def);
        $this->assertRegExp('/\<span\s+?.*?class[^\>]+?\>/', $html['image'], 'Assert that with sprites on we get link with span tag');
    }


    /**
     * testLotusLiveLinksWithoutSprites
     *
     * This function tests the getDynamicMenuItem method of DCMenu when given a set of LotusLive links
     *
     * @dataProvider providerLotusLiveLinks
     *
     */
    public function testLotusLiveLinksWithoutSprites($def)
    {
        $mock = new DCMenuMock2();
        $GLOBALS['sugar_config']['use_sprites'] = false;
        $html = $mock->getDynamicMenuItem($def);
        $this->assertRegExp('/\<img\s+?/', $html['image'], 'Assert that with sprites off we get link with img tag');
    }

}

/**
 * DCMenuMock2
 *
 * Mock object for DCMenu to override protect access of getDynamicMenuItem method
 */
class DCMenuMock2 extends DCMenu
{

    public function getDynamicMenuItem($def)
    {
        return parent::getDynamicMenuItem($def);
    }

}