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
    <link data-linkcss="bootstrap" href="../styleguide/css/bootstrap.css" rel="stylesheet">
</head>
<body>

<form method="GET" action="buildcss.php">
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
        <div class="subnav row-fluid">
            <div class="btn-toolbar pull-left">
                <div>

                    <input id="min-false" type="radio" name="min" value="false" checked>
                    <label style="display: inline;" for="min-false">Uncompressed</label>
                    <input id="min-true" type="radio" name="min" value="true">
                    <label style="display: inline;" for="min-true">Compressed</label>
                </div>
            </div>
            <div class="btn-toolbar pull-right">
              <div class="btn-group">
                <a class="btn btn-primary btn-submit" href="javascript:void(0)">Compile!</a>
              </div>
            </div>
          </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <?php
                /**
                 * Display themes list as a ordered list.
                 */
                foreach ($themes as $client => $themesArray) {
                    echo '<span class="cube" style="width:35px; height: 30px; margin-top: -7px;"></span>';
                    echo '<h3>' . $client . '</h3>';
                    echo '<table class="table table-bordered table-striped"><tbody>';
                    foreach ($themesArray as $theme) {
                        $checked = '';
                        if ($client == 'base' && $theme == 'default') $checked = ' checked';
                        $root = $themesRoot . '/clients/' . $client . '/' . $theme . '/';
                        echo '<tr>';
                        // echo '<div>';
                        echo '<td>';
                        echo '<input type="radio" name="variables" value="' . $root . '"' . $checked . '>';
                        echo '</td>';
                        echo '<td>';
                        echo '<code>' . $theme . '</code> ';
                        echo '</td>';
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
</form>
<script src="../styleguide/js/jquery-1.7.2.min.js"></script>
<script src="../bootstrap/js/bootstrap-modal.js"></script>
<script>
    $('.btn-submit').click(function() {
        var attrs = $('form').serialize();

        $.get('buildcss.php?' + attrs, function(data){
            $("#build-result .modal-body h4").text(data);
            $("#build-result").modal();

            $('link[data-linkcss=bootstrap]').remove();
            $("head").append("<link>");
                var css = $("head").children(":last");
                css.attr({
                  'data-linkcss': 'bootstrap',
                  rel:  "stylesheet",
                  type: "text/css",
                  href: '../styleguide/css/bootstrap.css?t=' + new Date().getTime()
                });
        });
    });
    $('tr').hover().css('cursor', 'pointer').click(function() {
        $(this).find('input[type=radio]').attr('checked', 'checked');
    });
</script>
<div id="build-result" class="modal hide fade in">
            <div class="modal-header">
              <a class="close" data-dismiss="modal">×</a>
              <h3>Result</h3>
            </div>
            <div class="modal-body">
              <h4></h4>
            </div>
            <div class="modal-footer">
              <a target="_blank" href="../styleguide" class="btn">Open styleguide</a>
              <a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
            </div>
          </div>
</body>
</html>