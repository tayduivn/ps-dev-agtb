<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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

require_once ('modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php');

/*
 * This test checks to see if custom elements can be defined in a popupdef and be handled by PopupMetaDataParser.php
 * @ticket 50308
 */
class Bug50308Test extends Sugar_PHPUnit_Framework_TestCase {

    var $customFilePath = 'custom/modules/Users/metadata/popupdefs.php';
    var $customFileDir = 'custom/modules/Users/metadata';
    var $originalPopupMeta = array();
    var $newPopupMeta = array('moduleMain'=>array('one','two'), 'varName'=>array('one','two') , 'orderBy'=>array('one','two'), 'whereClauses'=>array('one','two'), 'searchInputs'=>array('one','two'), 'create'=>array('one','two'));

    public function setUp()
    {
        //back up users popup if it exists
        if(is_file($this->customFilePath)){
            include($this->customFilePath);
            $this->originalPopupMeta = $popupMeta;
            $this->newPopupMeta = $popupMeta;
        }else{
            //lets create the directory if it does not exist
            if(!is_dir($this->customFileDir)){
                sugar_mkdir($this->customFileDir);
            }
        }

        //define and add the new elements
        $this->newPopupMeta['addToReserve'] = array('whereStatement', 'templateMeta');
        $this->newPopupMeta['whereStatement'] = 'select money from yourWallet where deposit = "myPocket"';
        $this->newPopupMeta['templateMeta'] = array('one','two');
        $this->newPopupMeta['disappear'] = 'this element was not defined and should be processed';

    }

    public function tearDown() {

        //remove custom file
        SugarAutoLoader::unlink($this->customFilePath, true);
        //recreate custom file using old data if it was collected
        if(!empty($this->originalPopupMeta)){
            $meta = "<?php\n \$popupMeta = array (\n";
            foreach( $this->originalPopupMeta as $k=>$v){
    			$meta .= "    '$k' => ". var_export_helper ($v) . ",\n";
            }
            $meta .=");\n";

            SugarAutoLoader::put($this->customFilePath, $meta, true);
        }

        unset($this->customFilePath);
        unset($this->customFileDir);
        unset($this->originalPopupMeta);
        unset($this->newPopupMeta);

    }

    /*
     * This method writes out the custom popupdef file to custom users directory, then runs the save function on the  popup metadata parser
     * the tests assert that the custom elements are preserved by the parser
     */
    public function testUsingCustomPopUpElements() {

	//declare the vars global and then include the modules file to make sure they are available during testing
        global $moduleList, $beanList, $beanFiles;
        include('include/modules.php');

        if (empty($GLOBALS['app_list_strings'])){
            $language = $GLOBALS['current_language'];
            $GLOBALS['app_list_strings'] = return_app_list_strings_language($language);
        }
        //write out to file and assert that the file was written, or we shouldn't continue
            $meta = "<?php\n \$popupMeta = array (\n";
            foreach( $this->newPopupMeta as $k=>$v){
    			$meta .= "    '$k' => ". var_export_helper ($v) . ",\n";
            }
            $meta .=");\n";

        $writeResult = SugarAutoLoader::put($this->customFilePath, $meta);
        $this->assertGreaterThan(0,$writeResult, 'there was an error writing custom popup meta to file using this path: '.$this->customFilePath);

        //create new instance of popupmetadata parser
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser(MB_POPUPLIST, 'Users');

        //run save to write out the file using the new array elements.
        $parser->handleSave(false);

        //assert the file still exists
        $this->assertTrue(is_file($this->customFilePath),' PopupMetaDataParser::handleSave() could not write out the file as expected.');

        //include the file again to get the new popup meta array
        include($this->customFilePath);
        $popupKeys = array_keys($popupMeta);
        //assert that one of the new elements is there
        $this->assertContains('whereStatement', $popupKeys,'an element that was defined in addToReserve was not processed and save within PopupMetaDataParser::handleSave()');

        //assert that the element that was written but not defined in 'addToReserve' is no longer there
        $this->assertNotContains('disappear', $popupKeys, 'an element that was added but NOT defined in addToReserve was incorrectly processed and saved within PopupMetaDataParser::handleSave().');
    }
}
