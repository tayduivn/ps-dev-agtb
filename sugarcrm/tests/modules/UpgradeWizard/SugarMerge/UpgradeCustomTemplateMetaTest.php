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
require_once 'include/dir_inc.php';

class UpgradeCustomTemplateMetaTest extends Sugar_PHPUnit_Framework_TestCase
{

    var $merge;

    function setUp()
    {
        SugarTestMergeUtilities::setupFiles(array('Calls', 'Meetings', 'Notes'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
    }


    function tearDown()
    {
        SugarTestMergeUtilities::teardownFiles();
    }

    /**
     * @group SugarMerge
     */
    function testMergeCallsEditviewdefsFor611()
    {
        require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
        $this->merge = new EditViewMerge();
        $this->merge->merge('Calls', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Calls/metadata/editviewdefs.php', 'modules/Calls/metadata/editviewdefs.php', 'custom/modules/Calls/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Calls/metadata/editviewdefs.php');

        $this->assertNotContains('forms[0]', $viewdefs['Calls']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "forms[0] did not get replaced");
    }

    /**
     * @group SugarMerge
     */
    function testMergeMeetingsEditviewdefsFor611()
    {
        require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
        $this->merge = new EditViewMerge();
        $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Meetings/metadata/editviewdefs.php', 'modules/Meetings/metadata/editviewdefs.php', 'custom/modules/Meetings/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Meetings/metadata/editviewdefs.php');

        $this->assertNotContains('this.form.', $viewdefs['Meetings']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "this.form did not get replaced");
    }


    /**
     * Custom button definitions should not be kept during upgrade
     * @group SugarMerge
     */
    function testMergeCustomButtonsAndStudioChanges()
    {
        require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
        $this->merge = new EditViewMerge();
        $this->merge->merge('Notes', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php', 'modules/Notes/metadata/editviewdefs.php', 'custom/modules/Notes/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Notes/metadata/editviewdefs.php');

        //Assert that custom Buttons are not kept
        $this->assertArrayNotHasKey('buttons', $viewdefs['Notes']['EditView']['templateMeta']['form'], "Buttons array picked up from custom file");

        //Assert that studio possible changes are retained
        $this->assertArrayHasKey('useTabs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('tabDefs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('syncDetailEditViews', $viewdefs['Notes']['EditView']['templateMeta']);

    }

}