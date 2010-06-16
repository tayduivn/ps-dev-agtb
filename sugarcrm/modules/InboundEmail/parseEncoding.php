<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/*********************************************************************************
 * $Id: parseEncoding.php 32812 2008-03-14 18:33:02Z roger $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
// takes a file as an argument and parses the stuff as text;

function write_array_to_file( $the_name, $the_array, $the_file ) {
	
    $the_string =   "<?php\n" .
'\n
if(empty(\$GLOBALS["sugarEntry"])) die("Not A Valid Entry Point");
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
 ********************************************************************************/' .


                    "\n \$$the_name = " .
                    var_export_helper( $the_array ) .
                    ";\n?>\n";

    if( $fh = @sugar_fopen( $the_file, "w" ) ){
        fputs( $fh, $the_string);
        fclose( $fh );
        return( true );
    }
    else{
        return( false );
    }
}

function var_export_helper($tempArray) { 	 
 		if(!is_array($tempArray)){
 			return var_export($tempArray, true);	
 		}
         $addNone = 0; 	 
  	 
         foreach($tempArray as $key=>$val) 	 
         { 	 
                 if($key == '' && $val == '') 	 
                         $addNone = 1; 	 
         } 	 
  	 
         $newArray = var_export($tempArray, true); 	 
  	 
         if($addNone) 	 
         { 	 
                 $newArray = str_replace("array (", "array ( '' => '',", $newArray); 	 
         } 	 
  	 
         return $newArray;
 }

function grabFiles($url) {
	$dh = fsockopen($url, 80);
	while($fileName = readdir($dh)) {
		if(is_dir($url.$fileName)) {
			grabFiles($url.$fileName);
		}
		
		$fh = sugar_fopen($url.$fileName, "r");
		
		$fileContent = fread($fh, filesize($url.$fileName));
		
		$writeFile = "./{$fileName}";
		$fhLocal = sugar_fopen($writeFile, "w");
		
		fwrite($writeFile, $fileContent);
	}
}

///////////////////////////////////////////////////////////////////////////////
////	START CODE

while($file = readdir($dhUnicode)) {
	if(is_dir($file)) {
		$dhUniDeep = opendir("http://www.unicode.org/Public/MAPPINGS/OBSOLETE/EASTASIA/{$file}");
		
	}
}







$dh = opendir("./");
$search = array(" ", "  ", "   ", "    ");
$replace = array("\t","\t","\t","\t");


if(is_resource($dh)) {
	while($inputFile = readdir($dh)) {
		if(strpos($inputFile, "php")) {
			continue;
		}
		
		$inputFileVarSafe = str_replace("-","_",$inputFile);
		$outputFile = $inputFileVarSafe.".php";
		
		$fh = sugar_fopen($inputFile, "r");
		if(is_resource($fh)) {
			$charset = array();
			while($line = fgets($fh)) {
				$commentPos = strpos($line, "#");
				if($commentPos == 0) {
					continue; // skip comment strings
				}
				

				$exLine = str_replace($search, $replace, $line);
				$exLine = explode("\t", $line);


				$count = count($exLine);
				if($count < 2) {
					echo "count was {$count} :: file is {$inputFile} :: Error parsing line: {$line}\r";
					continue; // unexpected explode
				}
				
				// we know 0 is charset encoding
				// we know 1 is unicode in hex
				$countExLine = count($exLine);
				for($i=1; $i<$countExLine; $i++) {
					$exLine[$i] = trim($exLine[$i]);
					if($exLine[$i] != "") {
						$unicode = $exLine[$i];
						break 1;
					}
				}
				$charset[$exLine[0]] = $unicode;
				
			}
			
			if(count($charset) > 0) {
				write_array_to_file($inputFileVarSafe, $charset, $outputFile);
			}
			
		} else {
			echo "Error occured reading line from file!\r";
		}
		
	}	
} else {
	die("no directory handle");
}




echo "DONE\r";
?>