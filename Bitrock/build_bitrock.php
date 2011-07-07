#!/usr/bin/php -q
<?php
# This script prepares SugarCRM code for release.  Windows and UNIX
# distributable files for PRO, and CE are currently supported.
# $Id$

# Shivaram, 07/10/2007
# php's copy function doesn't seem to preserve file permissions
# hence using cp instead
function system_copy($source, $dest) {
    system ("cp ".$source." $dest");
    return 0;
}

# substituted system_copy for copy in copy_recursive in utils/dir_inc.php
function system_copy_recursive( $source, $dest ){
    if( is_file( $source ) ){
        return( system_copy( $source, $dest ) );
    }
    if( !is_dir($dest) ){
        mkdir( $dest );
    }

    $status = true;

    $d = dir( $source );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        $status &= system_copy_recursive( "$source/$f", "$dest/$f" );
    }
    $d->close();
    return( $status );
}

$SCRIPT_DIR     = getcwd();
$MAIL_BUILD_TO  = "";
$ON_DEMAND      = false;
$PUBLISH        = false;
$DEPLOY         = false;
$LOG_FILE       = "build-log.txt";
$SRC_DIR        = "sugarcrm";
$MOD_DIR        = "sugarmods";
$TMP_DIR        = "tmp";
$ZIP_DIR        = "zip";
$ZIP_CMD        = "zip -q -6";
$TAR_CMD        = "tar -czf";
$RC             = 0;    # return code

##  COMMAND LINE ARGUMENT PROCESSING
function usage(){
    print( "Builds the SugarCRM BitRock Installer for OS, PRO, ENT distributions.\n" );
    print( "Usage:\n" );
    print( "  " . $_SERVER['argv'][0] . " [OPTIONS]\n" );
    print( "Options:\n" );
    print( "  --help                give this message and exit\n" );
    print( "  --clean-only          run clean and exit\n" );
    print( "  --mail MAIL_TO        mail build outputs to MAIL_TO\n" );
    print( "  --publish             copy build outputs to publishing directory\n" );
    print( "  --version VERSION     give the sugarcrm version\n" );
    print( "  --skip-osx            skip building the OS X BitRock installer\n" );
    print( "  --skip-linux          skip building the Linux BitRock installer\n" );
    print( "  --skip-windows        skip building the Windows BitRock installer\n" );
    print( "  --skip-mssql          skip building the Windows-MSSQL BitRock installer\n" );
    print( "  --skip-solaris      skip building the Solaris-Intel BitRock installer\n" );
    exit( 1 );
}

#####################################################################
## CLEAN UP THE MESS
function clean(){
    global $BASE_BUILD_DIR;
    $lpr =& getPrinterInstance();

    $lpr->output( "+--->  START CLEAN UP" );

    if( file_exists( $BASE_BUILD_DIR ) ){
        $lpr->output( "+ Removing the directory $BASE_BUILD_DIR" );
        //rmdir_recursive( "$BASE_BUILD_DIR" );
        system( "rm -rf $BASE_BUILD_DIR" );
    }
}

function copy_sugarcrm($FLAVOR, $PLATFORM){
    global $TMP_DIR;
    global $SUGAR_VERSION;

    $RELEASE_ARCHIVE = "/var/www/html/release_archive";
    $LATEST_BUILDS  = "/var/www/html/builds/$SUGAR_VERSION/latest";

    switch($FLAVOR){
        case 'ce':
            $PREFIX = "SugarCE";
            break;
        case 'pro':
            $PREFIX = "SugarPro";

            break;
        case 'ent':
            $PREFIX = "SugarEnt";
            break;
        default:
            $lpr =& getPrinterInstance();
            $lpr->output("WARNING: flavor is $FLAVOR, hit default case\n");
    }

    system( "unzip -q -d $TMP_DIR/$PLATFORM/$FLAVOR $LATEST_BUILDS/$PREFIX-$SUGAR_VERSION.zip" );
    //print( "unzip -q -d $TMP_DIR/$PLATFORM/$FLAVOR $LATEST_BUILDS/$PREFIX-$SUGAR_VERSION.zip" );
    system( "mv $TMP_DIR/$PLATFORM/$FLAVOR/$PREFIX-Full-$SUGAR_VERSION $TMP_DIR/$PLATFORM/$FLAVOR/sugarcrm" );

    $distro = <<<EOT
<?php
\$distro_name='Sugar_FastStack';
?>
EOT;

    file_put_contents( "$TMP_DIR/$PLATFORM/$FLAVOR/sugarcrm/distro.php", $distro );
}

