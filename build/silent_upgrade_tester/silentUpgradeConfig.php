<?php
//$Id: silentUpgradeConfig.php Jun 18, 2008 2:42:51 PM nchander $
 
 /* Please read the comments before or next to each item to understand how to fill it.
    * NOTE: Windows uses backslashed (\) for paths and linux uses forward slash (/). When enter windows paths (i.e backslashes) be sure to escape it (i.e. \\ instead of just \).
    * Please be sure to use lowercase letters for database names, operating system and flavor names (i.e. ent, pro, ce). 
    */
 
 $silentUpgradeConfig = Array (
						/* This is the ABSOLUTE path to the location of the silentUpgrade.php 
						 * file. This file is what we are testing here so the script needs 
						 * the path to call it.
						 */
						'silentUpgradeFilePath' => 'C:\\xampp\\htdocs\\silentUpgrade.php',
						
						/* This is an array of arrays that will define all the files for install and upgrade.
						  * KEEP IN MIND ALL PATHS ARE ABSOLUTE (i.e. C:\\xampp\\htdocs\\file.zip or \var\www\files\file.zip)
						  * Insert an array for each version you want to install/upgrade from. That array has three options:
						  * install files => An array of arrays with first element being the zip file for installing whatever version you want to install, 
						  *                       second element being type (i.e. ENT, PRO, CE) and third element being the database you want to deploy to (chose from mysql, mssql or oci8)
						  * short version => The version number of the installation WITHOUT any periods (i.e. 5.1.0RC would become 510rc or 510RC)
						  * full version => the version number with all the punctuations (i.e. 5.1.0RC)
						  * upgrade files =>  An array of arrays with first element being the zip file for upgrading from the installed version to whatever, 
						  *       		 second element being type (i.e. ENT, PRO, CE), and third element being the database (mysql, mssql or oci8)
						  *
						  */
						'files' => 
							Array(
								Array(
									'install files' => 
										Array(
											Array('C:\\xampp\\htdocs\\test\\451i_zips\\SugarEnt-4.5.1i.zip', 'ent', 'mysql'), 
											Array('C:\\xampp\\htdocs\\test\\451i_zips\\SugarPro-4.5.1i.zip', 'pro', 'mssql'), 
											Array('C:\\xampp\\htdocs\\test\\451i_zips\\SugarOS-4.5.1i.zip', 'ce', 'mysql')
										),
									'short version' => '451i',
									'full version' => '4.5.1i',
									'upgrade files' => 
										Array(
											Array('C:\\xampp\\htdocs\\test\\510_zips\\SugarEnt-Upgrade-4.5.1-to-5.1.0.zip', 'ent', 'mysql'), 
											Array('C:\\xampp\\htdocs\\test\\451i_zips\\SugarPro-Upgrade-4.5.1-to-5.1.0RC.zip', 'pro', 'mssql'), 
											Array('C:\\xampp\\htdocs\\test\\451i_zips\\SugarCE-Upgrade-4.5.1-to-5.1.0RC.zip', 'ce', 'mysql')
										),
								),
								Array(
									'install files' => 
										Array(
											Array('C:\\xampp\\htdocs\\test\\500_zips\\SugarEnt-5.0.0.zip', 'ent', 'oci8'), 
											Array('C:\\xampp\\htdocs\\test\\500_zips\\SugarPro-5.0.0.zip', 'pro', 'mssql'), 
											Array('C:\\xampp\\htdocs\\test\\500_zips\\SugarCE-5.0.0.zip', 'ce', 'mysql')
										),
									'short version' => '500',
									'full version' => '5.0.0',
									'upgrade files' => 
										Array(
											Array('C:\\xampp\\htdocs\\test\\510_zips\\SugarEnt-Upgrade-5.0.0-to-5.1.0.zip', 'ent', 'oci8'), 
											Array('C:\\xampp\\htdocs\\test\\500_zips\\SugarPro-Upgrade-5.0.0-to-5.1.0RC.zip', 'pro', 'mssql'), 
											Array('C:\\xampp\\htdocs\\test\\500_zips\\SugarCE-Upgrade-5.0.0-to-5.1.0RC.zip', 'ce', 'mysql')
										),
								),
							),
							
 						'logfile' => 'C:\\xampp\\htdocs\\sugarlog.log', //where you want silentUpgrade.php and silentUpgradeTester.php to write its log to
						
						/* Where your HTML/php files should be located (ie. /var/www/ or C:\\inetpub). THIS MUST BE THE BASE WWW/HTML DIRECTORY! 
						    In other words if one were to go to http://<server name> (for example http://localhost or http://honey-b one would
					              see the files that are listed in this directory
						*/
 						'html_directory' => 'C:\\xampp\\htdocs',
						
						/* Where do you want all the files created by this script (in other words the unzipped files, etc) to be stored in. NOT AN ABSOLUTE PATH!. 
						    In other words this is a folder in html_directory. So if your html_directory is /var/www and store_directory is test then all the files 
						    will be stored in /var/www/test. Leave blank if you just want to store in root html_directory.  This script will create directories in 
						    this folder using the naming scheme  silent_<DB><SHORT VERSION><TYPE>  so for 5.1.0 PRO on mysql it would become silent_mysql510pro. 
						    If such directories already exists it WILL be removed! 
						*/
						'store_directory' => 'test',
 						
 						/* Top level array is all the different db versions you want to install to
 						 * Each version has it's on associated array. Please fill in the host, username
 						 * and password for each array
						 *
						 * restrictions is an array that lists what versions cannot be run on this DB. For example Oracle can only be run on Ent so we list pro and ce
						 * thus the script will NOT install CE and PRO versions of Sugar on Oracle.
 						 */
 						 
 						'dbversions' => 
 							Array(
								'mysql' =>
									Array(
										'host' => 'localhost',
										'user' => 'root',
										'pwd' => '',
										'restrictions' => Array() //If blank all flavors of sugar (ent,pro,ce) will be run on this DB type. For example Oracle only runs on Ent
									),
								'oci8' => //Oracle
									Array(
										'host' => 'localoracle', //the SID in tnsnames.ora or database name in tnsname.ora (default is XE for an Oracle 10g install)
										'user' => 'system', //system username (usually 'system')
										'pwd' => 'idontknow', //system password
										'restrictions' => Array('pro', 'ce')//If blank all flavors of sugar (ent,pro,ce) will be run on this DB type. For example Oracle only runs on Ent so we list pro and ce
									),
								 'mssql' =>
									Array(
										'host' => 'localhost',
										'instance' => ' ', //if empty leave a space between the quotes
										'user' => 'root',
										'pwd' => 'idontknow',
										'restrictions' => Array() //If blank all flavors of sugar (ent,pro,ce) will be run on this DB type. For example Oracle only runs on Ent
									),
								
							),
							
 						'build_dir' => 'C:\\xampp\\htdocs\\SilentUpgrader', //the ABSOLUTE path where this script is located in
 						'path_to_php' => 'C:\\xampp\\php' //if OS is Windows put the ABSOLUTE path to the php directory (NO PHP.EXE). For linux just put 'php'
 );
?>
