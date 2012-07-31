<?php
$themesRoot = '../themes';
$clientsDir = array_diff(scandir($themesRoot . '/clients'), array(".", "..", ".DS_Store"));

$themes = array();

/**
 * Loop the themes/clients dir to list all platforms
 */
foreach ($clientsDir as $platform) {

    $themes[$platform] = array();
    $root = $themesRoot . '/clients/' . $platform;
    $themesDir = array_diff(scandir($root), array(".", "..", ".DS_Store"));

    /**
     * Loop the clients/__client__/ dir to list all themes
     */
    foreach ($themesDir as $theme) {
        if (!file_exists($root . '/' . $theme . '/variables.less')) break;

        $themes[$platform][] = $theme;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Styleguide Builder - SugarCRM</title>
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href="../styleguide/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="nav-collapse">
                    <div class="container">
                        <ul class="nav">
                            <li>
                                <a class="brand" href="#">bootstrap.css builder</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /navbar-inner -->
        </div>
        <h4 class="subhead">Click on a theme name to build bootstrap.css (styleguide only) according to the theme</h4>

        <div class="container-fluid">
            <div class="row-fluid">
                <?php
                /**
                 * Display themes list as a ordered list.
                 */
                foreach ($themes as $client => $themesArray) {
                    echo '<h3>Platform: ' . $client . '</h3>';
                    echo '<table class="table table-bordered table-striped"><tbody>';
                    foreach ($themesArray as $theme) {
                        $root = $themesRoot . '/clients/' . $client . '/' . $theme . '/';
                        echo '<tr>';
                        // echo '<div>';
                        echo '<td>';
                        echo '<a href="buildcss.php?variables=' . $root . '">' . $theme . '</a> ';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="btn-group"><a class="btn" href="buildcss.php?min=true&variables=' . $root . '">Compressed</a>';
                        echo '<a class="btn btn-primary" href="buildcss.php?variables=' . $root . '">Uncompressed</a>';
                        echo '</div></td>';
                        echo '<td>';
                        echo '<small>last modified: ' . date("n/j/y \a\\t H:i:s.", filemtime($root . '/variables.less')) . '</small>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>
                      </table>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>