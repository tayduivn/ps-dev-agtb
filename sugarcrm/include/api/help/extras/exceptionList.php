<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * This file is here to provide a HTML template for the rest help api.
 */

$theme = new SidecarTheme();

$bootstrap_css = $theme->getCSSURL();

// Fixes pathing for help requests ending in '/'
$base_path = '../../../';
$link_path = '../';
if (substr($_SERVER['REQUEST_URI'], -1) == '/') {
    $base_path .= '../';
    $link_path .= '../';
}
?>

<!DOCTYPE HTML>
<html>

    <head>
        <title>SugarCRM Auto Generated API Exceptions Documentation</title>
        <?php
        foreach($bootstrap_css as $css) {
            echo '<link rel="stylesheet" href="' . $base_path . $css . '">';
        }
        ?>
        <style>

            body {
                padding: 5px;
            }

            .container-fluid div{
                background-color: @NavigationBar;
            }

            .line{
                border-bottom: 1px solid black;
            }

            .score{
                text-align: right;
            }

            .pre-scrollable {
                width: 600px;
                background-color: white;
                color: red;
            }

            .table {

                background-color: white;
            }

            .table td {
                white-space: normal;
                word-wrap: break-word;
            }

            h2{
                padding-top: 30px;
            }

            .well-small {
                background-color: white;
            }

            .alert {
                padding: 20px;
                text-align: center;
            }

        </style>

        <?php foreach ($jsfiles as $file): ?>
        <script type="text/javascript" src="<?php echo $base_path . $file ?>"></script> 
        <?php endforeach; ?>
    </head>

    <body>

        <h2>SugarCRM API Exceptions</h2>

        <p><a href="<?php echo $link_path ?>help">SugarAPI help documentation</a></p>
        <div class="container-fluid">

            <div class="row-fluid">

                <div class="span2"><h1>Type</h1></div>
                <div class="span1"><h1>HTTP Code</h1></div>
                <div class="span4"><h1>Description</h1></div>
                <div class="span2"><h1>Default error code</h1></div>
                <div class="span3"><h1>Default error message</h1></div>
            </div>

        <?php foreach ($exceptions as $exception): ?>

            <div class="row-fluid line" id="<?php echo $exception['element_id'] ?>">
                <div class="row-fluid">
                    <div class="span2">
                        <?php echo $exception['type'] ?>
                    </div>
                    <div class="span1">
                        <?php echo $exception['code'] ?>
                    </div>
                    <div class="span4">
                        <?php echo $exception['desc'] ?>
                    </div>
                    <div class="span2">
                        <?php echo $exception['label'] ?>
                    </div>
                    <div class="span3">
                        <?php echo $exception['message'] ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>

        </div>
    </body>
</html>
