<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
?>
<html>
<head>
<title>SugarCRM Upgrader</title>
<meta name="viewport" content="initial-scale=1.0">
<meta name="viewport" content="user-scalable=no, width=device-width">
<script src='include/javascript/jquery/jquery-min.js'></script>
<script src='sidecar/lib/jquery/jquery.iframe.transport.js'></script>
<style>
body {
    padding: 20px;
    font: 13px arial, sans-serif;
    background-color: #ffffff;
    text-align: center
}

.btn {
    display: block;
    color: #fff;
    padding: 10px;
    position: relative;
    margin: 10px 5px 5px;
    text-align: center;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    border-radius: 2px;
    background-color: #f8f8f8;
    background-image: -moz-linear-gradient(top, #f8f8f8, #f4f4f4);
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f8f8f8), to(#f4f4f4));
    background-image: -webkit-linear-gradient(top, #f8f8f8, #f4f4f4);
    background-image: -o-linear-gradient(top, #f8f8f8, #f4f4f4);
    background-image: linear-gradient(to bottom, #f8f8f8, #f4f4f4);
    background-repeat: repeat-x;
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff4f4f4', endColorstr='#fffffff', GradientType=0);
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 1), 0 1px 1px rgba(0, 0, 0, 0.4);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 1), 0 1px 1px rgba(0, 0, 0, 0.4);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 1), 0 1px 1px rgba(0, 0, 0, 0.4);
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box
}

.progress {
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    height: 10px;
    position: absolute;
    right: 10px;
    top: 11px;
    width: 150px;
    overflow: hidden;
    background-color: #f8f8f8;
    -webkit-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
    -moz-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
    box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
}

.hide {
    display: none;

}

.progress .bar {
    float: left;
    width: 0;
    height: 10px;
    font-size: 8px;
    opacity: .6;
    color: #fff;
    text-align: center;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    background-color: #0e90d2;
    background-image: -moz-linear-gradient(top, #149bdf, #0480be);
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#149bdf), to(#0480be));
    background-image: -webkit-linear-gradient(top, #149bdf, #0480be);
    background-image: -o-linear-gradient(top, #149bdf, #0480be);
    background-image: linear-gradient(to bottom, #149bdf, #0480be);
    background-repeat: repeat-x;
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff149bdf', endColorstr='#ff0480be', GradientType=0);
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15), 1px 0 2px rgba(0, 0, 0, 0.4);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15), 1px 0 2px rgba(0, 0, 0, 0.4);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15),, 1px 0 2px rgba(0, 0, 0, 0.4);
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box
}

.progress-success .bar, .progress .bar-success, .btn.btn-success {
    background-color: #5eb95e;
    background-image: -moz-linear-gradient(top, #62c462, #57a957);
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62c462), to(#57a957));
    background-image: -webkit-linear-gradient(top, #62c462, #57a957);
    background-image: -o-linear-gradient(top, #62c462, #57a957);
    background-image: linear-gradient(to bottom, #62c462, #57a957);
    background-repeat: repeat-x;
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff62c462', endColorstr='#ff57a957', GradientType=0)
}

.progress-warn .bar, .progress .bar-warn {
    opacity: 1;
    background-color: #faa732;
    background-image: -moz-linear-gradient(top, #fbb450, #f89406);
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fbb450), to(#f89406));
    background-image: -webkit-linear-gradient(top, #fbb450, #f89406);
    background-image: -o-linear-gradient(top, #fbb450, #f89406);
    background-image: linear-gradient(to bottom, #fbb450, #f89406);
    background-repeat: repeat-x;
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fffbb450', endColorstr='#fff89406', GradientType=0)
}

.progress-error .bar, .progress .bar-error {
    opacity: 1;
    background-color: #dd514c;
    background-image: -moz-linear-gradient(top, #ee5f5b, #c43c35);
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ee5f5b), to(#c43c35));
    background-image: -webkit-linear-gradient(top, #ee5f5b, #c43c35);
    background-image: -o-linear-gradient(top, #ee5f5b, #c43c35);
    background-image: linear-gradient(to bottom, #ee5f5b, #c43c35);
    background-repeat: repeat-x;
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffee5f5b', endColorstr='#ffc43c35', GradientType=0)
}

.check {
    height: 10px;
    position: absolute;
    left: 10px;
    top: 11px;
    width: 10px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background-color: #f8f8f8;
    -webkit-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
    -moz-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
    box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), inset 0 1px 1px rgba(0, 0, 0, .1);
}

.progress .checker {
    opacity: 1
}

.box {
    color: #444;
    padding: 10px;
    position: relative;
    margin: 5px auto;
    width: 300px;
    text-align: center;
    -webkit-border-radius: 2px;
    -moz-border-radius: 2px;
    border-radius: 2px;
    background-color: #ffffff;
    -webkit-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), 0 2px 1px rgba(0, 0, 0, .1);
    -moz-box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), 0 2px 1px rgba(0, 0, 0, .1);
    box-shadow: inset 0 -1px 10px rgba(0, 0, 0, .1), 0 0 1px rgba(0, 0, 0, .8), 0 2px 1px rgba(0, 0, 0, .1);
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box
}

.box strong {
    color: #666;
    text-align: left;
    width: 100px;
    display: block;
    padding-left: 20px;
    font-size: 11px
}

.box p {
    font-size: 13px;
    display: block;
    padding: 0;
    margin: 5px 0;
    line-height: 16px
}

.done strong {
    color: #000
}

.done .progress .bar {
    opacity: 1
}

h1 {
    font-size: 16px
}

h2 {
    font-size: 11px
}

small {
    font-size: 11px;
    font-weight: 700
}

a {
    color: #62c462;
    text-decoration: none
}

a:hover {
    color: #57a957;
    text-decoration: underline
}

.alert {
    position: absolute;
    top: 20px;
    left: 10px;
    width: 50%;
    margin: 10px auto;
    height: 24px;
    -webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    -moz-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}

.alert .bar {
    opacity: 1;
    font-size: 11px;
    padding: 5px 30px 5px 10px;
    text-align: left;
    height: 24px;
    font-weight: bold
}

.close {
    position: absolute;
    right: 10px;
    top: 4px;
    font-weight: bold;
    opacity: .3
}

.close:hover {
    opacity: .6
}

@media (max-width:767px) {
    body {
        -webkit-transform: translate3d(0, 0, 0);
        padding: 20px 0;
    }

    .box {
        margin-left: 0;
        margin-right: 0;
        width: 100%
    }

    .alert {
        width: 100%;
        left: 0;
        top: 0
    }
}
// License dialog
      html {
        overflow-y: scroll;
      }

      .has-overlay {
        overflow: hidden;
      }
      .has-overlay > body {
        height: 100%;
        overflow-y: scroll;
      }

      .semantic-content {
        -webkit-transform: translate(0, 100%);
        -moz-transform: translate(0, 100%);
        -o-transform: translate(0, 100%);
        -ms-transform: translate(0, 100%);
        transform: translate(0, 100%);
        -webkit-transform: translate3d(0, 100%, 0);
        transform: translate3d(0, 100%, 0);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 999;
        opacity: 0;
        color: #222;
        line-height: 1.3;
        display: none\9;
      }
      .semantic-content:target {
        -webkit-transform: translate(0, 0);
        -moz-transform: translate(0, 0);
        -o-transform: translate(0, 0);
        -ms-transform: translate(0, 0);
        transform: translate(0, 0);
        opacity: 1;
      }
      .is-active.semantic-content {
        display: block\9;
      }
      .semantic-content:target {
        display: block\9;
      }
      .semantic-content .modal-inner {
        position: absolute;
        top: 50px;
        left: 50%;
        z-index: 20;
        margin-left: -325px;
        width: 650px;
        overflow-x: hidden;
        border-radius: 2px;
        background: #fff;
        -webkit-box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
      }
      .semantic-content .modal-inner > img,
      .semantic-content .modal-inner > video,
      .semantic-content .modal-inner > iframe {
        width: 100%;
        height: auto;
        min-height: 300px;
      }
      .semantic-content .modal-inner > img {
        width: auto;
        max-width: 100%;
      }
      .semantic-content header {
        border-bottom: 1px solid #dddddd;
        padding: 0 1.2em;
      }
      .semantic-content header > h2 {
        margin: 0.5em 0;
      }
      .semantic-content .modal-content {
        max-height: 400px;
        max-height: 70vh;
        border-bottom: 1px solid #dddddd;
        padding: 15px 1.2em;
        overflow-x: hidden;
        overflow-y: auto;
      	text-align: justify;
      }
      .semantic-content .modal-content > * {
        max-width: 100%;
      }
      .semantic-content footer {
        padding: 0;
        background: #fff;
        border-radius: 2px;
        border-bottom: 1px solid #dddddd;
      }
      .semantic-content footer a:hover {
        background: #f0f0f0;
        color: #111;
      }
      .semantic-content .modal-close {
        display: block;
        text-indent: -100px;
        overflow: hidden;
      }
      .semantic-content .modal-close:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10;
        background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAEElEQVR42gEFAPr/AAAAAMwA0QDNTiUx4gAAAABJRU5ErkJggg==");
      }
      .semantic-content .modal-close:after {
        content: '\00d7';
        position: absolute;
        top: 55px;
        left: 50%;
        z-index: 20;
        margin-left: 285px;
        background: #fff;
        border-radius: 2px;
        padding: 6px 10px;
        font-size: 24px;
        text-decoration: none;
        text-indent: 0;
      }
      @media screen and (max-width: 690px) {
        .semantic-content .modal-inner {
          width: auto;
          left: 20px;
          right: 20px;
          margin-left: 0;
        }
        .semantic-content .modal-close {
          left: auto;
          right: 33px;
          margin-left: 0;
        }
        .semantic-content .modal-close:after {
          margin-left: 40%;
        }
      }
      @media screen and (max-width: 30em) {
        .semantic-content {
          -webkit-transform: translate(0, 400px);
          -webkit-transform: translate3d(0, 100%, 0);
          transform: translate3d(0, 100%, 0);
          -webkit-transition: -webkit-transform .25s ease-in-out, opacity 1ms .25s;
          -moz-transition: -moz-transform .25s ease-in-out, opacity 1ms .25s;
          -o-transition: -o-transform .25s ease-in-out, opacity 1ms .25s;
          -ms-transition: -ms-transform .25s ease-in-out, opacity 1ms .25s;
          transition: transform .25s ease-in-out, opacity 1ms .25s;
          display: block;
          height: 100%;
          bottom: auto;
        }
        .semantic-content:target {
          -webkit-transition: -webkit-transform .25s ease-in-out;
          -moz-transition: -moz-transform .25s ease-in-out;
          -o-transition: -o-transform .25s ease-in-out;
          -ms-transition: -ms-transform .25s ease-in-out;
          transition: transform .25s ease-in-out;
        }
        .semantic-content .modal-inner {
          -webkit-box-sizing: border-box;
          -moz-box-sizing: border-box;
          box-sizing: border-box;
          top: 0;
          left: 0;
          right: 0;
          height: 100%;
          overflow: scroll;
          -webkit-box-shadow: none;
          box-shadow: none;
        }
        .semantic-content .modal-content {
          max-height: none;
        }
        .semantic-content .modal-close {
          display: none;
        }
      }
      @media screen and (max-height: 46em) and (min-width: 30em) {
        .semantic-content .modal-content {
          max-height: 340px;
          max-height: 50vh;
        }
      }
      @media screen and (max-height: 36em) and (min-width: 30em) {
        .semantic-content .modal-content {
          max-height: 265px;
          max-height: 40vh;
        }
      }

      @media screen and (min-width: 30em) {
        .semantic-content {
          -webkit-transition: opacity 0.4s;
          -o-transition: opacity 0.4s;
          transition: opacity 0.4s;
        }
      }

      .semantic-content h1 {font-size: 24px; margin: 0; padding: 10px 0;}
      .semantic-content h2, .semantic-content h3 {font-size: 12px;}
      .semantic-content footer a {display: inline-block; width: calc(50%); text-align: center; padding: 15px 0; text-decoration: none; color: #555; text-decoration: none; color: #555;}
      .semantic-content .left-pedal {width: calc(50% - 1px); border-right: 1px solid #ccc;}
      .semantic-content .right-pedal {width: calc(50%);}
</style>
<!--[if lt IE 10]>
<style type="text/css">
    .box strong {
        float: left;
    }
</style>
<![endif]-->

<script>
    if (top !== self) {
        top.location.href = location.href;
    }

    $(window).bind("load", function () {

            var uploader = {token: "<?php echo $token ?>"};
            uploader.hideError = function () {
                $('#errorBox').addClass('hide');
                $('.bar-error').removeClass('bar-error');
            };
            uploader.displayError = function (error) {
                $('#errorBox').removeClass('hide')
                $('#errorBox').find('div').addClass('bar-error').text(error);
                $('#' + uploader.stages[uploader.stage] + 'Bar').addClass('bar-error');
                $('#' + uploader.stages[uploader.stage] + 'MicroBar').width('100%').addClass('bar-error').removeClass('bar-success');
                uploader.clearStatusUpdate();
            };
            uploader.updateProgress = function (bar, percent) {

                if (uploader.stage == -1) {
                    debugger;
                    return;
                }
                $('#upgradeTitle').text('Upgrade Progress ' + uploader.stage + ' of ' + uploader.stages.length);
                if (percent == 100) {
                    $('#' + bar + 'Bar').addClass('bar-success');
                    $('#' + bar + 'MicroBar').addClass('bar-success');
                } else {
                    $('#' + bar + 'Bar').removeClass('bar-success');
                    $('#' + bar + 'MicroBar').removeClass('bar-success');
                }
                $('#' + bar + 'Bar').width(percent + '%');
                $('#' + bar + 'MicroBar').width(percent + '%');
            }
            uploader.STATUS_FREQ = 1000;
            uploader.statusUpdates = false;
            uploader.acceptedLicense = false;
            uploader.stage = 0;
            uploader.stages = ['unpack', 'pre', 'commit', 'post', 'cleanup'];
            uploader.counterStages = ['pre', 'post'];
            uploader.updateStatus = function () {
                $.ajax({
                    type: 'POST',
                    url: 'UpgradeWizard.php',
                    data: {
                        token: uploader.token,
                        action: 'status'
                    },
                    dataType: 'json',
                    success: function (e) {
                        if (uploader.statusUpdates) {
                            if(e.data.script_count) {
                              for(var i in e.data.script_count){
                                  uploader.updateProgress(i, Object.keys(e.data.scripts[i]).length/ e.data.script_count[i] * 100);
                              }
                            }
                            uploader.setNextStatusUpdate();
                        }
                    }

                });

            }
            uploader.setNextStatusUpdate = function () {
                uploader.statusUpdates = true;
                uploader.updateInterval = setTimeout(uploader.updateStatus, uploader.STATUS_FREQ);
            }
            uploader.clearStatusUpdate = function () {
                uploader.statusUpdates = false;
                if (uploader.updateInterval) {
                    clearTimeout(uploader.updateInterval);
                }
            }


            uploader.executeStage = function () {
                uploader.hideError();
                $.ajax({
                    type: 'POST',
                    url: 'UpgradeWizard.php',
                    data: {
                        token: uploader.token,
                        action: uploader.stages[uploader.stage]
                    },
                    dataType: 'json',
                    success: function (e) {
                        if (e.status == 'error' || e.status == undefined) {
                            uploader.displayError(e.message || "A server error occurred, please check your logs");
                            $('#' + uploader.stages[uploader.stage] + 'Bar').addClass('bar-error');
                        } else {
                            if (e.data === true) {
                                uploader.clearStatusUpdate();
                                uploader.updateProgress(uploader.stages[uploader.stage], 100);
                                $('#upgradeTitle').text('Upgrade Complete');
                                $('#successBox').removeClass('hide');
                            } else {
                                uploader.stage = uploader.stages.indexOf(e.data);

                                if (uploader.stage > 0) {
                                    uploader.updateProgress(uploader.stages[uploader.stage - 1], 100);
                                } else {
                                	uploader.clearStatusUpdate();
                                }
                                var percentComplete = 0;
                                if(uploader.counterStages.indexOf(e.data) == -1){
                                    percentComplete = 25;
                                }
                                uploader.updateProgress(e.data, percentComplete);
                                uploader.executeStage();
                            }

                        }
                    },
                    error: function (e) {
                        uploader.displayError("A server error occurred, please check your logs");
                        $('#' + uploader.stages[uploader.stage] + 'Bar').addClass('bar-error');
                    }



                })

            }
            ;


            $('#uploadForm').submit(function (evt) {
                uploader.hideError();
                evt.preventDefault();
                if(!$('#uploadBox input[type=file]')[0].value) {
                	uploader.displayError("Please select upgrade package file");
                	return;
                }
                uploader.stage = uploader.stages.indexOf('unpack');
                $('#uploadBox').addClass('hide');
                $('#progressBox').removeClass('hide');
                uploader.updateProgress('unpack', 25);

                $.ajax('UpgradeWizard.php', {
                        data: $(":hidden", this).serialize(),
                        files: $(":file", this),
                        iframe: true,
                        processData: false,
                        error: function (e) {
                            uploader.displayError("A server error occurred, please check your logs");
                        }
                    }
                ).complete(function (data) {

                        try {
                            var response = $.parseJSON(data.responseText);
                            if (response.status == 'error') {
                                $('#uploadBox').removeClass('hide');
                                uploader.displayError(response.message);
                            } else {

                                uploader.stage = uploader.stages.indexOf(response.data);
                                uploader.updateProgress('unpack', 100);
/*                              License display disabled for now.
                                if(response.license || response.readme) {
                                    uploader.displayLicense(response);
                                } else { */
                                    uploader.executeStage();
                                    uploader.setNextStatusUpdate();
                                //}
                            }
                        } catch (e) {
                            $('#uploadBox').removeClass('hide');
                            uploader.displayError(data);

                        }

                    });

            });

            uploader.displayLicense = function(response) {
            	window.location.hash = 'modal-text';
            	$('#licenseText').text(response.license || response.readme);
            	window.addEventListener('hashchange', function(e) {
                    var hash = window.location.hash.replace('#', '');
            	    if(hash == 'accepted') {
                        window.removeEventListener('hashchange', arguments.callee);
                        if(uploader.acceptedLicense) {
                            // ensure we launch the rest of the upgrade only once
                            return;
                        } else {
                            uploader.acceptedLicense = true;
                        }
                        $('#uploadBox').addClass('hide');
                        $('#progressBox').removeClass('hide');
                        uploader.updateProgress('unpack', 100);
                        uploader.executeStage();
                        uploader.setNextStatusUpdate();
            	    } else if(hash == 'modal-text') {
                	    /* do nothing */
            	    } else {
                        $('#uploadBox').removeClass('hide');
                        $('#progressBox').addClass('hide');
                        uploader.updateProgress('unpack', 0);
            	    }
            	}, false);
            };

        }
    )
    ;


</script>
</head>
<body>
<div class="box shine">
    <h1>SugarCRM Upgrader</h1>
</div>
<div id='errorBox' class="alert progress hide">
    <div class="bar bar-error" style="width: 100%;">
    </div>
</div>
    <div class="box" id='uploadBox'>
        <p>
            <small>Upload the upgrade package to begin the upgrade process.</small>
        </p>
        <form id='uploadForm'>
            <!-- MAX_FILE_SIZE must precede the file input field -->
            <input type="hidden" name="token" value="<?php echo $token ?>"/>
            <input type="hidden" name="action" value="unpack"/>
            <input type="hidden" name="MAX_FILE_SIZE" value="300000000"/>
            <!-- Name of input element determines name in $_FILES array -->
            <input name="zip" type="file"/>
            <a class="btn btn-success" onclick='$("#uploadForm").submit()'>Complete Upgrade</a>
        </form>

        <div id='uploadBar' class="progress check hide">
            <div class="bar bar-error" style="width: 100%;"></div>
        </div>


    </div>
    <span id='progressBox' class='hide'>
<div class='box'>
    <small id='upgradeTitle'>Upgrade Progress</small>
</div>
<div class="box">

    <div class="progress check">
        <div id='unpackMicroBar' class="bar bar-success" style="width: 100%;"></div>
    </div>
    <strong>Upload</strong>

    <div class="progress">
        <div id='unpackBar' class="bar" style="width: 0%;"></div>
    </div>
</div>
    <div class="box">

        <div class="progress check">
            <div id='preMicroBar' class="bar bar-success" style="width: 0%;"></div>
        </div>
        <strong>Pre Upgrade</strong>

        <div class="progress">
            <div id='preBar' class="bar" style="width: 0%;"></div>
        </div>
    </div>
        <div class="box">

            <div class="progress check">
                <div id='commitMicroBar' class="bar bar-success" style="width: 0%;"></div>
            </div>
            <strong>Upgrade</strong>

            <div class="progress">
                <div id='commitBar' class="bar" style="width: 0%;"></div>
            </div>
        </div>

    <div class="box">

        <div class="progress check">
            <div id='postMicroBar' class="bar bar-success" style="width: 0%;"></div>
        </div>
        <strong>Post Upgrade</strong>

        <div class="progress">
            <div id='postBar' class="bar" style="width: 0%;"></div>
        </div>
    </div>
        <div class="box">

            <div class="progress check">
                <div id='cleanupMicroBar' class="bar bar-success" style="width: 0%;"></div>
            </div>
            <strong>Cleanup</strong>

            <div class="progress">
                <div id='cleanupBar' class="bar" style="width: 0%;"></div>
            </div>
        </div>
        <div class="box hide" id="successBox">
        <a class="btn btn-success" href="index.php">Back to SugarCRM</a>
        </div>
</span>
<div class="box">
    <p>
        <small>
            If you run into any issues contact <a href="http://support.sugarcrm.com/">support</a>.
        </small>
    </p>
</div>
  <section class="semantic-content" id="modal-text" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true">
    <div class="modal-inner">
        <header id="modal-label"><h1>SugarCRM License Agreement</h1></header>
        <div class="modal-content"><h2>Please read and accept this license</h2><p id="licenseText">LICENSE.txt</p></div>
        <footer><a href="#accepted" class="left-pedal" data-close="Close" data-dismiss="modal">Accept</a><a href="#!" class="right-pedal" data-close="Close" data-dismiss="modal">Cancel</a></footer>
    </div>
    <a href="#!" class="modal-close" title="Close this modal" data-close="Close" data-dismiss="modal">×</a>
  </section>
</body>
</html>