function build_installer($FLAVOR, $PLATFORM, $OUTPUT){
   $lpr =& getPrinterInstance();

    global $BASE_BUILD_DIR;
    global $TMP_DIR;
    global $ZIP_DIR;
    global $TAR_CMD;
    global $SUGAR_VERSION;

    global $FASTSTACK_SVN;
    global $BITROCK_BASE;

    $BASE_BITROCK_DIR = "$BITROCK_BASE/base";
    $BASE_LICENSE_DIR = "$BITROCK_BASE/license";
    $BASE_PROJECT_DIR= "/home/build/sugarsvn/build-bitrock_mango/Bitrock/project";
    $BASE_MYSQL_DIR = "$BITROCK_BASE/mysql";

    $INSTALL_BUILDER_EXE = "/opt/installbuilder-6.0.3/bin/builder";
    $OLD_INSTALL_BUILDER_EXE = "/opt/installbuilder-4.5.2/bin/builder";

    // define $BUILD_PLATFORM
    if ($PLATFORM != 'mssql') { $BUILD_PLATFORM = $PLATFORM; } else { $BUILD_PLATFORM = 'windows'; }

    $lpr->output( "+ Copying base files for $PLATFORM $FLAVOR version" );
    mkdir_recursive( "$TMP_DIR/$PLATFORM/$FLAVOR" );

    $lpr->output( "+--->  Made dir" );

    // Copying base files
      if ($PLATFORM == 'mssql') {
        $lpr->output( "+--->  Copying base files $PLATFORM to $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR" );
        system("svn export -q --force $FASTSTACK_SVN/base/$PLATFORM.zip $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$PLATFORM.zip");
        system("unzip -q $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$PLATFORM.zip -d $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$PLATFORM-$FLAVOR");
        system("mv $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$PLATFORM-$FLAVOR/$PLATFORM/* $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR");
      } else {
        $lpr->output( "+--->  Copying base files $BASE_BITROCK_DIR/$PLATFORM/ to $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR" );
        system("svn export -q --force http://svn1.sjc.sugarcrm.pvt/faststack/base/$PLATFORM $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR");
      }
      //copy new logo image
      system("cp -f /home/build/new_logo/*  $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR/images/");
      $lpr->output( "+--->  Finished copying base files" );
    // Copying correct sugarcrm version
      $lpr->output( "+--->  Copying sugarcrm"); 
    copy_sugarcrm($FLAVOR, $PLATFORM);
      $lpr->output( "+--->  Finished copy");    
    // Copying correct mysql version
    if ($PLATFORM != 'mssql') {
            if( $FLAVOR == 'ce' ){
                //system_copy_recursive( "$BASE_MYSQL_DIR/mysql-community/$BUILD_PLATFORM", "$TMP_DIR/$PLATFORM/$FLAVOR/mysql" );
                //system( "cp -r /home/build/bitrock-download/20080213/output-sugarcrm-windows-20080213/mysql $TMP_DIR/$PLATFORM/$FLAVOR/mysql");
                system("svn export -q --force http://svn1.sjc.sugarcrm.pvt/faststack/mysql/mysql-community/$PLATFORM $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR/mysql");
            }
            else{
                //system_copy_recursive( "$BASE_MYSQL_DIR/mysql-oem/$BUILD_PLATFORM", "$TMP_DIR/$PLATFORM/$FLAVOR/mysql");
                system("svn export -q --force http://svn1.sjc.sugarcrm.pvt/faststack/mysql/mysql-oem/$PLATFORM $BASE_BUILD_DIR/$TMP_DIR/$PLATFORM/$FLAVOR/mysql");
                }
        }

    // Copy Offline Client project file
    if ( ($FLAVOR == 'pro' || $FLAVOR == 'ent') && ($PLATFORM == 'windows' || $PLATFORM == 'mssql') ){
        system_copy( "$BASE_PROJECT_DIR/sugarcrm-offline-$FLAVOR.xml", "$TMP_DIR/$PLATFORM/$FLAVOR/sugarcrm-offline-client-$SUGAR_VERSION.xml" );
    }
    
$lpr->output( "+--->  Copying project files" );
    // Copying correct project file
    if ($PLATFORM == 'mssql' || $PLATFORM == 'windows') {
        system_copy( "$BASE_PROJECT_DIR/sugarcrm-$FLAVOR-$PLATFORM.xml", "$TMP_DIR/$PLATFORM/$FLAVOR/sugarcrm-$SUGAR_VERSION.xml" );
    } else {
        system_copy( "$BASE_PROJECT_DIR/sugarcrm-$FLAVOR.xml", "$TMP_DIR/$PLATFORM/$FLAVOR/sugarcrm-$SUGAR_VERSION.xml" );
    }
$lpr->output( "+--->  Done copying project files" );

$lpr->output( "Copying correct license files" );
    if ( $FLAVOR == 'ce' ) {
        system_copy( "$BASE_LICENSE_DIR/license-ce.txt", "$TMP_DIR/$PLATFORM/$FLAVOR/license.txt" );
    } else {
        system_copy( "$BASE_LICENSE_DIR/sugarsubagr.txt", "$TMP_DIR/$PLATFORM/$FLAVOR/license.txt" );
    }
 
    if ( $PLATFORM == 'mssql' ) {
        system("cat $BASE_LICENSE_DIR/pagebreak.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        system("cat $BASE_LICENSE_DIR/license-mssql2005.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        system("cat $BASE_LICENSE_DIR/pagebreak.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        system("cat $BASE_LICENSE_DIR/bitrock-mssql-20080129.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
    } elseif ( $PLATFORM == 'windows' ) {
        system("cat $BASE_LICENSE_DIR/pagebreak.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        system("cat $BASE_LICENSE_DIR/bitrock-windows-20080213.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
    } else {
        if ( ($FLAVOR == 'ce') ) {
            system("cat $BASE_LICENSE_DIR/pagebreak.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
            system("cat $BASE_LICENSE_DIR/bitrock-linux-20080129.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        } else {
            system("cat $BASE_LICENSE_DIR/pagebreak.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
            system("cat $BASE_LICENSE_DIR/bitrock-nomysql-20080129.txt >> $TMP_DIR/$PLATFORM/$FLAVOR/license.txt");
        }
    }
$lpr->output( "+--->  Done copying license files" );


    chdir( "$TMP_DIR/$PLATFORM/$FLAVOR" );

    // Running the installbuilder executable
     if ($FLAVOR == 'ce') {
    	$lpr->output( "+--->  Compiling using command: $INSTALL_BUILDER_EXE build ./sugarcrm-$SUGAR_VERSION.xml $BUILD_PLATFORM" );
    	system( "$INSTALL_BUILDER_EXE build ./sugarcrm-$SUGAR_VERSION.xml $BUILD_PLATFORM" );
    	$lpr->output( "+--->  Compiling done"  );
     }
    // Running the installbuilder executable for offline client
    if ( ($FLAVOR == 'pro' || $FLAVOR == 'ent') && ($PLATFORM == 'windows') ){
        $lpr->output( "+ Building Offline Client $FLAVOR version" );
        system( "$INSTALL_BUILDER_EXE build ./sugarcrm-offline-client-$SUGAR_VERSION.xml $PLATFORM" );   
    }

    chdir( "$BASE_BUILD_DIR/$ZIP_DIR" );

    if ($PLATFORM == 'osx'){
        // tar the file for an osx version of the installer

        system( "$TAR_CMD sugarcrm-".strtoupper($FLAVOR)."-$SUGAR_VERSION-$PLATFORM-installer.app.tgz sugarcrm-".strtoupper($FLAVOR)."-$SUGAR_VERSION-$PLATFORM-installer.app" );

        chdir( "$BASE_BUILD_DIR" );

        rmdir_recursive( "$BASE_BUILD_DIR/$ZIP_DIR/sugarcrm-".strtoupper($FLAVOR)."-$SUGAR_VERSION-$PLATFORM-installer.app" );
    }

    chdir( "$BASE_BUILD_DIR" );
}

# command line arg variables (and their defaults)
$CLEAN_ONLY       = 0;
$BUILD_OSX          = 1;
$BUILD_LINUX        = 1;
$BUILD_WINDOWS     = 1;
$BUILD_MSSQL       = 1;
$BUILD_SOLARIS = 0;
 
for( $iii = 1; $iii < $_SERVER['argc']; $iii++ ){
    $the_arg = $_SERVER['argv'][$iii];
    switch( $the_arg ){
        case "--help":
            usage();
            break;
        case "--clean-only":
            $CLEAN_ONLY     = 1;
            break;
        case "--skip-osx":
            $BUILD_OSX  = 0;
            break;
        case "--skip-linux":
            $BUILD_LINUX  = 0;
            break;
        case "--skip-windows":
            $BUILD_WINDOWS = 0;
            break;
        case "--skip-mssql":
            $BUILD_MSSQL = 0;
            break;
        case "--skip-solaris":
            $BUILD_SOLARIS = 0;
            break;
        case "--mail":
            $iii++;
            $MAIL_BUILD_TO = $_SERVER['argv'][$iii];
            $BASE_BUILD_DIR = "/home/build/$MAIL_BUILD_TO";
            break;
        case "--publish":
            $PUBLISH = true;
            break;
         case "--version":
             $iii++;
             $VER = $_SERVER['argv'][$iii];
             break;    
        default:
            print( "ERROR: Unknown option: $the_arg \n" );
            usage();
            break;
    }
}

require_once( "common.php" );
require_once( "utils/dir_inc.php" );
require_once( "utils/printer.php" );

set_time_limit( 0 );

$lpr    =& getPrinterInstance();
$BASE_BUILD_DIR = "/home/build/sugarbuild-$VER_SUFFIX";

clean();
if( $CLEAN_ONLY == 1 ){
    exit(0);
}
chdir("./project");
system("svn revert *; svn update");
$file_list = findAllFiles( ".", array() );
foreach ($file_list as $file) {
	$o = @file_get_contents($file);
	file_put_contents($file, str_replace("@_SUGAR_VERSION", "$SUGAR_VERSION", $o));
} 
chdir("..");
/*
system ("cd project; svn revert .; svn update");
system ("tmp_files=`find . -type f |xargs grep @_SUGAR_VERSION |grep -v .svn | awk '{print $1}'`;\
version_files=`for ii in $tmp_files; do echo ${ii%:*}; done |uniq`;\
for ii in  $version_files; do echo "There is the version file:$ii"; \
        sed -e "s/@_SUGAR_VERSION/$SUGAR_VERSION/g" <$ii >tmp; \
        mv tmp $ii; done" , $RC");
if( $RC ){
       $lpr->output( "ERROR replacing @_SUGAR_VERSION with: $SUGAR_VERSION." );
       exit( $RC );
}*/
if( !file_exists( $BASE_BUILD_DIR ) ){
    mkdir( $BASE_BUILD_DIR );
}

$lpr->setOutputFile( "$BASE_BUILD_DIR/$LOG_FILE" );
$lpr->output( "+--->  BUILD START" );

chdir( $BASE_BUILD_DIR );
if( !file_exists( $TMP_DIR ) ){
    mkdir( $TMP_DIR );
}
if( !file_exists( $ZIP_DIR ) ){
    mkdir( $ZIP_DIR );
}

if( $BUILD_OSX == 1 ){
    $OUTPUT = "output-osx-sugar";
    foreach( $BUILD_TYPES as $FLAVOR ){
        build_installer($FLAVOR, 'osx', $OUTPUT);
    }
    $lpr->output( "+ Completing OS X build." );
}
else{
    $lpr->output( "+ Skipping OS X build." );
}

if( $BUILD_LINUX == 1 ){
    $OUTPUT = "output-linux-sugar";
    foreach( $BUILD_TYPES as $FLAVOR ){
        build_installer($FLAVOR, 'linux', $OUTPUT);
    }
    $lpr->output( "+ Completing Linux build." );
}
else{
    $lpr->output( "+ Skipping Linux build." );
}

if( $BUILD_WINDOWS == 1 ){
    $OUTPUT = "output-windows-sugar";
    //foreach( $BUILD_TYPES as $FLAVOR ){
    //    build_installer($FLAVOR, 'windows', $OUTPUT);
    //}
    build_installer('ce', 'windows', $OUTPUT);
    build_installer('ent', 'windows', $OUTPUT);  // to build ent offline client
    build_installer('pro', 'windows', $OUTPUT);  // to build pro offline client
    $lpr->output( "+ Completing Windows build." );
}
else{
    $lpr->output( "+ Skipping Windows build." );
}

if( $BUILD_MSSQL == 1 ){
    $OUTPUT = "output-mssql-sugar";
    foreach( $BUILD_TYPES as $FLAVOR ){
        build_installer($FLAVOR, 'mssql', $OUTPUT);
    }
    $lpr->output( "+ Completing Windows-MSSQL build." );
}
else{
    $lpr->output( "+ Skipping Windows-MSSQL build." );
}

if( $BUILD_SOLARIS == 1 ){
    $OUTPUT = "output-solaris-intel-sugar";
    build_installer('ce', 'solaris-intel', $OUTPUT);
    $OUTPUT = "output-solaris-sparc-sugar";
    build_installer('ce', 'solaris-sparc', $OUTPUT);
    $lpr->output( "+ Completing Solaris build." );
}
else{
    $lpr->output( "+ Skipping Solaris build." );
}

if( $PUBLISH ){
    $lpr->output( "+---> Publishing builds..." );

    $STAMP=`date +"%F-%H-%M"`;
    $STAMP=trim( $STAMP );

    if( is_dir( "$PUBLISH_DIR/$STAMP" ) ){
        rmdir_recursive( "$PUBLISH_DIR/$STAMP" );
    }

    $lpr->output( "+ Creating dir: $PUBLISH_DIR/$STAMP" );
    mkdir_recursive( "$PUBLISH_DIR/$STAMP" );
    system( "cp $BASE_BUILD_DIR/$ZIP_DIR/*.* $PUBLISH_DIR/$STAMP" );
    system( "chmod go+rx $PUBLISH_DIR" );
    system( "chmod go+rx $PUBLISH_DIR/$STAMP" );
    system( "chmod go+r $PUBLISH_DIR/$STAMP/*.*" );

    $lpr->output( "+ Creating link to latest" );
    system( "rm -f $PUBLISH_DIR/latest" );
    system( "ln -s $STAMP $PUBLISH_DIR/latest" );

    # if we publish the build, we will send mail about it
//    $lpr->output( "+---> Sending mail regarding published builds..." );
//    mailBuildSuccess( $lpr->getOutputFile(), "builds/$VER_SUFFIX/$STAMP" );
}

$lpr->output( "+--->  BUILD FINISH" );
?>
