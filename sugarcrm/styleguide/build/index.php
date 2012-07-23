<h1>bootstrap.css builder</h1>
<h4>Click on a theme name to build bootstrap.css (styleguide only) according to the theme</h4>
<?php
$themesRoot = '../themes';
$clientsDir = array_diff( scandir($themesRoot . '/clients'), array(".", "..") );

$themes = array();

/**
 * Loop the themes/clients dir to list all platforms
 */
foreach ($clientsDir as $platform) {
    
    $themes[$platform] = array();
    $root = $themesRoot . '/clients/' . $platform;
    $themesDir = array_diff( scandir($root), array(".", "..") );

    /**
     * Loop the clients/__client__/ dir to list all themes
     */
    foreach ($themesDir as $theme) {
        if (!file_exists($root . '/' . $theme . '/variables.less')) break;

        $themes[$platform][] = $theme;
    }
}

/**
 * Display themes list as a ordered list.
 */
echo '<ol>';
foreach ($themes as $client => $themesArray) {
    echo '<li>'.$client;
    echo '<ol>';
    foreach ($themesArray as $theme) {
        $root = $themesRoot . '/clients/' . $client . '/' . $theme . '/';
        echo '<li><a href="buildcss.php?variables=' . $root . '">' . $theme . '</a></li>';
    }
    echo '</ol></li>';
}
echo '</ol>';
