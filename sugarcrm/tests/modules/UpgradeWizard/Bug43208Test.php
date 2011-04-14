<?php
/**
 * Bug43208Test
 * 
 * This test checks to see if the function repairTableDictionaryExtFile in uw_utils.php is working correctly.
 * There were some scenarios in 6.0.x whereby the files loaded in the extension tabledictionary.ext.php file 
 * did not exist.  This would cause warnings to appear during the upgrade.  As a result, we added the 
 * repairTableDictionaryExtFile function to scan the contents of tabledictionary.ext.php and then remove entries
 * where the file does not exist.
 */
class Bug43208Test extends Sugar_PHPUnit_Framework_TestCase 
{

var $tableDictionaryExtFile = 'custom/Extension/application/Ext/TableDictionary/tabledictionary.ext.php';	
	
function setUp() {
    //Create the language files with bad name
    if(file_exists($this->tableDictionaryExtFile)) {
       copy($this->tableDictionaryExtFile, $this->tableDictionaryExtFile . '.backup');
       unlink($this->tableDictionaryExtFile);
    } else if(!file_exists('custom/Extension/application/Ext/TableDictionary')){
       mkdir_recursive('custom/Extension/application/Ext/TableDictionary');
    }
	
    if( $fh = @fopen($this->tableDictionaryExtFile, 'w+') )
    {
$string = <<<EOQ
<?php

//WARNING: The contents of this file are auto-generated
include('custom/metadata/bug43208Test_productsMetaData.php');

//WARNING: The contents of this file are auto-generated
include('modules/Contacts/Contact.php');
?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    } 
      
}

function tearDown() {
    if(file_exists($this->tableDictionaryExtFile . '.backup')) {
       copy($this->tableDictionaryExtFile . '.backup', $this->tableDictionaryExtFile);
       unlink($this->tableDictionaryExtFile . '.backup');  
    } else {
       unlink($this->tableDictionaryExtFile);
    }
}


function testRepairTableDictionaryExtFile() 
{	
	require_once('modules/UpgradeWizard/uw_utils.php');
	repairTableDictionaryExtFile();
	
	if(function_exists('sugar_fopen'))
	{
		$fp = @sugar_fopen($this->tableDictionaryExtFile, 'r');
	} else {
		$fp = fopen($this->tableDictionaryExtFile, 'r');
	}			
		
	$matches = 0;
    if($fp)
    {
         while($line = fgets($fp))
	     {
	    	if(preg_match('/\s*include\s*\(\'(.*?)\'\);/', $line, $match))
	    	{
	    	   $matches++;
	    	   $this->assertTrue(file_exists($match[1]), 'Assert that entry for file ' . $line . ' exists');
	    	}
	     }  
		 fclose($fp); 
   }	
   
   $this->assertEquals($matches, 1, 'Assert that there was one match for file modules/Contacts/Contact.php');
}


}

?>