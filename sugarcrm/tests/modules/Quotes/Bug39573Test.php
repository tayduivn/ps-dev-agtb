<?php

require_once 'modules/Accounts/Account.php';
require_once 'modules/Quotes/Quote.php';

class Bug39573Test extends Sugar_PHPUnit_Framework_TestCase
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

        ob_start();
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
        $output = ob_get_contents();
        ob_end_clean();
        
        $this->assertContains(js_escape(br2nl($this->product_bundle_note->description)), $output, 'Unable to find Commente in duplicate quote');
        
	}
}