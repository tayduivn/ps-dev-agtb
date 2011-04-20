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
var $corruptExtModuleFile = 'custom/Extension/application/Ext/TableDictionary/Bug43208_module.php';

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
    
    if( $fh = @fopen($this->corruptExtModuleFile, 'w+') )
    {
$string = <<<EOQ
<?php
 //WARNING: The contents of this file are auto-generated
 include ("modules/Accounts/Account.php");
 	include( "custom/metadata/bug43208Test_productsMetaData.php" ); 
include("modules/Contacts/Contact.php") ;
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
    
    if(file_exists($this->corruptExtModuleFile)) {
       unlink($this->corruptExtModuleFile);
    }
    
}


function testRepairTableDictionaryExtFile() 
{	
	require_once('ModuleInstall/ModuleInstaller.php');
	
	repairTableDictionaryExtFile();
	
	$moduleInstaller = new ModuleInstaller();
	$moduleInstaller->silent = true;
	$moduleInstaller->rebuild_tabledictionary();
	
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
	    	if(preg_match('/\s*include\s*\(\s*[\'|\"](.*?)[\'\"]\s*\)\s*;/', $line, $match))
	    	{
	    	   $matches++;
	    	   $this->assertTrue(file_exists($match[1]), 'Assert that entry for file ' . $line . ' exists');
	    	}
	     }  
		 fclose($fp); 
   }	
   
   $this->assertEquals($matches, 1, 'Assert that there was one match for file modules/Contacts/Contact.php in ' . $this->tableDictionaryExtFile);
   
   
	if(function_exists('sugar_fopen'))
	{
		$fp = @sugar_fopen($this->corruptExtModuleFile, 'r');
	} else {
		$fp = fopen($this->corruptExtModuleFile, 'r');
	}			
		
	$matches = 0;
    if($fp)
    {
         while($line = fgets($fp))
	     {
	    	if(preg_match('/\s*include\s*\(\s*[\'|\"](.*?)[\'\"]\s*\)\s*;/', $line, $match))
	    	{
	    	   $matches++;
	    	   $this->assertTrue(file_exists($match[1]), 'Assert that entry for file ' . $line . ' exists');
	    	}
	     }  
		 fclose($fp); 
   }	
   
   $this->assertEquals($matches, 2, 'Assert that there was two matches for correct entries in ' . $this->corruptExtModuleFile);   
   
}


}

/**
 * repairTableDictionaryExtFile
 * 
 * There were some scenarios in 6.0.x whereby the files loaded in the extension tabledictionary.ext.php file 
 * did not exist.  This would cause warnings to appear during the upgrade.  As a result, this
 * function scans the contents of tabledictionary.ext.php and then remove entries where the file does exist.
 */
function repairTableDictionaryExtFile()
{
	$tableDictionaryExtDirs = array('custom/Extension/application/Ext/TableDictionary', 'custom/application/Ext/TableDictionary');
	
	foreach($tableDictionaryExtDirs as $tableDictionaryExt)
	{
	
		if(is_dir($tableDictionaryExt) && is_writable($tableDictionaryExt)){
			$dir = dir($tableDictionaryExt);
			while(($entry = $dir->read()) !== false)
			{
				$entry = $tableDictionaryExt . '/' . $entry;
				if(is_file($entry) && preg_match('/\.php$/i', $entry) && is_writeable($entry))
				{
			
						if(function_exists('sugar_fopen'))
						{
							$fp = @sugar_fopen($entry, 'r');
						} else {
							$fp = fopen($entry, 'r');
						}			
						
						
					    if($fp)
				        {
				             $altered = false;
				             $contents = '';
						     
				             while($line = fgets($fp))
						     {
						    	if(preg_match('/\s*include\s*\(\s*[\'|\"](.*?)[\"|\']\s*\)\s*;/', $line, $match))
						    	{
						    	   if(!file_exists($match[1]))
						    	   {
						    	      $altered = true;
						    	   } else {
						    	   	  $contents .= $line;
						    	   }
						    	} else {
						    	   $contents .= $line;
						    	}
						     }
						     
						     fclose($fp); 
				        }
				        
				        
					    if($altered)
					    {
							if(function_exists('sugar_fopen'))
							{
								$fp = @sugar_fopen($entry, 'w');
							} else {
								$fp = fopen($entry, 'w');
							}		    	
				            
							if($fp && fwrite($fp, $contents))
							{
								fclose($fp);
							}
					    }					
				} //if
			} //while
		} //if
	}
}

?>