<html>
<head>
    <!-- required classes for tests -->
    <script type="text/javascript" src='../sidecar/lib/backbone/underscore.js'></script>
    <script type="text/javascript" src='../sidecar/lib/jquery/jquery.min.js'></script>
    <script type="text/javascript" src='../sidecar/lib/backbone/backbone.js'></script>
    <script type="text/javascript" src="../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
    <script type="text/javascript" src='../sidecar/lib/sugarapi/sugarapi.js'></script>
    <script type="text/javascript" src='../sidecar/minified/sidecar.min.js'></script>
    <script type="text/javascript" src='../config.js'></script>

    <!-- jasmine and sinon core files -->
    <script type="text/javascript" src='../sidecar/lib/sinon/sinon.js'></script>
    <script type="text/javascript" src='../sidecar/lib/jasmine/jasmine.js'></script>
    <script type="text/javascript" src='../sidecar/lib/jasmine/jasmine-html.js'></script>
    <script type="text/javascript" src='../sidecar/lib/jasmine-sinon/jasmine-sinon.js'></script>
    <script type="text/javascript" src='../sidecar/lib/jasmine-jquery/jasmine-jquery.js'></script>
    <script type="text/javascript" src="../sidecar/lib/jasmine-ci/jasmine-reporters/jasmine.phantomjs-reporter.js"></script>
    <script type="text/javascript" src='../sidecar/tests/spec-helper.js'></script>
    <script type="text/javascript" src='jshelpers/component-helper.js'></script>
    <link rel="stylesheet" href="../sidecar/lib/jasmine/jasmine.css"/>

    <!-- Fixtures -->
    <script type="text/javascript" src='../sidecar/tests/fixtures/api.js'></script>
    <script type="text/javascript" src='../sidecar/tests/fixtures/metadata.js'></script>
    <script type="text/javascript" src='../sidecar/tests/fixtures/language.js'></script>

    <!-- Begin test files -->
<?php

/**
 * jasmine test generator. This file will recursively search the test directory for .js test files and include them.
 */

    $exclude = array("jshelpers", "jssource", "PHPUnit", 'ci');

    $dirItr = new RecursiveDirectoryIterator('.');
    $itrItr = new RecursiveIteratorIterator($dirItr);
    foreach($itrItr as $path => $file) {
        if (substr(basename($path), -3) != ".js")
            continue;
        $skip = false;
        foreach($exclude as $ex){
            if (strpos($path, $ex) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;

        echo "<script type='text/javascript' src='$path'></script>\n";
    }
?>
    <!-- End test files -->
    <script type="text/javascript">
        SUGAR.App.config.syncConfig = false;
        (function () {
            var jasmineEnv = jasmine.getEnv();
            jasmineEnv.updateInterval = 1000;

            var trivialReporter = new jasmine.TrivialReporter();

            jasmineEnv.addReporter(trivialReporter);

            // Allows us to create report in JUnit XML Report format for CI.
            // I've observed no "slow down" when ran from browser ;=)
            jasmineEnv.addReporter(new jasmine.PhantomJSReporter());

            jasmineEnv.specFilter = function (spec) {
                return trivialReporter.specFilter(spec);
            };

            var currentWindowOnload = window.onload;

            window.onload = function () {
                if (currentWindowOnload) {
                    currentWindowOnload();
                }
                execJasmine();
            };

            function execJasmine() {
                jasmineEnv.execute();
            }
        })();
    </script>
</head>
<body>
<div></div>
</body>
</html>
