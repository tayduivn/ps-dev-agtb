<?php
//$Id: silentUpgradeTester.php Jun 18, 2008 2:42:33 PM nchander $

if(!defined('sugarEntry')) define('sugarEntry', true);

require_once('silentUpgradeUtils.php');
require_once('silentUpgradeConfig.php');
require('zip_utilities.php');

/**************************************************/

echo "\n";
echo "********************************************************************\n";
echo "***************This process may take a long time***************\n";
echo "********************************************************************\n";
echo "\n";

runWizard();

/***************************************************/


 function runWizard()
 {
 	global $cd, $cp, $rm_rf, $rm, $pwd, $php, $mkdir, $slash, $mv;
 	global $silentUpgradeConfig, $logfp;

	$logfile = $silentUpgradeConfig['logfile'];
	$logfp = fopen($logfile, "a");

	/*Set some OS specific variables*/
	$os = strtolower(substr(PHP_OS,0,3));
	if($os == 'win')
	{
		$os = 'windows';
		$silentUpgradeConfig['operating_system'] = 'windows';
	}
	else
	{
		$os = 'linux';
		$silentUpgradeConfig['operating_system'] = 'linux';
	}

	global $php, $slash;
	setCmdVars($os, $silentUpgradeConfig['path_to_php']);

	/* Let's get some variables out for ease of access */
 	$files = $silentUpgradeConfig['files'];
	$dbversions = $silentUpgradeConfig['dbversions'];
	$output = "The URLs for the deployed and upgraded versions are located at (using database):\n";

	fwrite($logfp, "\n\n" . date("M d, Y H:i") . ": ****STARTING silentUpgradeTester.php****\n");

	foreach($files as $module) //for each db
	{
		if(isset($module['install files']) && $module['install files'] != null){
			foreach($module['install files'] as $file_type)
			{
				/*SET UP DB STUFF*/
				$db = $file_type[2];
				if($db === 'oracle') $db = 'oci8'; //oracle is oci8

				$dbinfo = $dbversions[$db];

				if(!isset($dbinfo) || empty($dbinfo))
				{
					if(empty($db))
					{
						echo "****ERROR: silentUpgradeConfig.php does not specifiy a database for " .
							$module['full version'] . "\n\n";
						fwrite($logfp, "****ERROR: silentUpgradeConfig.php does not specifiy a database for " .
							$module['full version'] . "\n");
					}
					else
					{
						echo "****ERROR: silentUpgradeConfig.php specifies installation of {$module['full version']}" .
						     $file_type[1] . " on a non-existant database $db. Skipping it****\n\n";
						fwrite($logfp, "****ERROR: silentUpgradeConfig.php specifies installation of {$module['full version']}" .
						     $file_type[1] . " on a non-existant database $db. Skipping it****\n");
					}
					continue;
				}

				$restrictions = $dbinfo['restrictions'];

				if(in_array($file_type[1], $restrictions)) //if this type cannot be run on this DB then skip it
					continue;

				if($db == 'mssql')
				{
					if( trim($dbinfo['instance']) == '' )
					{
						$dbinfo['instance'] = ' ';
						$dbversions[$db]['instance'] = ' ';
						$silentUpgradeConfig['dbversions'][$db]['instance'] = ' ';
					}
					if($os == 'linux')
					{
						echo "****ERROR: Your silentUpgradeConfig.php file says to install SugarCRM on " .
							 "linux using mssql. MSSQL is not supported on linux. Skipping it****\n\n";
						fwrite($logfp, "****ERROR: Your silentUpgradeConfig.php file says to install SugarCRM on " .
							 "linux using mssql. MSSQL is not supported on linux. Skipping it****\n");
						continue;
					}
				}
				if($db == 'oci8')
				{
					$systemusr = $dbinfo['user'];
					$systempwd = $dbinfo['pwd'];
					$name = "silent_" . $module['short version'];
					$dbinfo['user'] = $name;
					$dbinfo['pwd'] = $name;
					$dbversions[$db]['user'] = $name;
					$dbversions[$db]['pwd'] = $name;
					$silentUpgradeConfig['dbversions'][$db]['user'] = $name;
					$silentUpgradeConfig['dbversions'][$db]['pwd'] = $name;

					createOCIUsers($module['short version'],
									$silentUpgradeConfig['html_directory'] . $slash . $silentUpgradeConfig['store_directory'],
									$systemusr, $systempwd, $dbinfo['host'], $slash);
				}
				/*FINISH SETTING UP DB STUFF*/

				if(!file_exists($file_type[0]))
				{
					echo "****FILE " . $file_type[0] . " NOT FOUND. SKIPPING IT\n";
					fwrite($logfp, "****FILE " . $file_type[0] . " NOT FOUND. SKIPPING IT\n");
					continue;
				}

				$install_types[] = $file_type[1] . ",$db";

				echo "DEPLOYING " . $module['full version'] . " " . $file_type[1] . " ON DATABASE $db\n";
				fwrite($logfp, "****DEPLOYING " . $module['full version'] . " " . $file_type[1] . " ON DATABASE $db\n");

				$dest_dir = $silentUpgradeConfig['store_directory'] . $slash . "silent_$db" .
							$module['short version'] . $file_type[1];

				deploy( $file_type[0], $silentUpgradeConfig['html_directory'], $dest_dir,
						$module['short version'], $module['full version'], $db, $file_type[1] );

				$output .= "http://localhost/" . $silentUpgradeConfig['store_directory']
						. "/silent_" . $db . $module['short version'] . $file_type[1] . " (using $db db silent_" .
						$file_type[1] . $module['short version'] . ")\n";
			}
		}
		echo "\n";

		if($os == 'windows') $WshShell = Array();
		$i = 0;
		if(isset($module['upgrade files']) && $module['upgrade files'] != null){
			foreach($module['upgrade files'] as $file_type)
			{
				/*SET UP DB STUFF*/
				$db = $file_type[2];
				if($db === 'oracle') $db = 'oci8'; //oracle is oci8

				if(!isset($dbversions[$db]) || empty($dbversions[$db]))
				{
					if(empty($db))
					{
						echo "****ERROR: silentUpgradeConfig.php does not specifiy a database for " .
							$module['full version'] . "\n\n";
						fwrite($logfp, "****ERROR: silentUpgradeConfig.php does not specifiy a database for " .
							$module['full version'] . "\n");
					}
					else
					{
						echo "****ERROR: silentUpgradeConfig.php specifies upgrading of {$module['full version']}" .
						     $file_type[1] . " on a non-existant database $db. Skipping it****\n\n";
						fwrite($logfp, "****ERROR: silentUpgradeConfig.php specifies upgrading of {$module['full version']}" .
						     $file_type[1] . " on a non-existant database $db. Skipping it****\n");
					}
					continue;
				}
				if($db == 'mssql' && $os == 'linux')
				{
					echo "****ERROR: Your silentUpgradeConfig.php file says to install SugarCRM on " .
						 "linux using mssql. MSSQL is not supported on linux. Skipping it****\n\n";
					fwrite($logfp, "****ERROR: Your silentUpgradeConfig.php file says to install SugarCRM on " .
						 "linux using mssql. MSSQL is not supported on linux. Skipping it****\n");
					continue;
				}
				/*FINISH SETTING UP DB STUFF*/

				if(!in_array("{$file_type[1]},$db", $install_types)) //if the specified version was never installed no need to upgrade
					continue;

				if(!file_exists($file_type[0]))
				{
					echo "***FILE " . $file_type[0] . " NOT FOUND. SKIPPING IT\n";
					fwrite($logfp, "****FILE " . $file_type[0] . " NOT FOUND. SKIPPING IT\n");
					continue;
				}

				echo "UPGRADING " . $module['full version'] . " " . $file_type[1] . " ON DATABASE $db\n";
				fwrite($logfp, "****UPGRADING " . $module['full version'] . " " . $file_type[1] . " ON DATABASE $db\n");

				if($os == 'windows')
				{
					echo "PLEASE NOTE THAT THIS PROCESS WILL LAUNCH ANOTHER COMMAND PROMPT WINDOW THAT WILL MINIMIZE. IGNORE IT" .
						 " AND DO NOT CLOSE IT!\n";
				}

				$dest_dir = $silentUpgradeConfig['html_directory'] . $slash . $silentUpgradeConfig['store_directory'] .
							$slash . "silent_$db" . $module['short version'] . $file_type[1];

				$args = '"' . $file_type[0] . '" "' . $logfile . '" "' .
						$dest_dir . '" admin yes';

				$sufile = $silentUpgradeConfig['silentUpgradeFilePath'];

				if(!file_exists($sufile))
				{
					echo "****ERROR: silentUpgrade.php FILE DOES NOT EXIST. PLEASE CHECK IF THE PATH IN CONFIG FILE IS CORRECT\n*****";
					fwrite($logfp, "****ERROR: silentUpgrade.php FILE DOES NOT EXIST. PLEASE CHECK IF THE PATH IN CONFIG FILE IS CORRECT\n*****");
					fclose($logfp);
					die();
				}

				$runCommand = "$php -f \"$sufile\" $args";

				fclose($logfp); //close log so that silentUpgrade.php can write to it

				if($os == 'windows')
				{
					$WshShell[$i] = new COM("WScript.Shell");
					$WshShell[$i]->Run($runCommand, 7, true);
				}
				else if($os == 'linux')
				{
					shell_exec($runCommand);
					system("chmod -R 777 " . $silentUpgradeConfig['html_directory'] . "/" . $silentUpgradeConfig['store_directory'] .
							"/silent_$db" . $module['short version'] . $file_type[1]);
				}
				$logfp = fopen($logfile, "a"); //reopen log file so we can write to it
				$i++;
			 }
		}
		echo "\n";
	}

	echo $output;
	fwrite($logfp, $output);
	fclose($logfp); //close log file
 }
