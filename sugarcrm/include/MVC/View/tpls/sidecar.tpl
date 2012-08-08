{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
*}

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=8, IE=9, IE=10" >
        <link rel="stylesheet" href="sidecar/lib/chosen/chosen.css"/>

        <!-- App Scripts -->
        <script src='sidecar/minified/sidecar.min.js'></script>
        <!-- <script src='sidecar/minified/sugar.min.js'></script> -->
        <script src='{$configFile}'></script>

        <!-- CSS -->
        <link rel="stylesheet" href="sidecar/lib/chosen/chosen.css"/>
        <link rel="stylesheet" href="include/javascript/twitterbootstrap/css/bootstrap.css"/>
        <link rel="stylesheet" href="sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">
    </head>
    <body>
    	<div id="sugarcrm">
			<div id="sidecar">
                <div id="alert" class="alert-top"></div>
                <div id="header"></div>
                <div id="content">
                    <div class="container welcome">
                        <div class="row">
                            <div class="span4 offset4 thumbnail">
                                <div class="modal-header tcenter">
                                    <img src="include/javascript/twitterbootstrap/img/throbber.gif"></img>
                                </div>
                                <div class="modal-footer">
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="footer"></div>
			</div>
		</div>
        {literal}
		<script language="javascript">
			var syncResult, view, layout, html;
			var App = SUGAR.App.init({
                el: "#sidecar",
                callback: function(app){
                    app.start();
                }
            });
            App.api.debug = App.config.debugSugarApi;
		</script>
        {/literal}
    </body>
</html>

