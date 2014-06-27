<?php ob_clean(); ?>

<link rel="stylesheet" href="modules/HealthCheck/static/css.css?v=1"/>
<script src='include/javascript/jquery/jquery-min.js?v=1'></script>

<div class="upgrade">
    <div id="alerts" class="alert-top">
        <div class="alert-wrapper">
            <div class="alert alert-danger alert-block" data-flag="3">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Error</strong>
                There was a problem with upgrade. Please correct the issue and <a href="index.php?module=HealthCheck">restart</a>
                or <a href="mailto:support@sugarcrm.com">contact support</a>.
            </div>
            <div class="alert alert-success alert-block" data-flag="1">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Success:</strong>
                You have passed the upgrade check.
            </div>
            <div class="alert alert-warning alert-block"  data-flag="2">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Warning</strong>
                Adjustments to your system will be automatically applied, please take a note.
            </div>
        </div>
    </div>
    <div class="modal" data-step="1">
        <div class="modal-header modal-header-upgrade row-fluid">
            <span class="step-circle">
                <span>1</span>
            </span>

            <div class="upgrade-title span7">
                <h3>Sugar 6.5 to 7.5 Upgrade: Prerequisites</h3>
                <span>Before performing an upgrade there are some prerequisites that should be followed to ensure a successful upgrade.</span>
            </div>
            <div class="progress-section span5 pull-right">
                <span><img src="modules/HealthCheck/static/company_logo.png" alt="SugarCRM" class="logo"></span>

                <div class="progress progress-success">
                    <div class="bar" style="width: 33%;"></div>
                </div>
            </div>
        </div>
        <div class="modal-body record">
            <div class="row-fluid">
                <h1>Upgrading</h1>

                <p>To get the most out of Sugar we recommend being on the latest version. Newer versions of Sugar come
                    with increased performance, bug fixes, and new features in general.
                    Before upgrading Sugar it is highly recommended that the upgrade be run on a test or backup copy of
                    your production system first. This will not only allow you to be familiar with the process, but can
                    point out any potential issue(s) you may encounter when upgrading your production instance. We also
                    recommend checking the <a target="_blank"
                                              href="http://support.sugarcrm.com/05_Resources/03_Supported_Platforms/Sugar_7.5.x_Supported_Platforms/">Support
                        Platforms</a> page before upgrading to make sure your current technology is still supported on
                    the version you are upgrading to.
                    To obtain the correct backup files, please follow the steps in the <a target="_blank"
                                                                                          href="http://support.sugarcrm.com/02_Documentation/01_Sugar_Editions/01_Sugar_Ultimate/Sugar_Ultimate_7.5/Installation_and_Upgrade_Guide/">Downloading
                        Sugar</a> section and download the proper upgrade zip file matching your current version of
                    Sugar and the desired upgraded version.</p>

                <h1>Prerequisites</h1>

                <p>Before performing an upgrade there are some prerequisites that should be followed to ensure a
                    successful upgrade.</p>

                <ul>
                    <li>Backup your current Sugar directory on the web server and the database.</li>
                    <li>Verify the PHP post_max_size and upload_max_filesize settings are larger than the size of the
                        upgrade zip file. These settings can be verified by performing a
                        http://us.php.net/manual/en/function.phpinfo.php function or by checking the php.ini file.
                    </li>
                    <li>Verify that the user the web server is running under has read and write permissions to the Sugar
                        directory as well as the config.php file in the Sugar directory.
                    </li>
                    <li>If you have made code level changes to a file, verify the changes are in the custom directory or
                        they may be removed during the upgrade.
                    </li>
                    <li>If op-code caching is enabled in PHP, disable it to ensure cached code is not used during the
                        upgrade. Op-code caching can be re-enabled after the upgrade is complete.
                    </li>
                    <li>If you are using Zend Core 2.0, increase the values for ConnectionTimeout to 3000 seconds and
                        RequestTimeout to 6000 seconds.
                    </li>
                    <li>If you are running on Apache, set the LimitRequestBody value in the httpd.conf file to 2GB.</li>
                </ul>
            </div>
        </div>
        <div class="modal-footer">
          <span sfuuid="25" class="detail">
            <a class="btn btn-invisible" href="javascript:void(0);">Cancel</a>
            <a class="btn btn-primary" href="#2" name="next_button">Next</a>
          </span>
        </div>
    </div>
    <div class="modal" data-step="2">
        <div class="modal-header modal-header-upgrade row-fluid">
            <span class="step-circle">
                <span>2</span>
            </span>

            <div class="upgrade-title span7">
                <h3>Sugar 6.5 to 7.5 Upgrade: Health Check</h3>
                <span>We need to verify your instance before upgrade</span>
            </div>
            <div class="progress-section span5 pull-right">
                <span><img src="modules/HealthCheck/static/company_logo.png" alt="SugarCRM" class="logo"></span>

                <div class="progress progress-success">
                    <div class="bar" style="width: 66%;"></div>
                </div>
            </div>
        </div>
        <div class="modal-body record">
            <div class="row-fluid ">
                <h1>Health Check</h1>

                <p>Before upgrade health check is required. Health check may take up to 30 minutes to complete. Press
                    Next to perform the Health Check.</p>
            </div>
        </div>
        <div class="modal-footer">
          <span sfuuid="25" class="detail">
            <a class="btn btn-invisible" href="javascript:void(0);">Cancel</a>
            <a class="btn btn-primary" href="#3" name="next_button">Next</a>
          </span>
        </div>
    </div>
    <div class="modal" data-step="3">
        <div class="modal-header modal-header-upgrade row-fluid">
            <span class="step-circle">
                <span>3</span>
            </span>

            <div class="upgrade-title span7">
                <h3>Sugar 6.5 to 7.5 Upgrade: Pre-check...</h3>
                <span>Running tasks and preparing for upgrade...</span>
            </div>
            <div class="progress-section span5 pull-right">
                <span><img src="modules/HealthCheck/static/company_logo.png" alt="SugarCRM" class="logo"></span>

                <div class="progress progress-success">
                    <div class="bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="modal-body record">
            <div class="row-fluid" id="healthcheck">
                <img style="display: block; margin: 20px auto" src="themes/default/images/loading.gif" alt="Loading...">
            </div>
        </div>
        <div class="modal-footer">
          <span sfuuid="25" class="detail">
            <a class="btn btn-invisible" href="javascript:void(0);">Cancel</a>
            <a class="btn btn-invisible" href="javascript:void(0);">Send Log to Sugar</a>
            <a class="btn btn-invisible" href="javascript:void(0);">Export Log</a>
            <a class="btn btn-primary disabled" href="UpgradeWizard.php" name="next_button">Next</a>
          </span>
        </div>
    </div>