function deploy( $ZIP_FILE, $HTML_DIR, $HTML_DEST_DIR, $SHORT_VER_SUFFIX, $FULL_VERSION, $db, $type )
{
	global $silentUpgradeConfig, $logfp;
	global $cd, $cp, $rm_rf, $rm, $pwd, $php, $mkdir, $mv, $slash;
	setCmdVars($silentUpgradeConfig['operating_system'], $silentUpgradeConfig['path_to_php']);
    global $silentUpgradeConfig;
    $BUILD_DIR = $silentUpgradeConfig['build_dir'];
	$DEST_DIR = $HTML_DIR . $slash . $HTML_DEST_DIR;

	$installStatus_RC = 0;
	$unit_test_RC = 0;
	$diff_sugardump_RC = 0;

	$short_ver = $type . $SHORT_VER_SUFFIX;
	$explode = 'explode';
	$configs = 'configs';

	$dbinfo = Array('type' => $db,
					'host' => $silentUpgradeConfig['dbversions'][$db]['host'],
					'username' => $silentUpgradeConfig['dbversions'][$db]['user'],
					'pwd' => $silentUpgradeConfig['dbversions'][$db]['pwd']);

	if($db == 'mssql')
		$dbinfo['instance'] = $silentUpgradeConfig['dbversions'][$db]['instance'];

	if($db != 'oci8')
	{
		fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: DROPPING $db DB silent_$type$SHORT_VER_SUFFIX\n");
		cleanDB($SHORT_VER_SUFFIX, $type, $dbinfo);
	}

	// *** DEPLOYING $type on localhost
	$trans_type = ucfirst( $type );
	if( $trans_type == "Ce" ){
		$trans_type = "CE";
	}

	/* The actual function from deploy.php */
	$type = strToLower( $type );

	//*** Deleting destination directory if it already exists... ****
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Deleting destination directory ($DEST_DIR) if it doesn't exist\n");
	recursiveDirRm($DEST_DIR, $slash);

	//*** Creating destination directory ***
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Creating destination directory ($DEST_DIR)\n");
	mkdir($DEST_DIR);
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Creating EXPLODE directory ($DEST_DIR . $slash . $explode)\n");
	mkdir($DEST_DIR . $slash . $explode);
	unzip($ZIP_FILE, "$DEST_DIR$slash$explode");

	$SUGARTYPE = $type;

	/* Move unzipped file. */
	mvAndRmUnzipDir($DEST_DIR . $slash . $explode, $DEST_DIR, $mv, $slash, $SUGARTYPE);

	//REMOVING EXPLODE DIR
	recursiveDirRm($DEST_DIR . $slash . $explode, $slash);

	//WRITING CONFIG FILES
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Creating config files\n");
	$context_root = explode("\\", $HTML_DEST_DIR);
	$context_root = implode("/", $context_root);
	$url = "http://localhost/" . $context_root;

	$dbinstance = '';
	$dbdroptables = 0;
	$sidbname = "silent_$SUGARTYPE$SHORT_VER_SUFFIX";
	if($db == 'mssql') $dbinstance = $silentUpgradeConfig['dbversions'][$db]['instance'];
	if($db == 'oci8')
	{
		$dbdroptables = 1;
		$sidbname = $silentUpgradeConfig['dbversions']['oci8']['host'];
	}

	writeConfigFilesForInstall($DEST_DIR, $slash, $silentUpgradeConfig['dbversions'][$db]['host'] , $silentUpgradeConfig['dbversions'][$db]['user']
						, $silentUpgradeConfig['dbversions'][$db]['pwd'], $SUGARTYPE, $SHORT_VER_SUFFIX , $FULL_VERSION,
						$db, "localhost Test for Silent Upgrade",
						'localhost', $url, $dbinstance, $dbdroptables, $sidbname);

	//WRITING CUSTOM VERSION FILE
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Creating custom version file\n");
	if( strtolower($silentUpgradeConfig['operating_system']) == 'windows')
		$cv = str_replace("\\", "\\\\", $ZIP_FILE);
	else
		$cv = $ZIP_FILE;

	writeCustomVersionFile( "$DEST_DIR/custom_version.php", $cv );

	if(strtolower($silentUpgradeConfig['operating_system']) == 'linux')
		system( "chmod -R 777 $DEST_DIR" );

	/* End the actual function */

	// print( "*** AUTO-INSTALLING $type on localhost\n" );
	fwrite($logfp, "****DEPLOY OF $FULL_VERSION$type: Auto-installing...\n");
	$installStatus = silentInstall($context_root, 'localhost');
	$installStatus_RC += $installStatus;
	if( $installStatus > 0 )
	{
		continue;
	}
}

