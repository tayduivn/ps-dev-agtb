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


require_once ('modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php');

class Bug46325Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $parser;
    public $fields;
    public $accountsFile;
    public $prospectsFile;

    function setUp()
    {
        $this->fields = Array(
            'name' => Array(
                    'width' => '40%',
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'link' => 1,
                    'default' => 1,
                    'name' => 'name',
                ),
        );

        require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
    	$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->accountsFile = 'custom/modules/Accounts/metadata/popupdefs.php';
        $this->prospectsFile = 'custom/modules/Prospects/metadata/popupdefs.php'; // Add in base/views when ready
    }

    function tearDown()
    {
        if (is_file($this->accountsFile))
        {
            unlink($this->accountsFile);
        }
        if (is_file($this->prospectsFile))
        {
            unlink($this->prospectsFile);
        }
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
    }

    /**
     * @outputBuffering enabled
     */
    function testUpdateCustomAccountMetadataPopupdefsSave()
    {
        $this->parser = new PopupMetaDataParser('popuplist', 'Accounts');
        $this->parser->_viewdefs = $this->fields;
        $this->parser->handleSave(false);
        require $this->accountsFile;
        $this->assertEquals('LNK_NEW_ACCOUNT', $popupMeta['create']['createButton']);
        unset($popupMeta);
        unset($this->parser);
    }

    /**
     * @outputBuffering enabled
     */
    function testUpdateCustomProspectsMetadataPopupdefsSave()
    {
        $this->useOutputBuffering = false;
        $this->parser = new PopupMetaDataParser('popuplist', 'Prospects');
        $this->parser->_viewdefs = $this->fields;
        $this->parser->handleSave(false);
        require $this->prospectsFile;
        $this->assertEquals('LNK_NEW_PROSPECT', $popupMeta['create']['createButton']);
        unset($popupMeta);
        unset($this->parser);
    }
}
