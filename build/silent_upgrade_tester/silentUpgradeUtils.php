<?php
//$Id: silentUpgradeUtils.php Jun 18, 2008 3:12:14 PM nchander $

/* Given the operating system set up variables for used command line calls (i.e. pwd, cd, cp, etc)
 * If OS is Windows $pathToPhp MUST be set correctly */
function setCmdVars($os, $pathToPhp ='')
{
	global $cd, $cp, $rm_rf, $rm, $pwd, $php, $mkdir, $slash, $mv;

	$cd = 'cd';

	if(strtolower($os) == 'windows')
	{
		if( !isset($pathToPhp) || empty($pathToPhp) || !file_exists("$pathToPhp\php.exe") )
		{
			echo "***** FAILURE: PLEASE PROVIDE CORRECT PATH TO PHP EXECUTABLE! Given $pathToPhp\n";
			die();
		}

		$cp = 'copy /Y';
		$rm_rf = 'del /S /F';
		$rm = 'del';
		$pwd = 'cd';
		$php = "$pathToPhp\php.exe";
		$slash = '\\';
		$mkdir = 'mkdir';
		$mv = 'move /Y';
	}
	else if(strtolower($os) == 'linux')
	{
		$cp = 'cp';
		$rm_rf = 'rm -rf';
		$rm = 'rm';
		$pwd = 'pwd';
		$php = "php";
		$slash = "/";
		$mkdir = 'mkdir -p';
		$mv = 'mv';

	}
	else
	{
		echo "***** FAILURE: PLEASE PROVIDE CORRECT OPERATING SYSTEM! Given $os\n";
		die();
	}
}

function findTypesForInstall($install_files)
{
	$types = Array();
	foreach($install_files as $file)
	{
		$types[] = $file[1];
	}
	return $types;
}

function createOCIUsers($shortver, $html_dir, $sys_usr, $sys_pass, $host, $slash)
{
	$name = "silent_" . $shortver;
	$datfile = "$html_dir$slash" . "oracle_db_files" . "$slash$name.ora";
	if(!file_exists("$html_dir$slash" . "oracle_db_files")) mkdir("$html_dir$slash" . "oracle_db_files");

	$link = oci_pconnect($sys_usr, $sys_pass, $host) or die("Can't connect to DB!!<br><br>");

	$query = Array();

	$query[] = "CREATE TABLESPACE $name LOGGING DATAFILE '$datfile' SIZE 5M EXTENT
				MANAGEMENT LOCAL SEGMENT SPACE MANAGEMENT AUTO";
	$query[] = "ALTER DATABASE DATAFILE '$datfile' AUTOEXTEND ON NEXT 1M";
	$query[] = "CREATE USER $name  PROFILE \"DEFAULT\" IDENTIFIED BY \"$name\"
				DEFAULT TABLESPACE $name QUOTA UNLIMITED ON $name ACCOUNT UNLOCK";
	$query[] = "GRANT ALTER ANY TABLE TO $name";
	$query[] = "GRANT CREATE TABLE TO $name";
	$query[] = "GRANT DELETE ANY TABLE TO $name";
	$query[] = "GRANT DROP ANY TABLE TO $name";
	$query[] = "GRANT INSERT ANY TABLE TO $name";
	$query[] = "GRANT SELECT ANY SEQUENCE TO $name";
	$query[] = "GRANT CREATE ANY SEQUENCE TO $name";
	$query[] = "GRANT DROP ANY SEQUENCE TO $name";
	$query[] = "GRANT SELECT ANY TABLE TO $name";
	$query[] = "GRANT UPDATE ANY TABLE TO $name";
	$query[] = "GRANT CONNECT TO $name";

	foreach($query as $q)
	{
		$res = @oci_parse($link, $q);
		@oci_execute($res);
	}

	@oci_commit($link);
	@oci_close($link);
}

