<?php
$time = microtime(true);
require('Rome.php');
$config = getConfig();

$rome = new Rome();
$exculsive = !empty($config['exclusive']);
if (!empty($config['regions'])) {
    $rome->setRegions($config['regions'], $exculsive);
}
if (!empty($config['deployments'])) {
    $rome->setDeployments($config['deployments'], $exculsive);
}
if (!empty($config['flav'])) {
    $rome->setFlav($config['flav']);
} elseif (!empty($config['skipFlavs'])) {
    $rome->setSkipFlavs($config['skipFlavs']);
}
if (!empty($config['ver'])) {
    $rome->setVersion($config['ver']);
}

if (!empty($config['exclude_dirs'])) {
    $rome->setExcludeDirs(explode(",", $config['exclude_dirs']));
}

if (!empty($config['build_dir'])) {
    $rome->setBuildDir($config['build_dir']);
}
if (!empty($config['dir']) && is_file($config['dir']) && empty($config['file'])) {
    $config['file'] = $config['dir'];
    unset($config['dir']);
}
if (!empty($config['clean'])) {
    if (!empty($config['file'])) {
        foreach ($config['builds'] as $build) {
            $rome->remove($rome->getBuildDir(). DIRECTORY_SEPARATOR .$build. DIRECTORY_SEPARATOR .$config['file']);
        }
    } elseif (!empty($config['dir'])) {
        foreach ($config['builds'] as $build) {
            $rome->remove($rome->getBuildDir(). DIRECTORY_SEPARATOR .$build. DIRECTORY_SEPARATOR .$config['dir']);
        }
    } else {
        $rome->remove($rome->getBuildDir());
    }
}

if (!empty($config['ver'])) {
    $rome->setVersion($config['ver']);
}
if (!empty($config['cleanCache'])) {
    foreach ($config['builds'] as $build) {
        $path = $rome->getBuildDir();
        $path .= "/$build";

        if (is_dir($path . "/sugarcrm")) {
            $path .= "/sugarcrm";
        }
        $rome->remove($path . "/cache/file_map.php");
        $rome->remove($path . "/cache/api");
        $rome->remove($path . "/cache/jsLanguage");
        $rome->remove($path . "/cache/modules");
        $rome->remove($path . "/cache/smarty");
        $rome->remove($path . "/cache/Expressions");
        //Only clear themes if we have touched a file that affects them
        if (empty($config['file']) || preg_match('/\.css|styleguide|\.less|themes/i', $config['file'])) {
            $rome->remove($path ."/cache/themes");
        }
        $rome->remove($path . "/cache/blowfish");
        $rome->remove($path . "/cache/dashlets");
        $rome->remove($path . "/cache/include/api");
        $rome->remove($path . "/cache/javascript");
        $rome->remove($path . "/cache/include/javascript");
        $rome->remove($path . "/cache/include/javascript/sugar_grp1.js");
        $rome->remove($path . "/cache/include/javascript/sugar_grp1_yui.js");
    }
}


if(!empty($config['retainCommentSpacing']))
{
    $rome->setRetainCommentSpacing($config['retainCommentSpacing']);
}

if (!empty($config['base_dir'])) {
    $config['base_dir'] = realpath($config['base_dir']);

    if (!empty($config['file'])) {
        if (file_exists($config['file'])) {
            $config['file'] = realpath($config['file']);
        } else {
            $config['file'] = realpath($config['base_dir'] . DIRECTORY_SEPARATOR . $config['file']);
        }
        $config['file'] = str_replace($config['base_dir'] . DIRECTORY_SEPARATOR, '', $config['file']);
        if (is_file($config['base_dir'] . DIRECTORY_SEPARATOR . $config['file'])) {
            $rome->setStartPath($config['base_dir']);
            echo "Building " . $config['base_dir'] . DIRECTORY_SEPARATOR . $config['file'];
            $rome->buildFile($config['base_dir'] . DIRECTORY_SEPARATOR . $config['file'], "");
        } else {
            $config_path = $config['base_dir'] . DIRECTORY_SEPARATOR . $config['file'];
            echo "Build Stopped.  You entered an invalid file name: $config_path";
        }
    } elseif (!empty($config['dir'])) {
        if (file_exists($config['dir'])) {
            $config['dir'] = realpath($config['dir']);
        } else {
            $config['dir'] = realpath($config['base_dir'] . DIRECTORY_SEPARATOR . $config['dir']);
        }
        $config['dir'] = str_replace($config['base_dir'], '', $config['dir']);
        if (is_dir($config['base_dir'] . DIRECTORY_SEPARATOR . $config['dir'])) {
            $rome->setStartPath($config['base_dir']);
            echo "Building " . $config['base_dir'] . DIRECTORY_SEPARATOR . $config['dir'];
            $rome->build($config['base_dir'] . DIRECTORY_SEPARATOR . $config['dir']);
        } else {
            $config_path = $config['base_dir'] . DIRECTORY_SEPARATOR . $config['dir'];
            echo "Build Stopped.  You entered an invalid directory name: " . $config_path;
        }
    } else {
        echo "Building " . $config['base_dir'];
        $path = $config['base_dir'];
        $rome->build("$path");
    }

    // this is placed here because after sidecar build this is not executed...
    if (!empty($config['mobile'])) {
        echo "\nBuilding Mobile\n\n";
        require_once('Mobile.php');
        $mobile = new Mobile(
            $rome,
            $config['nomad_repo_uri'],
            $config['mobile_branch'],
            $config['ver'],
            $config['builds'],
            $config['mobile_scheme'],
            $config['mobile_src_dir']
        );
    }

    if (!empty($config['latin'])) {
        if (!empty($config['langs'])) {
            $langs = explode(",", $config['langs']);
        } else {
            $langs = null;
        }

        echo "\nImporting Languages\n\n";
        $no_latin_scm = !empty($config['no-latin-scm']);
        require_once('Latin.php');
        $latin = new Latin(
            $rome,
            $config['languages']['gitPath'],
            $config['base_dir'],
            $config['ver'],
            $no_latin_scm,
            $langs
        );
        $latin->copyTranslations();
    }

    if (!empty($config['dir'])) {
        $build_dir = $rome->getBuildDir();

        foreach ($config['builds'] as $flav) {
            $dir = $build_dir . '/' . $flav . $config['dir'];
            $rome->generateMD5($dir);
        }
    }
} else {
    $rome->throwException("No Base Directory To Build From", true);
}

