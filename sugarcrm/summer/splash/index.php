<?php
chdir('../..');
define('sugarEntry', true);
require_once('include/utils/autoloader.php');
SugarAutoLoader::init();
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
    <!-- CSS -->
    <?php
    $min_file = 'summer/summer-splash.min.css';
    if(file_exists("cache/".$min_file)) {
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../cache/$min_file\" />\n";
    } else {
        require_once('jssource/JSGroupings.php');
        foreach($js_groupings as $group) {
            foreach($group as $file => $min) {
                if($min == $min_file) {
                    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../$file\" />\n";
                }
            }
        }
    }
    ?>
</head>
<body>

<div class='span9 pull-left' id="desc_block">
    <span id="cfe">CRM for Everyone</span>

    <div id="wih" class='span5'>
        Work is hard. CRM shouldn't make it harder!
    </div>
    <div id="catchphrase"></div>
</div>

<div class="span6 pull-right" id="login_block">
    <div class='well span3'>
        <div id='notices'>
        </div>
        <h4>Welcome<span class="username"></span>!</h4>
        <form class='form' id="login">
            <input type="text" name='email' class="span3" placeholder="Email Address">
            <input type="password" name='password' class="span3" placeholder="Password">

            <div class="pull-right">
                <button type="submit" name='login' class="span3 btn btn-success">Login
                </button>
                <button type='button' id="register_btn" name='register' class="span3 btn btn-primary">
                    Register
                </button>
                <div class='span3'><a id='reset_lnk' href="#">Can't Login?</a></div>
                <button type="button" id="google_login" name="google" class="span3 btn">
                    Login with Google <i class='icon-google-plus'></i>
                </button>
            </div>
        </form>
        <form class='form' id="register">
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


                <button type="submit" name='register' class="span3 btn btn-success">
                    Register
                </button>
                <button type='button' id='cancel_register_btn' name='cancel' class="span3 btn">
                    Cancel
                </button>

            </div>
        </form>
        <div class='form' id="instances">
            <h4>Select your desired CRM</h4>
            <ul id="instancelist" class="nav nav-tabs nav-stacked">
            </ul>
            <div class='span3'><a id='instances_refresh' href="#">Refresh</a></div>
        </div>
        <form class='form' id="reset">
            <h4>Reset Your Password</h4>
            <input type="text" name='email' class="span3" placeholder="Email Address">

            <div class="pull-right">
                <button type="submit" name='reset' class="span3 btn btn-success">
                    Reset Password
                </button>
                <button type='button' id='cancel_reset_btn' name='cancel' class="span3 btn">
                    Cancel
                </button>
            </div>
        </form>
        <form class='form' id="resetpass">
            <h4>Reset Your Password</h4>
            <label>Password</label>
            <input type="text" name='password' class="span3" placeholder="Password">
            <label>Repeat Password</label>
            <input type="text" name='password2' class="span3" placeholder="Password">
            <input type="hidden" name='email' class="span3" value='<?php echo @$_REQUEST['email'] ?>'>
            <input type="hidden" name='guid' class="span3" value='<?php echo @$_REQUEST['guid'] ?>'>

            <div class="pull-right">
                <button type="submit" name='reset' class="span3 btn btn-success">
                    Reset Password
                </button>
                <button type='button' id='cancel_resetpass_btn' name='cancel' class="span3 btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<?php
    $min_file = 'summer/summer-splash.min.js';
    if(file_exists("cache/".$min_file)) {
        echo "<script src=\"../../cache/$min_file\"></script>\n";
    } else {
        require_once('jssource/JSGroupings.php');
        foreach($js_groupings as $group) {
            foreach($group as $file => $min) {
                if($min == $min_file) {
                    echo "<script src=\"../../$file\"></script>\n";
                }
            }
        }
    }
?>
<script>
    login.startup(<?php echo json_encode($settings) ?>);
</script>
</body>

</html>
