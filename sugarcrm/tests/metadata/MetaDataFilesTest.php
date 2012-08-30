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
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';

class MetaDataFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $fileFullPaths = array(
        'Accountsmobilelistviewbase'   => 'modules/Accounts/clients/mobile/views/list/list.php',
        'Accountsmobilelistviewcustom' => 'custom/modules/Accounts/clients/mobile/views/list/list.php',
        'Bugsportaleditviewworking'    => 'custom/working/modules/Bugs/clients/portal/views/edit/edit.php',
        'Bugsmobilesearchviewbase'     => 'modules/Bugs/clients/mobile/views/search/search.php',
        'Casesportaldetailviewhistory' => 'custom/history/modules/Cases/clients/portal/views/detail/detail.php',
        'Callsbasesearchviewbase'      => 'modules/Calls/clients/base/views/search/search.php',
    );

    public $deployedFileNames = array(
        'Accountslistviewbase' => 'modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportaldetailviewworkingportal' => 'custom/working/modules/Notes/clients/portal/views/detail/detail.php',
        'Quotesadvanced_searchhistory' => 'custom/history/modules/Quotes/metadata/searchdefs.php',
        'Meetingsbasic_searchbase'  => 'modules/Meetings/metadata/searchdefs.php',
        'Bugswireless_advanced_searchbasemobile' => 'modules/Bugs/clients/mobile/views/search/search.php',
    );

    public $undeployedFileNames = array(
        'Accountslistviewbase' => 'custom/modulebuilder/packages/LZWYZ/modules/Accounts/metadata/listviewdefs.php',
        'Leadswirelesseditviewcustommobile' => 'custom/modulebuilder/packages/LZWYZ/modules/Leads/clients/mobile/views/edit/edit.php',
        'Notesportaldetailviewworkingportal' => 'custom/modulebuilder/packages/LZWYZ/modules/Notes/clients/portal/views/detail/detail.php',
        'Quotesadvanced_searchhistory' => 'custom/working/modulebuilder/packages/LZWYZ/modules/Quotes/metadata/searchdefs.php',
    );

    public function setUp() {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    /**
     * @dataProvider MetaDataFileFullPathProvider
     * @param string $module
     * @param string $viewtype
     * @param string $location
     * @param string $client
     * @param string $component
     */
    public function testMetaDataFileFullPath($module, $viewtype, $location, $client, $component) {
        $filepath = MetaDataFiles::getModuleFileName($module, $viewtype, $location, $client, $component);
        $known = $this->fileFullPaths[$module.$client.$viewtype.$component.$location];

        $this->assertEquals($known, $filepath, 'Filepath mismatch: ' . $filepath . ' <-> ' . $known);
    }

    /**
     * @dataProvider DeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $location
     * @param string $client
     */
    public function testDeployedFileName($view, $module, $location, $client) {
        $name = MetaDataFiles::getDeployedFileName($view, $module, $location, $client);
        $known = $this->deployedFileNames[$module.$view.$location.$client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    /**
     * @dataProvider UndeployedFileNameProvider
     * @param string $view
     * @param string $module
     * @param string $package
     * @param string $location
     * @param string $client
     */
    public function testUndeployedFileName($view, $module, $package, $location, $client) {
        $name = MetaDataFiles::getUndeployedFileName($view, $module, $package, $location, $client);
        $known = $this->undeployedFileNames[$module.$view.$location.$client];
        $this->assertEquals($known, $name, 'Filename mismatch: ' . $name . ' <-> ' . $known);
    }

    public function MetaDataFileFullPathProvider() {
        return array(
            array('Accounts', 'list', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Accounts', 'list', MB_CUSTOMMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Bugs', 'edit', MB_WORKINGMETADATALOCATION, MB_PORTAL, 'view'),
            array('Bugs', 'search', MB_BASEMETADATALOCATION, MB_WIRELESS, 'view'),
            array('Cases', 'detail', MB_HISTORYMETADATALOCATION, MB_PORTAL, 'view'),
            array('Calls', 'search', MB_BASEMETADATALOCATION, 'base', 'view'),
        );
    }

    public function DeployedFileNameProvider() {
        return array(
            array(MB_LISTVIEW, 'Accounts', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSEDITVIEW, 'Leads', MB_CUSTOMMETADATALOCATION, MB_WIRELESS),
            array(MB_PORTALDETAILVIEW, 'Notes', MB_WORKINGMETADATALOCATION, MB_PORTAL),
            array(MB_ADVANCEDSEARCH, 'Quotes', MB_HISTORYMETADATALOCATION, ''),
            array(MB_BASICSEARCH, 'Meetings', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSADVANCEDSEARCH, 'Bugs', MB_BASEMETADATALOCATION, MB_WIRELESS),
        );
    }

    public function UndeployedFileNameProvider() {
        return array(
            array(MB_LISTVIEW, 'Accounts', 'LZWYZ', MB_BASEMETADATALOCATION, ''),
            array(MB_WIRELESSEDITVIEW, 'Leads', 'LZWYZ', MB_CUSTOMMETADATALOCATION, MB_WIRELESS),
            array(MB_PORTALDETAILVIEW, 'Notes', 'LZWYZ', MB_WORKINGMETADATALOCATION, MB_PORTAL),
            array(MB_ADVANCEDSEARCH, 'Quotes', 'LZWYZ', MB_HISTORYMETADATALOCATION, ''),
        );
    }
}