</div>

<style>
    [data-step="2"], [data-step="3"] {
        display: none;
    }

    #alerts .alert {
        display: none;
    }
</style>

<script>
    (function () {
        var currentStep = 1,
            maxSteps = 3,
            hashStep = parseInt(window.location.hash),
            nodes = document.querySelectorAll('[data-step] a[name="next_button"]');

        if (hashStep > currentStep) {
            currentStep = hashStep;
        }

        for (var i = 0; i < nodes.length; i++) {
            nodes[i].addEventListener('click', showNextStep, false);
        }

        document.querySelector('[data-step="2"] a[name="next_button"]').addEventListener('click', doHealthCheck, false);

        function showNextStep() {
            var nextStep = currentStep + 1;
            if (nextStep <= maxSteps) {
                document.querySelector('[data-step="' + currentStep + '"]').style.display = 'none';
                document.querySelector('[data-step="' + nextStep + '"]').style.display = 'block';
                currentStep = nextStep;
            }
            return false;
        }

        function doHealthCheck() {
            $.ajax('index.php?module=HealthCheck&action=scan&bwcMode=1', {
                dataType: 'json',
                success: function (data) {
                    data = data.sort(_sortByBucket);
                    var flagToIcon = [,'icon-check color_green', 'icon-gear color_yellow', 'icon-exclamation-sign color_red'];
                    $("#healthcheck").html("");
                    for (var i = 0; i < data.length; i++) {
                        var item = data[i];
                        var html = ["<h1><i class='", flagToIcon[parseInt(item.flag)], "'></i> ", item.report, "</h1><p>", item.log, " <a href='#'>Learn more...</a></p>"];
                        $("#healthcheck").append(html.join(""));
                    }
                    var flag = data[data.length - 1].flag;
                    _displayAlert(flag);
                    if(flag == 1) {
                        $('.btn.btn-primary.disabled').removeClass('disabled');
                    }

                }
            });
        }

        function _sortByBucket(item1, item2) {
            if (item1.bucket > item2.bucket) return 1;
            if (item1.bucket < item2.bucket) return -1;
            return 0;
        }

        function _displayAlert(flag) {
            $('#alerts [data-flag=' + flag + ']').show();

        }

    })();
</script>