function cleanDB($shortVer, $type, $dbinfo)
{
	if(strtolower($dbinfo['type']) === 'mysql')
	{
		//echo "DROPPING " . strtoupper($dbinfo['type']) . " DB silent_$type$shortVer\n";
		$query = "drop database if exists `silent_$type$shortVer`;";
		$link = mysql_connect($dbinfo['host'], $dbinfo['username'], $dbinfo['pwd']) or die("\n****ERROR: COULD NOT CONNECT: " . mysql_error() );
		mysql_query($query) or die("\n****ERROR: DROP DB QUERY FAILED: " . mysql_error() );
		mysql_close($link);
		return 0;
	}
	if(strtolower($dbinfo['type'] === 'mssql'))
	{
		$db = "silent_$type$shortVer";
		echo "DROPPING " . strtoupper($dbinfo['type']) . " DB $db\n";


		// connection to the database
		$server = $dbinfo['host'];
		$instance = $dbinfo['instance'];
		$username = $dbinfo['username'];
		$password = $dbinfo['pwd'];

		if( trim($instance) == '' )
			$host = $server;
		else
			$host = $server . "\\" . $instance;

		$dbhandle = mssql_connect($host, $username, $password)
			or die("Couldn't connect to SQL Server on $server");


		/* First we need to kill any process that is working with this DB otherwise we'll get a stupid error from MSSQL */
		$res = mssql_query("sp_who", $dbhandle);

		$to_kill = Array();

		while($row = mssql_fetch_assoc($res))
		{
			if($row['dbname'] == $db)
			{
				$to_kill[] = Array('name' => $row['dbname'], 'spid' => $row['spid']);
			}
		}

		foreach($to_kill as $kill)
		{
			if($kill['name'] == $db) //check again
			{
				mssql_query("kill " . $kill['spid'], $dbhandle); //kill process
			}
		}

		/* Now we need to check if DB exists... too bad MSSQL doesn't support if exists */
		$check = "SELECT count(name) num FROM master..sysdatabases WHERE name = N'$db'";
		$tableCntRes = mssql_query($check);
		$tableCnt= mssql_fetch_row($tableCntRes);

		//if this db already exists, then drop it
		if($tableCnt[0]>0){
			$drop = "DROP DATABASE $db";
			//echo $drop;
		   @mssql_query($drop) or die("Can't drop DB!");
		}

		mssql_close($dbhandle);
	}
}

function mvAndRmUnzipDir($thedir, $dest, $mv, $slash, $type)
{
	//echo "func - pass in vars are\nthedir - $thedir\ndest - $dest\ntype - $type\n";
	chdir($thedir);
	$files = glob("Sugar*");
	if(count($files) == 0)
	{
		echo "****ERROR: FILE UNZIP DID NOT WORK****\n";
		//print_r($files);
		die();
	}

	chdir($thedir . $slash . $files[0]);

	if ($current = @opendir( @getcwd() ) )
	{
		while (false !== ($children = readdir($current)))
		{
			//echo "in while loop\n";
			if($children != "." && $children != "..")
			{
				$mvfile = @getcwd() . $slash . $children;
				//echo "$mv $mvfile $dest\n";
				$cmd = "$mv \"$mvfile\" \"$dest\"";
				system($cmd);
			}
		}

		closedir($current);
		//rmdir(getcwd());
	}
	else
	{
		echo "****ERROR: COULDN'T OPEN UNZIPPED DIRECTORY. CHECK PERMISSIONS\n";
		die();
	}
}

function recursiveDirRm($deldir, $slash)
{
	if( !file_exists($deldir) )
		return; // nothing here to delete

	if( is_file($deldir) ) //if it's a file... delete it and return
	{
		unlink($deldir);
		return;
	}

	$dir_cont = scandir($deldir);

	//nothing in this folder... just delete it
	if( count($dir_cont) == 2)
	{
		rmdir($deldir);
		return;
	}

	//delete all the folders inside this one
	for($i = 0; $i < count($dir_cont); $i++)
	{
		if($dir_cont[$i] == '.' || $dir_cont[$i] == "..")
			continue;

		if( is_file($dir_cont[$i]) ) //if it's a file
		{
			unlink($deldir . $slash . $dir_cont[$i]); //delete it
		}
		else //otherwise... it's a folder so...
		{
			//...delete the folder with index $i
			recursiveDirRm($deldir . $slash . $dir_cont[$i], $slash);
		}
	}

	// all the stuff inside the folder has been deleted. Let's delete this folder now
	rmdir($deldir);
}

function writeConfigFilesForInstall($loc, $slash, $dbhost, $dbuser, $dbpass, $type,
									$ver, $fullver, $dbtype, $setup_system_name, $hostname, $url, $dbinstance,
									$dbdroptables, $sidbname)
{
	$type = strtolower($type);
	$sugar_config_si = '$sugar_config_si';
	$sugar_config = '$sugar_config';

	require("configs/config_heredocs.php");

	$fh = fopen($loc . $slash . "config_si.php", "w");
	if( fwrite($fh,$config_si) == -1)
	{
		echo "****ERROR: COULD NOT WRITE CONFIG FILE config_si.php";
		die();

	}
	fclose($fh);

	$fh = fopen($loc . $slash . "config_$type.php", "w");
	if( fwrite($fh, $config_type) == -1)
	{
		echo "****ERROR: COULD NOT WRITE CONFIG FILE config_si.php";
		die();

	}
	fclose($fh);
}

?>
