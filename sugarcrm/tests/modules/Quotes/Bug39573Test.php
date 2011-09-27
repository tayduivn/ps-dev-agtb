<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Accounts/Account.php';
require_once 'modules/Quotes/Quote.php';
require_once('include/MVC/View/views/view.edit.php');

class Bug39573Test extends Sugar_PHPUnit_Framework_OutputTestCase
{  
    var $quote;
    var $product_bundle;
    var $product_bundle_note;
 
    public function setup()
    {
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->product_bundle = SugarTestProductBundleUtilities::createProductBundle();
        $this->product_bundle_note = SugarTestProductBundleNoteUtilities::createProductBundleNote();
        $this->product_bundle_note->description = $this->product_bundle_note->name.'Descrtipion';
        $this->product_bundle_note->save();        
        
        $this->product_bundle->set_product_bundle_note_relationship(1, $this->product_bundle_note->id, $this->product_bundle->id);
        $this->product_bundle->set_productbundle_quote_relationship($this->quote->id, $this->product_bundle->id);
               		 
    }
    
    public function tearDown()
    {
        $this->product_bundle->clear_product_bundle_note_relationship($this->product_bundle->id);
        $this->product_bundle->clear_productbundle_quote_relationship($this->product_bundle->id);
       
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestProductBundleNoteUtilities::removeAllCreatedProductBundleNotes();        

        unset($this->quote);
        unset($this->product_bundle);
        unset($this->product_bundle_note);
        unset($GLOBALS['current_user']);
    }
	

	public function testDuplicateQuote()
	{
        //set the request parameters and duplicate the quote
        $_REQUEST = $_POST = array(
            'module' => 'Quotes',
            'return_module' => 'Quotes',
            'record' => $this->quote->id,
            'return_id' => $this->quote->id,
            'action' => 'EditView',
            'return_action' => 'DetailView',
            'isDuplicate' => 'true',        
        );
        $GLOBALS['action'] = 'EditView';
        $GLOBALS['module'] = 'Quotes';
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        
        //require view and call display class so that ducplicate functionality is called
        require_once('modules/Quotes/views/view.edit.php');
        $view = new QuotesViewEdit($this->quote);
        $view->init($this->quote);
        $view->preDisplay();
        $view->ev->isDuplicate = TRUE;
        $view->display();
        unset($_REQUEST);
        unset($_POST);
        unset($GLOBALS['action']);
        unset( $GLOBALS['module']);
        unset($GLOBALS['app_list_strings']);
        $this->expectOutputRegex("/".js_escape(br2nl($this->product_bundle_note->description)."/"), 'Unable to find Comment in duplicate quote');
        //$this->assertContains(js_escape(br2nl($this->product_bundle_note->description)), $output, 'Unable to find Commente in duplicate quote');
        
	}
}