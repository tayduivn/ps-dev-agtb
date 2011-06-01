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

var $tableDictionaryExtFile1 = 'custom/Extension/application/Ext/TableDictionary/tabledictionary.ext.php';		
var $tableDictionaryExtFile2 = 'custom/application/Ext/TableDictionary/tabledictionary.ext.php';	
var $corruptExtModuleFile = 'custom/Extension/application/Ext/TableDictionary/Bug43208_module.php';

function setUp() {

    if(file_exists($this->tableDictionaryExtFile1)) {
       copy($this->tableDictionaryExtFile1, $this->tableDictionaryExtFile1 . '.backup');
       unlink($this->tableDictionaryExtFile1);
    } else if(!file_exists('custom/Extension/application/Ext/TableDictionary')){
       mkdir_recursive('custom/Extension/application/Ext/TableDictionary');
    }

    if( $fh = @fopen($this->tableDictionaryExtFile1, 'w+') )
    {
$string = <<<EOQ
<?php

//WARNING: The contents of this file are auto-generated
include('custom/metadata/bug43208Test_productsMetaData.php');

//WARNING: The contents of this file are auto-generated
include('custom/Extension/application/Ext/TableDictionary/Bug43208_module.php');
?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }     
    

    if(file_exists($this->tableDictionaryExtFile2)) {
       copy($this->tableDictionaryExtFile2, $this->tableDictionaryExtFile2 . '.backup');
       unlink($this->tableDictionaryExtFile2);
    } else if(!file_exists('custom/application/Ext/TableDictionary')){
       mkdir_recursive('custom/application/Ext/TableDictionary');
    }    
    
    if( $fh = @fopen($this->tableDictionaryExtFile2, 'w+') )
    {
$string = <<<EOQ
<?php

//WARNING: The contents of this file are auto-generated
include('custom/metadata/bug43208Test_productsMetaData.php');

//WARNING: The contents of this file are auto-generated
include('custom/Extension/application/Ext/TableDictionary/Bug43208_module.php');
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
 	include( "custom/metadata/bug43208Test_productsMetaData.php" ); 
?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }        
    
}

function tearDown() {
    if(file_exists($this->tableDictionaryExtFile1 . '.backup')) 
    {
       copy($this->tableDictionaryExtFile1 . '.backup', $this->tableDictionaryExtFile1);
       unlink($this->tableDictionaryExtFile1 . '.backup');  
    } else {
       unlink($this->tableDictionaryExtFile1);
    }

    if(file_exists($this->tableDictionaryExtFile2 . '.backup')) 
    {
       copy($this->tableDictionaryExtFile2 . '.backup', $this->tableDictionaryExtFile2);
       unlink($this->tableDictionaryExtFile2 . '.backup');  
    } else {
       unlink($this->tableDictionaryExtFile2);
    }    
    
    if(file_exists($this->corruptExtModuleFile)) {
       unlink($this->corruptExtModuleFile);
    }
    
}


function testRepairTableDictionaryExtFile() 
{	
	require_once('ModuleInstall/ModuleInstaller.php');
	repairTableDictionaryExtFile();
	
	if(function_exists('sugar_fopen'))
	{
		$fp = @sugar_fopen($this->tableDictionaryExtFile1, 'r');
	} else {
		$fp = fopen($this->tableDictionaryExtFile1, 'r');
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
   
   $this->assertEquals($matches, 1, 'Assert that there was one match for correct entries in file ' . $this->tableDictionaryExtFile1);

   
	if(function_exists('sugar_fopen'))
	{
		$fp = @sugar_fopen($this->tableDictionaryExtFile2, 'r');
	} else {
		$fp = fopen($this->tableDictionaryExtFile2, 'r');
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
   
   $this->assertEquals($matches, 1, 'Assert that there was one match for correct entries in file ' . $this->tableDictionaryExtFile2);
      
   
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
   
   $this->assertEquals($matches, 0, 'Assert that there was one match for correct entries in file ' . $this->corruptExtModuleFile);   
   
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