$total = microtime(true) - $time;
echo "\n\n" . 'TOTAL TIME: ' . $total . "\n";


function getConfig()
{
    //if (!isset($_SERVER['argv'])) {
    //    $argv = $_SERVER['argv'];
    //}
    //$argv = $GLOBALS['argv'];

    global $argv;
    include('config/runtime.config.php');
    if (!isset($config)) {
        $config = array();
    }
    if (isset($argv)) {
        //array_shift($argv);
        foreach ($argv as $arg) {
            if (substr($arg, 0, 2) == '--') {
                $arg = substr($arg, 2, strlen($arg));
            }
            if (substr($arg, 0, 1) == '-') {
                $arg = substr($arg, 1, strlen($arg));
            }
            $params = explode('=', $arg);
            if (count($params) > 1) {
                $config[$params[0]] = $params[1];
            } else {
                $config[$params[0]] = true;
            }
        }
    }
    if (isset($config['help'])) {
        getHelp();
        exit(0);
    }
    $d = dir('config/builds');
    $flavs = array();
    while ($e = $d->read()) {
        $path = 'config/builds/' . $e;
        if (is_file($path) && substr($e, 0, 6) == 'config') {
            array_push($flavs, substr($e, 7, strpos($e, '.', 8)-7));
        }
    }
    if (!empty($config['flav'])) {
        $f_flag = 0; // 0- no match all pre-config flavs ; 1-match one flav
        foreach ($flavs as $f) {
            if ($config['flav']==$f) {
                $f_flag=1;
                $config['builds'][] = $f;
            }
        }
        if (!$f_flag) {
            echo "Build Stopped. You entered an invalid flav name:" . $config['flav'] . "\n";
            exit(1);
        }
    } else {
        foreach ($flavs as $f) {
            $config['builds'][] = $f;
        }
    }
    return $config;
}

function getHelp()
{
    echo <<<END
Build options:

-—ver [required]
    specifies the version number to include in the build. All references
    in the application to a “version number" will indicate whatever
    version is specified.

    php build.php --ver=6.1.0

    The above example will replace all references to a version in
    the application with 6.1.0

-—flav
    specifies the flavor to build. It will only build and update files
    related to that flavor. For a full list of available flavors look in
    the config/builds directory. It reduces build time and is useful when
    doing development.

    php build.php --ver=6.1.0 --flav=ent

    Will only build the Enterprise version of the application.
    —-flav=pro builds the Professional and -—flav=com will build the Community edition.

-—clean
    Will tell the build system to either delete files before building or not.
    —-clean will delete files before building

    php build.php --ver=6.1.0 --clean

    -—clean=0 will tell the system not to delete files before building.
    This is useful when you want to preserve your config.php file

-—cleanCache
    Clears the cache before doing the build. This will only delete certain
    cache files before doing a build.

    php build.php --ver=6.1.0 --cleanCache

    Will clean the cache directory.

-—dir
    Build a specific directory or file rather than building the entire
    application.

    php build.php --ver=6.1.0 --dir=sugarcrm/modules/Accounts

    Will build only the Accounts directory

--exclude_dirs
    Exclude list of directories rather than building them.

    php build.php --ver=6.1.0 --exclude_dirs=node_modules[,vendor,...]

    Will exclude node_modules from the build process

—-build_dir
    If you would like to change where the files are built to, specify
    —-build_dir with that you would like

    php build.php --ver=6.1.0 --build_dir=/tmp

    Will specify for all flavors to be built to the tmp directory

-—base_dir
    If you want to call on build.php from outside of the rome directory,
    you must specify your project location using —-base_dir

    php build.php --ver=6.1.0 --base_dir=/Users/mitani/code/Mango

--latin
    Add language files to the build.

--langs[=de_DE,it_it,...]
    Will build Sugar using only specified languages.
    Defaults to all languages. Requires latin flag.

--no-latin-scm
    Do not execute source control management commands to update
    translations, assume that translations have already been updated.

    php build.php --ver=6.1.0 --flav=ent --latin=1
--mobile
    Build mobile version under {BUILD_PATH}/sugarcrm/mobile

--mobile_scheme
    Mobile build scheme (qa, prod, dev)
    php build.php --mobile --mobile_scheme qa

--mobile_branch
    Nomad repository branch
    php build.php --mobile --mobile_branch v1_3

--mobile_src_dir
    Directory where to clone Nomad repository. Defaults to Sugar build path
    php build.php --mobile --mobile_src_dir /Volumes/WORK/SugarCRM/releases

See also: https://github.com/sugarcrm/Mango/wiki/SugarCRM-Build-System

END;
}
