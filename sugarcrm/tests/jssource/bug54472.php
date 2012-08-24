<?php
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Professional End User
* License Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/EULA. By installing or using this file, You have
* unconditionally agreed to the terms and conditions of the License, and You may
* not use this file except in compliance with the License. Under the terms of the
* license, You shall not, among other things: 1) sublicense, resell, rent, lease,
* redistribute, assign or otherwise transfer Your rights to the Software, and 2)
* use the Software for timesharing or service bureau purposes such as hosting the
* Software for commercial gain and/or for the benefit of a third party. Use of
* the Software may be subject to applicable fees and any use of the Software
* without first paying applicable fees is strictly prohibited. You do not have
* the right to remove SugarCRM copyrights from the source code or user interface.
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution. See full license for
* requirements. Your Warranty, Limitations of liability and Indemnity are
* expressly stated in the License. Please refer to the License for the specific
* language governing these rights and limitations under the License.
* Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
* All Rights Reserved.
********************************************************************************/

/*
* This test will confirm that JSGroupings are concatenated using the Extensions Framework
*
*/

require_once('modules/Administration/QuickRepairAndRebuild.php');



class Bug54472Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $beforeArray;
    private $removeJSG_Dir = false;


    public function setUp()
    {
        //lets create the needed directories and js grouping files in the appropriate extensions directory
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, true);
        $GLOBALS['current_user']->is_admin = true;

        //lets retrieve the original jsgroupings file to populate the js_grouping array to compare against later on
        include('jssource/JSGroupings.php');

        //store the grouping value before any changes
        $this->beforeArray = $js_groupings;

        //create supporting files in seperate function
        $this->createSupportingFiles();

        //run repair so the extension files are created and updated
				$rac = new RepairAndClear();
				$rac->repairAndClearAll(array('rebuildExtensions'), array(), false, false);

    }


    /*
     * This function creates supporting directory structure and files to carry out the test
     */
    private function createSupportingFiles(){

        //create the js group directory in the proper extension location if needed
        if(!file_exists("custom/Extension/application/Ext/JSGroupings/")){
            mkdir_recursive("custom/Extension/application/Ext/JSGroupings/", true);
            $this->removeJSG_Dir = true;
        }

        //create the first grouping file and define the first group
        if( $fh = @fopen("custom/Extension/application/Ext/JSGroupings/Jgroup0.php", 'w+') )
        {
        $jsgrpStr = '<?php
$js_groupings [\'testEntrySite\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_test_grp1.js", "include/javascript/cookie.js" => "include/javascript/sugar_test_grp1.js");
';
                        fputs( $fh, $jsgrpStr);
                        fclose( $fh );
        }


        //now create a second custom grouping file
        if( $fhAcc = @fopen("custom/Extension/application/Ext/JSGroupings/Jgroup1.php", 'w+') )
        {
        $jsgrpACCStr = '<?php
$js_groupings [\'testEntryMod\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_testAcc_grp1.js", "include/javascript/quickCompose.js" => "include/javascript/sugar_testAcc_grp1.js");
';
                        fputs( $fhAcc, $jsgrpACCStr);
                        fclose( $fhAcc );
        }


    }

    public function tearDown()
    {

        //remove the 2 grouping files and their directories
        if(file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup0.php')){
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup0.php');
        }
        if(file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup1.php')){
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup1.php');
        }
        if($this->removeJSG_Dir && file_exists("custom/Extension/application/Ext/JSGroupings")) {
            @rmdir("custom/Extension/application/Ext/JSGroupings");
        }

        //unset before array
        unset($this->beforeArray);

        //run repair so the extension files are reset back to original state
        $trac = new RepairAndClear();
        $trac->repairAndClearAll(array('rebuildExtensions'), array(), false, false);

    }

    public function testGetJSGroupingCustomEntries() {

        //include jsgroupings file again, this time it should pick up the 2 new groups from the extensions.
        include('jssource/JSGroupings.php');

        //assert that the array count has increased, this confirms it is grabbing the files correctly
        $this->assertGreaterThan(count($this->beforeArray),count($js_groupings), 'JSGrouping array was not concatenated correctly, the number of elements should have increased');

        //Check for the individual entries to confirm they are being concatenated and not overwritten
        $this->assertArrayHasKey('testEntrySite', $js_groupings,'JSGrouping array was not concatenated correctly, site entry is missing');
        $this->assertArrayHasKey('testEntryMod', $js_groupings,'JSGrouping array was not concatenated correctly, module entry is missing');

    }

}
