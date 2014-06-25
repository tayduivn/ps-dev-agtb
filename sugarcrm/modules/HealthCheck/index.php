<style>
    .health-checker-dialog {
        margin: 20px auto;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-shadow: 1px 1px 3px #ccc;
        width: 600px;
    }

    .health-checker-dialog .circle {
        border: 1px solid #ccc;
        border-radius: 20px;
        width: 24px;
        height: 24px;
        float: left;
        text-align: center;
        margin: 0 5px;
    }

    .health-checker-dialog .circle-inner {
        padding: 3px;
        background: #ccc;
        border-radius: 22px;
        margin: 2px;
        /* width: 20px; */
        /* height: 20px; */
    }

    .health-checker-dialog .logo {
        float: right;
        margin-top: -5px;
    }

    .health-checker-dialog .footer {
        text-align: right;
        border-top: 1px solid #ccc;
        background-color: #efefef;
        height: 40px;
        padding: 10px 10px 0 10px;
    }

    .health-checker-dialog .header {
        border-bottom: 1px solid #ccc;
        height: 40px;
        padding: 10px 10px 0 10px;
    }

    .health-checker-dialog button {
        border-radius: 3px;
        border: 1px solid dodgerblue;
        padding: 5px 10px;
        background: dodgerblue;
        box-shadow: none;
        color: white;
        text-shadow: none;
    }

    .health-checker-dialog .footer a {
        padding-right: 5px;
    }

    .health-checker-dialog .icon {
        border-radius: 25px;
        padding: 5px 8px;
        border-radius: 25px;
        color: white;
        font-style: normal;
        margin-right: 10px;
    }

    .health-checker-dialog .icon.icon-B {
        background: orange;
    }

    .health-checker-dialog .icon.icon-G {
        background: #008000;
    }

    .health-checker-dialog .icon.icon-F,
    .health-checker-dialog .icon.icon-E {
        background: darkred;
    }

    .health-checker-dialog ul {
        margin: 0;
        padding: 0;
    }

    .health-checker-dialog ul li {
        list-style: none;
    }

    .health-checker-dialog .body {
        padding: 20px;
    }

    .health-checker-dialog p {
        margin-left: 35px;
    }


</style>

<div class="health-checker-dialog">
    <div class="header">
        <div class="circle">
            <div class="circle-inner">
                3
            </div>
        </div>
        <strong>Health Check</strong> <br>
        <small>Running tasks and preparing for upgrade...</small>
        <img src="themes/default/images/company_logo.png" class="logo" id="logo" border="0">
    </div>
    <div class="body">
        <img style="margin: 10px auto; display: block;" title="Loading..." src="themes/default/images/bar_loader.gif">
    </div>
    <div class="footer">
        <a href="#">Cancel</a>
        <a href="#">Send Log To Sugar</a>
        <a href="index.php?module=HealthCheck&action=download">Export Log</a>
        <button>Next</button>
    </div>
</div>

<script type="text/javascript">
    var $body = $('.health-checker-dialog .body');
    $.ajax('index.php?module=HealthCheck&action=scan', {
            dataType: 'json',
            success: function (data) {
                var $ul = $('<ul></ul>');

                $body.html($ul);

                if (data.code === 0 && data.data.length == 0) {
                    var li = ['<li><h3><i class="icon icon-G">G</i>', SUGAR.language.get('HealthCheck', 'LBL_INSTANCE_IS_READY'), '</h3>',
                        '</li>'];
                    $ul.append(li.join(''));
                } else {

                    for (var index in data.data) {
                        if (data.data.hasOwnProperty(index)) {
                            var item = data.data[index];
                            $(item).each(function () {
                                var li = ['<li><h3><i class="icon icon-', index ,'">', index , '</i>', this.key, '</h3><p>',
                                    this.reason,
                                    ' <a href="#">', SUGAR.language.get('HealthCheck', 'LBL_LEARN_MORE')  , '</a></p></li>'];
                                $ul.append(li.join(''));
                            });
                        }
                    }

                }
            }
        },
        'json'
    );
</script>
