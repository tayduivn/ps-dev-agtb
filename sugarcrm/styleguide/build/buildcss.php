<?php
require '../../vendor/lessphp/lessc.inc.php';

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}

if (!isset($_GET["variables"])) {
    die('You must specify a theme folder');
}

// Start generating bootstrap.css
try {
    $client = $_GET["client"];
    $root = $_GET["variables"];
    $variablesLess = file_get_contents($root . 'variables.less');
    $variables = getCustomThemeVars($variablesLess);
    $variables['baseUrl'] = '".."';
    $split_css = (isset($_GET["split_css"]) && $_GET["split_css"]=="true");
    $files = array();

    if ($split_css) {
        // Build bootstrap.css and sugar.css.
        $files[] = array(
            'in' => '../less/clients/' . $client . '/bootstrap.less',
            'out' => '../assets/css/bootstrap.css',
        );
        $files[] = array(
            'in' => '../less/clients/' . $client . '/sugar.less',
            'out' => '../assets/css/sugar.css',
        );
    } else {
        // Build bootstrap.css.
        $files[] = array(
            'in' => '../less/clients/' . $client . '/config.less',
            'out' => '../assets/css/bootstrap.css',
        );
    }

    // Build bootstrap-mobile.css.
    $files[] = array(
        'in' => '../less/clients/mobile/config.less',
        'out' => '../assets/css/bootstrap-mobile.css',
    );

    // Build utility CSS files.
    $modulesRoot = '../less/modules';
    $modulesFile = array_diff(scandir($modulesRoot), array(".", "..", ".DS_Store"));

    foreach ($modulesFile as $module) {
        if ( substr_count($module,'.less') ) {
            $files[] = array(
                    'in' => $modulesRoot . '/' . $module,
                    'out' => '../assets/css/' . str_replace('.less', '.css', $module),
            );
        }
    }

    // Set up the parser.
    $less = new lessc;
    $less->setVariables($variables);
    if (isset($_GET["min"]) && $_GET["min"]=="true") {
        $less->setFormatter("compressed");
    }

    // Compile the files.
    foreach ($files as $file) {
        $less->compileFile($file['in'], $file['out']);
    }

    // echo '<h2>bootstrap.css successfully generated.</h2>';
    // echo '<p><a href="./../styleguide/">Go to the styleguide</a></p>';
    // echo '<p><a href="./index.php">Back</a></p>';
    echo 'bootstrap.css successfully generated.';

} catch (Exception $ex) {
    exit('lessc fatal error:'.$ex->getMessage());
}

/**
 * Utility function to pull variables from a less file
 *
 * Same as /sugarcrm/include/api/ThemeApi.php
 * @param $variablesLess
 * @return array
 */
function getCustomThemeVars($variablesLess)
{
    $output = array();

    // Parses the hex colors     @varName:      #aaaaaa;
    $output = array_merge($output, parseFile("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess));
    // Parses the rgba colors     @varName:      rgba(0,0,0,0);
    $output = array_merge($output, parseFile("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess));
    // Parses the related colors     @varName:      @relatedVar;
    $output = array_merge($output, parseFile("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess));
    // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
    $output = array_merge($output, parseFile("/@([^:|@]+Path):(\s+)\"(.*?)\";/", $variablesLess));

    return $output;
}

/**
 * Utility function to parse a less file with a regex
 *
 * Same as /sugarcrm/include/api/ThemeApi.php
 * @param $regex
 * @param $input
 * @param bool $formatAsCollection
 * @return array
 */
function parseFile($regex, $input, $formatAsCollection = false)
{
    $output = array();
    preg_match_all($regex, $input, $match, PREG_PATTERN_ORDER);
    foreach ($match[1] as $key => $lessVar) {
        if ($formatAsCollection) {
            $output[] = array('name' => $lessVar, 'value' => $match[3][$key]);
        } else {
            $output[$lessVar] = $match[3][$key];
        }
    }
    return $output;
}
