<?php

$beanList = array();
$beanFiles = array();
require('include/modules.php');
$GLOBALS['beanList'] = $beanList;
$GLOBALS['beanFiles'] = $beanFiles;
require_once 'modules/Quotes/Quote.php';

class SugarTestQuoteUtilities
{
    private static $_createdQuotes = array();

    private function __construct() {}

    public static function createQuote($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarQuote';
    	$quote = new Quote();
        $quote->name = $name . $time;
        $quote->quote_stage = 'Draft';
        $quote->date_quote_expected_closed = $GLOBALS['timedate']->to_display_date(gmdate('Y-m-d'));
        if(!empty($id))
        {
            $quote->new_with_id = true;
            $quote->id = $id;
        }
        $quote->save();
        self::$_createdQuotes[] = $quote;
        return $quote;
    }

    public static function setCreatedQuote($quote_ids) {
    	foreach($quote_ids as $quote_id) {
    		$quote = new Quote();
    		$quote->id = $quote_id;
        	self::$_createdQuotes[] = $quote;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedQuotes() 
    {
        $quote_ids = self::getCreatedQuoteIds();
        $GLOBALS['db']->query('DELETE FROM quotes WHERE id IN (\'' . implode("', '", $quote_ids) . '\')');
    }
        
    public static function getCreatedQuoteIds() 
    {
        $quote_ids = array();
        foreach (self::$_createdQuotes as $quote) {
            $quote_ids[] = $quote->id;
        }
        return $quote_ids;
    }
}
?>