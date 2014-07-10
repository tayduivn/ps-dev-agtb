
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
                <strong>Success</strong>
                You have passed the upgrade check.
            </div>
            <div class="alert alert-warning alert-block"  data-flag="2">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Warning</strong>
                Adjustments to your system will be automatically applied, please take a note.
            </div>
            <div class="alert alert-success alert-block"  data-send="ok">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Success</strong>
                Log was sent successfully.
            </div>
            <div class="alert alert-danger alert-block"  data-send="error">
                <button class="btn btn-link btn-invisible close" data-action="close">
                </button>
                <strong>Error</strong>
                Unable to send log to Sugar. Please make sure you've internet connection.
            </div>
        </div>
    </div>
    <div class="modal" data-step="1">
        <div class="modal-header modal-header-upgrade row-fluid">
            <span class="step-circle">
                <span>1</span>
            </span>

            <div class="upgrade-title span7">
                <h3>Sugar 7 Health Check</h3>
                <span></span>
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

                <p>To ensure a succesful upgrade this health check wizard will scan your current SugarCRM
                instance and will generate a full report of any incompatible customizations. This report
                will explain which changes will be performed to your instance during an upgrade. In case
                of any incompatible issues which cannot be automatically resolved by the upgrade wizard,
                this health check tool will report what needs to be addressed.</p>

                <p>If not all prerequisits pass, an upgrade to Sugar 7 will not be possible.</p>

            </div>
        </div>
        <div class="modal-footer">
          <span sfuuid="25" class="detail">
            <a class="btn btn-invisible" href="index.php">Cancel</a>
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
                <h3>Sugar 7 Health Check</h3>
                <span>Review health check results</span>
            </div>
            <div class="progress-section span5 pull-right">
                <span><img src="modules/HealthCheck/static/company_logo.png" alt="SugarCRM" class="logo"></span>

                <div class="progress progress-success">
                    <div class="bar" style="width: 66%;"></div>
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
            <a class="btn btn-invisible" href="index.php">Cancel</a>
            <a class="btn btn-invisible send-logs" href="javascript:void(0);">Send Log to Sugar</a>
            <a class="btn btn-invisible" href="index.php?module=HealthCheck&action=export">Export Log</a>
            <a class="btn btn-primary disabled" href="index.php?module=HealthCheck&action=confirm" name="next_button">Confirm</a>
          </span>
        </div>
    </div>
</div>

{literal}
<style>
    [data-step="2"], [data-step="3"] {
        display: none;
    }

    #alerts .alert {
        display: none;
    }
</style>
{/literal}

{literal}
<script>
    (function () {
        var currentStep = 1,
            maxSteps = 2,
            hashStep = parseInt(window.location.hash),
            nodes = document.querySelectorAll('[data-step] a[name="next_button"]');

        if (hashStep > currentStep) {
            currentStep = hashStep;
        }

        for (var i = 0; i < nodes.length; i++) {
            nodes[i].addEventListener('click', showNextStep, false);
        }

        document.querySelector('[data-step="1"] a[name="next_button"]').addEventListener('click', doHealthCheck, false);

        document.querySelector('.send-logs').addEventListener('click', sendLogs, false);

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
            var flagToIcon = [,'icon-check color_green', 'icon-gear color_yellow', 'icon-exclamation-sign color_red'];
            $.ajax('index.php?module=HealthCheck&action=scan', {
                dataType: 'json',
                success: function (data) {
                    data = data.sort(_sortByBucket);
                    $("#healthcheck").html("");
                    for (var i = 0; i < data.length; i++) {
                        var item = data[i];
                        var html = ["<h1><i class='", flagToIcon[parseInt(item.flag)], "'></i> ", item.report, "</h1><p>", item.log, " <a href='#'>Learn more...</a></p>"];
                        $("#healthcheck").append(html.join(""));
                    }
                    var flag = data[data.length - 1].flag;
                    _displayAlert(flag);
                    if(flag < 3) {
                        $('.btn.btn-primary.disabled').removeClass('disabled');
                    }
                },
                error: function () {
                    var html = ["<h1><i class='", flagToIcon[parseInt(3)],
                            "'></i> Unexpected error occurred!</h1><p>We've encountered an unexpected error during heath check procedure. Please <a href='mailto:support@sugarcrm.com'>contact support</a>.</p>"];
                    $("#healthcheck").html(html.join(""));
                    _displayAlert(3);
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

        function sendLogs() {
            $.ajax('index.php?module=HealthCheck&action=send', {
               dataType: 'json',
                success: function (data) {
                    $('[data-send=' + data.status + ']').show();
                },
                error: function () {
                    $('[data-send="error"]').show();
                }
            });
        }

    })();
</script>
{/literal}