function writeCustomVersionFile( $file, $custom_version )
{
    $FILE_OUTPUT =  "<?php\n";
    $FILE_OUTPUT .= "    \$custom_version = \"$custom_version\"\n";
    $FILE_OUTPUT .= "?>\n";

    $fh = fopen( $file, 'w+');
    if( fwrite( $fh, $FILE_OUTPUT ) == -1 )
    {
        die();
    }
    fclose( $fh );
	chmod($file, 0666);
}

function silentInstall($context_root, $hostname)
{
	# This script (currently) assumes that the sugarcrm app has been extracted
	# and that all necessary files are in place for a successful install.

    $si_results = "";
	$server_page = "http://$hostname/" . $context_root . "/install.php";

    // print( "Installing SugarCRM located at: $server_page ...\n" );
    $fh = fopen( $server_page . "?goto=SilentInstall&cli=true", "r" ) or die( $php_errormsg );

    while( !feof( $fh ) ){
        $si_results .= fread( $fh, 1048576 );
    }

    $info = stream_get_meta_data($fh);
    fclose( $fh );

    // message in a bottle
    preg_match( '/<bottle>(.*)<\/bottle>/s', $si_results, $message );
    if( count( $message ) == 2 ){
        // success
        print( $message[1] . "\n" );
    }
    else {
        // failure
        preg_match( '/Exit (.*)/', $si_results, $message );
        if( count( $message ) == 2 ){
            print( "Error.  Most likely your configuration file is invalid.  Message returned was:\n" );
			print( $si_results . "\n" );
			die();
        }
        else if( $info['timed_out'] ){
            print( "Error.  Connection timed out!" );
			print( $si_results . "\n" );
			die();
        }
        else {
            print( "Unknown error.  I don't know about this type of error message:\n" );
			print( $si_results . "\n" );
			die();
        }
        print( $si_results . "\n" );
		return 1;
    }
}
?>
