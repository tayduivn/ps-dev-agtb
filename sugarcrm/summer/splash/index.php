<?php
chdir('../..');
define('sugarEntry', true);
require_once('summer/splash/BoxOfficeClient.php');
$settings = array();
if (!empty($_REQUEST['do'])) {
    switch ($_REQUEST['do']) {
        case 'activate':
            $boc = BoxOfficeClient::getInstance();
            if ($boc->activateUser($_REQUEST['email'], $_REQUEST['guid'])) {
                $settings['success'][] = 'Congratulations! Your account is now active!';
            } else {
                $settings['error'][] = 'Please check your activation code and try again!';
            }
            break;
        case 'resetpass':
            $settings['display'] = 'resetpass';
            break;
    }
}

$id = session_id();
if(empty($id)){
    session_start();
}
if(!empty($_SESSION['gauth_data'])){
    $settings['login_response']  = json_decode($_SESSION['gauth_data']);
    unset($_SESSION['gauth_data']);
}


?>

<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="x-ua-compatible" content="IE=8">
    <link rel="stylesheet" href="../../sidecar/lib/chosen/chosen.css"/>
    <!-- CSS -->
    <link rel="stylesheet" href="../../sidecar/lib/chosen/chosen.css"/>
    <link rel="stylesheet" href="../lib/twitterbootstrap/css/bootstrap.css"/>
    <link rel="stylesheet" href="../../sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css"/>
    <script src="../../sidecar/lib/jquery/jquery.min.js"></script>
    <script src="../lib/twitterbootstrap/js/bootstrap-alert.js"></script>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">
    <script src="../../sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js"></script>
    <script src="login.js"></script>
    <style>
        body {
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }

    </style>

</head>
<body>

<div class='span9 pull-left' style='position:relative;top:250px'>
    <span style='font-size:60px;color:white;'>CRM for Everyone</span>

    <div style='font-size:14px;color:whitesmoke;' class='span5'>
        Work is hard. CRM shouldn't make it harder!
    </div>
    <div id="catchphrase" style='text-align:center;clear:both;font-size:24px;color:white;position:relative;top:50px'>

    </div>
</div>

<div class=" pull-right" style='margin-right: 50px'>

</div>

<div class="span6 pull-right" style='clear:right;position:relative;top:200px'>

    <div class='well span3'>
        <div id='notices'>
        </div>
        <h4>Welcome <span class="username"> </span></h4>
        <form class='form' id="login">
            <input type="text" name='email' class="span3" placeholder="Email Address">
            <div class='span3'>Login with Google or:</div>
            <input type="password" name='password' class="span3" placeholder="Password">

            <div class="pull-right">
                <button type="submit" name='login' class="span3 btn btn-success" style='margin-bottom: 5px'>Login
                </button>
                <button type='button' id="register_btn" name='register' class="span3 btn btn-primary"
                        style='margin-bottom: 5px'>Register
                </button>
                <div class='span3' style="text-align: center"><a id='reset_lnk' href="#">Can't Login?</a></div>
            </div>
        </form>
        <form class='form' id="register" style='display:none'>
            <h4>Register</h4>
            <label>First Name</label>
            <input type="text" name='first_name' class="span3" placeholder="First Name">
            <label>Last Name</label>
            <input type="text" name='last_name' class="span3" placeholder="Last Name">
            <label>Email Address</label>
            <input type="text" name='email' class="span3" placeholder="Email Address">
            <label>Company</label>
            <input type="text" name='company' class="span3" placeholder="Company Name">
            <label>Password</label>
            <input type="password" name='password' class="span3" placeholder="Password">
            <label>Repeat Password</label>
            <input type="password" name='password2' class="span3" placeholder="Password">

            <div class="pull-right">


                <button type="submit" name='register' class="span3 btn btn-success" style='margin-bottom: 5px'>
                    Register
                </button>
                <button type='button' id='cancel_register_btn' name='cancel' class="span3 btn"
                        style='margin-bottom: 5px'>Cancel
                </button>

            </div>
        </form>
        <div class='form' id="instances" style='display:none'>
            <h4>Select your desired CRM</h4>
            <ul id="instancelist" class="nav nav-tabs nav-stacked">
            </ul>
            <div class='span3' style="text-align: center"><a id='instances_refresh' href="#">Refresh</a></div>
        </div>
        <form class='form' id="reset" style='display:none'>
            <h4>Reset Your Password</h4>
            <input type="text" name='email' class="span3" placeholder="Email Address">

            <div class="pull-right">
                <button type="submit" name='reset' class="span3 btn btn-success" style='margin-bottom: 5px'>
                    Reset Password
                </button>
                <button type='button' id='cancel_reset_btn' name='cancel' class="span3 btn" style='margin-bottom: 5px'>
                    Cancel
                </button>
            </div>
        </form>
        <form class='form' id="resetpass" style='display:none'>
            <h4>Reset Your Password</h4>
            <label>Password</label>
            <input type="text" name='password' class="span3" placeholder="Password">
            <label>Repeat Password</label>
            <input type="text" name='password2' class="span3" placeholder="Password">
            <input type="hidden" name='email' class="span3" value='<?php echo @$_REQUEST['email'] ?>'>
            <input type="hidden" name='guid' class="span3" value='<?php echo @$_REQUEST['guid'] ?>'>

            <div class="pull-right">
                <button type="submit" name='reset' class="span3 btn btn-success" style='margin-bottom: 5px'>
                    Reset Password
                </button>
                <button type='button' id='cancel_resetpass_btn' name='cancel' class="span3 btn"
                        style='margin-bottom: 5px'>
                    Cancel
                </button>
            </div>
        </form>
    </div>


</div>
</div>
<script>
    login.startup(<?php echo json_encode($settings) ?>);
</script>
</body>

</html>