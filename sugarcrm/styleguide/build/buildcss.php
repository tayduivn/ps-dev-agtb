<?php
require 'lessc.inc.php';

if (!isset($_GET["variables"])) die('You must specify a theme folder');

// Start generating bootstrap.css
try {
    $root = $_GET["variables"];
    $variablesLess = file_get_contents( $root . 'variables.less' );
    $variables = getCustomThemeVars($variablesLess);
    $variables['baseUrl'] = '"../../bootstrap"';
    $less = new lessc('../bootstrap/less/bootstrap.less');
    file_put_contents('../styleguide/css/bootstrap.css', $less->parse($variables));
    echo '<h2>bootstrap.css successfully generated.</h2>';
    echo '<a href="./../styleguide/">Go to the styleguide</a>';

} catch (exception $ex) {
    exit('lessc fatal error:<br />'.$ex->getMessage());
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
    $output = array_merge( $output, parse_file("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess) );
    // Parses the rgba colors     @varName:      rgba(0,0,0,0);
    $output = array_merge( $output, parse_file("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess) );
    // Parses the related colors     @varName:      @relatedVar;
    $output = array_merge( $output, parse_file("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess) );
    // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
    $output = array_merge( $output, parse_file("/@([^:|@]+Path):(\s+)\"(.*?)\";/", $variablesLess) );

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
function parse_file($regex, $input, $formatAsCollection = false)
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