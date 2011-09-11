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

require_once('modules/Contacts/Contact.php');
require_once('include/MVC/View/views/view.list.php');

class Bug36989Test extends Sugar_PHPUnit_Framework_TestCase
{
     private $module = "Contacts";
     private $searchFieldsBackup;
     private $customSearchFields;
     private $customSearchdefs;

     public function setUp()
    {
          $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		  require('include/modules.php');
		  $GLOBALS['beanList'] = $beanList;
		  $GLOBALS['beanFiles'] = $beanFiles;

          if(file_exists('custom/modules/Contacts/metadata/SearchFields.php'))
          {
              $this->customSearchFields = file_get_contents('custom/modules/Contacts/metadata/SearchFields.php');
              unlink('custom/modules/Contacts/metadata/SearchFields.php');
          }

          $this->searchFieldsBackup = file_get_contents('modules/Contacts/metadata/SearchFields.php');
          file_put_contents('modules/Contacts/metadata/SearchFields.php', '<?php $searchFields[\'Contacts\'] = array(\'test\' => array());');

     }

    public function tearDown()
    {
         file_put_contents('modules/Contacts/metadata/SearchFields.php', $this->searchFieldsBackup);

         if(!empty($this->customSearchFields))
         {
             file_put_contents('custom/modules/Contacts/metadata/SearchFields.php', $this->customSearchFields);
         }

         SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
    
     function testOverrideSearchFields() {
          $list = new ViewList();
          $list->module = "Contacts";
          $list->seed = new Contact();
          $list->prepareSearchForm();
          $this->assertTrue(isset($list->searchForm->searchFields['test']));
    }
}
?>
