$Id: README.txt Jul 3, 2008 4:30:45 PM nchander $

This script (silentUpgradeTester.php) will install and upgrade different versions and flavors of SugarCRM onto different databases according to the settings specified in silentUpgradeConfig.php. 


----------------------------
TABLE OF CONTENTS
----------------------------
1) System Requirements
2) Usage
3) The Config File
4) Troubleshooting



----------------------------
1) SYSTEM REQUIREMENTS
----------------------------
Windows or Linux only
PHP 5

For installation on MySQL:
MySQL database
mysql PHP extension

For installation on MSSQL:
Windows only
MSSQL database
mssql PHP extension

For installation on Oracle:
Oracle database (10g recommended)
oci8 PHP extension
The environment variable ORACLE_HOME must be compiled into PHP for Linux installations

----------------------------
2) USAGE
----------------------------
On Windows:
[path to php]\php.exe -f [path to script]\silentUpgradeTester.php
On Linux:
[sudo if required] php [path to script]/silentUpgradeTester.php

This script requires no command line arguments but does require that the settings in silentUpgradeConfig.php to be accurate

----------------------------
3) THE CONFIG FILE
----------------------------
The config file is located in silentUpgradeConfig.php. It is represented as a associative array of associative arrays. Please be sure to close all opened parantheses and quotation marks. Be sure to end all listings with a comma (,). Also for Windows paths be sure to use double backslashes (\\) or the script will throw an error. Please be sure to use lowercase letters for database names, operating system and flavor names (i.e. ent, pro, ce). 

The following instructions are also listed as comments in the silentUpgradeConfig.php file.

silentUpgradeFilePath -> This is the path to the silentUpgrade.php file. This is the the file that we are essentially testing so without this this script cannot do much. 

files -> This is an array of associative arrays (i.e. the keys are defined as strings not integers). Each item in this 'files' part will be array corresponding to a different version of SugarCRM. Each version is represented by another associative array. hat array has three options:
		install files => An array of arrays with first element being the zip file for installing whatever version you want to install, second element being type (i.e. ENT, PRO, CE) and third element being the database you want to deploy to (chose from mysql, mssql or oci8)
		short version => The version number of the installation WITHOUT any periods (i.e. 5.1.0RC would become 510rc or 510RC)
	    full version => the version number with all the punctuations (i.e. 5.1.0RC)
		upgrade files =>  An array of arrays with first element being the zip file for upgrading from the installed version to whatever, second element being type (i.e. ENT, PRO, CE) and third element being the database you want to deploy to (mysql, mssql or oci8)
		
logfile -> The location of a logfile (a plain text file) where you want the scripts to write their logs to

html_directory -> Where your HTML/php files should be located (ie. /var/www/ or C:\\inetpub). THIS MUST BE THE BASE WWW/HTML DIRECTORY! In other words if one were to go to http://<server name> (for example http://localhost or http://honey-b one would see the files that are listed in this directory.

store_directory -> Where do you want all the files created by this script (in other words the unzipped files, etc) to be stored in. NOT AN ABSOLUTE PATH!. In other words this is a folder in html_directory. So if your html_directory is /var/www and store_directory is test then all the files will be stored in /var/www/test. Leave blank if you just want to store in root html_directory.  This script will create directories in this folder using the naming scheme  silent_<DB><SHORT VERSION><TYPE>  so for 5.1.0 PRO on mysql it would become silent_mysql510pro. If such directories already exists it WILL be removed! 

dbversions -> An array of associative arrays with each associative arrays corresponding to a database version you want installed (from MySQL, MSSQL and Oracle). Instead of deleting an element you do not want to install to it would be wiser to comment out that module. Please fill in the host, username and password for each DB. Note that for Oracle the database entry in the array is called oci8 NOT Oracle. Do not change this. Also for Oracle the username and password is the system username (default is system) and password and the host is the SID or database name in the tnsnames.ora file. Restrictions is an array that lists which flavors of SugarCRM WILL NOT be installed on this database. For example Oracle (oci8) only works on the ENT flavor so we list (pro, ce) for restrictions so that PRO and CE will not be installed on Oracle.

build_dir -> The ABSOLUTE path where this script is located

path_to_php -> On Windows this is the ABSOLUTE path to the php directory (NO PHP.EXE). On Linux just type 'php'

----------------------------
4) TROUBLESHOOTING
----------------------------

Q) What is the admin password for the install?
A) The password is 'asdf' (without quotes).

Q) The script sometimes hangs during upgrades for OS/CE flavors
A) This is because the silentUpgrade.php is waiting for user to accept the license. On Windows there will be a command prompt window that is minimized. You can accept the license in that window by typing in 'yes' and pressing the Enter key. The best way to fix this problem is to comment out the code in silentUpgrade.php that asks for license acceptance

Q) When connecting to a remote MySQL database the script sometimes dies
A) The user you specified probably does not have the correct permission to access the remote database.

Q) The script sometimes dies with an error that 'cannot delete X file or cannot drop Y database'. 
A) This is an issue with the filesystem (or the database) when there are existing connections or processes. In this case the best thing to do is to manually delete the files or drop the database. On Linux it would help to run the script as super user by using sudo php [path to script]/silentUpgradeTester.php

Q) When installing or upgrading on a MSSQL database the script sometimes dies with an error "CANNOT SELECT DATABASE".
A) This is a common error with MSSQL and PHP. Currently there is no fix to this issue. The best thing to do is try again.

Q) When installing or upgrading on an Oracle database the script dies with an OCI error stating that it cannot find or resolve the TNS connector (for Linux).
A) There can be two things at fault here. Firstly check to make sure that the host provided for oci8 in silentUpgradeConfig.php is actually listed in the tnsnames.ora file and all the settings in both files are accurate. If that is not the case than it is most likely because the environment variable ORACLE_HOME was not set during compiliation of PHP. The best way to fix this is to recompile PHP with the follwing option:
	--with-oci8=/path/to/oracle/home/dir