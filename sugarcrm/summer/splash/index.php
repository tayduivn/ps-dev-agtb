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
<div class="blur"></div>
<div class="wrapper">
    <div class="content">
        <div id='notices'></div>
        <svg class="sugarcube" version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" xml:space="preserve">
            <defs xmlns="http://www.w3.org/2000/svg">
                <filter id="dropshadow" height="130%">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="0.8"/>
                    <feOffset dx="0.5" dy="0.55" result="offsetblur"/>
                    <feMerge>
                        <feMergeNode/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
            <g transform="scale(3) translate(-50, -100)" filter="url(#dropshadow)">
                <path fill="#E61718" d="M70.218,115.322c-0.717,0.282-1.888,0.282-2.602,0l-12.91-5.114c-0.715-0.283-0.715-0.746,0-1.03
                    l12.91-5.112c0.714-0.284,1.885-0.284,2.602,0l12.909,5.112c0.715,0.284,0.715,0.747,0,1.03L70.218,115.322z"/>
                <path fill="#343433" d="M68.198,136.272c0,0.771-0.585,1.167-1.301,0.885l-12.91-5.112c-0.715-0.283-1.301-1.143-1.301-1.914
                    v-17.926c0-0.768,0.586-1.167,1.301-0.883l12.91,5.113c0.716,0.282,1.301,1.144,1.301,1.914V136.272z"/>
                <path fill="#CDCCCB" d="M69.632,136.268c0,0.77,0.586,1.165,1.303,0.884l12.908-5.112c0.716-0.284,1.301-1.146,1.301-1.914
                    v-17.925c0-0.77-0.585-1.169-1.301-0.885l-12.908,5.111c-0.717,0.284-1.303,1.146-1.303,1.914V136.268z"/>
            </g>
        </svg>
        <form class="form" id="login">
            <div class="top-half-container">
                <i class="icon-user input-prefix-icon"></i>
                <input class="login-text-input top-half with-icon" type="text" name="email" placeholder="Email Address"/>
            </div>
            <div class="bottom-half-container">
                <i class="icon-key input-prefix-icon"></i>
                <input class="login-text-input bottom-half with-icon" type="password" name="password" placeholder="Password"/>
            </div>
            <div class="forgot-container"><a class="forgot" id="reset_lnk">Forgot Password?</a></div>
            <div class="centered">
                <button type="submit" name='login' class="btn btn-success">Login</button>
                <button type='button' id="register_btn" name='register' class="btn btn-primary">Register</button>
                <div class="block-divider"><div class="line"></div><span>OR</span><div class="line"></div></div>
                <button type="button" id="google_login" name="google" class="btn">
                    <i class='icon-google-plus'></i> Login with Google
                </button>
            </div>
        </form>
        <form class='form' id="register">
            <h4 class="heading-small">Registration Form</h4>
            <div class="top-half-container">
                <i class="icon-user input-prefix-icon"></i>
                <input type="text" name='first_name' class="login-text-input top-half with-icon" placeholder="First Name">
            </div>
            <div class="bottom-half-container">
                <i class="icon-user input-prefix-icon"></i>
                <input type="text" name='last_name' class="login-text-input bottom-half with-icon" placeholder="Last Name">
            </div>
            <div class="input-container">
                <i class="icon-envelope input-prefix-icon"></i>
                <input type="text" name='email' class="login-text-input with-icon" placeholder="Email Address">
            </div>
            <div class="input-container">
                <i class="icon-briefcase input-prefix-icon"></i>
                <input type="text" name='company' class="login-text-input with-icon" placeholder="Company Name">
            </div>
            <div class="top-half-container">
                <i class="icon-key input-prefix-icon"></i>
                <input type="password" name='password' class="login-text-input top-half with-icon" placeholder="Password">
            </div>
            <div class="bottom-half-container">
                <i class="icon-key input-prefix-icon"></i>
                <input type="password" name='password2' class="login-text-input bottom-half with-icon" placeholder="Retype Password">
            </div>
            <div class="centered">
                <button type="submit" name='register' class="btn btn-success">Register</button>
                <button type='button' id='cancel_register_btn' name='cancel' class="btn">Cancel</button>
            </div>
        </form>
        <div class='form' id="instances">
            <h4 class="heading-small">Select your desired CRM</h4>
            <ul id="instancelist" class="nav nav-tabs nav-stacked">
            </ul>
            <div class='centered'>
                <a id='instances_refresh' class="btn" href="#"><i class="icon-refresh"></i> Refresh</a>
            </div>
        </div>
        <form class='form' id="reset">
            <h4 class="heading-small">Reset Your Password</h4>
            <div class="input-container">
                <i class="icon-envelope input-prefix-icon"></i>
                <input type="text" name='email' class="login-text-input with-icon" placeholder="Email Address">
            </div>
            <div class="centered">
                <button type="submit" name='reset' class="btn btn-success">Reset</button>
                <button type='button' id='cancel_reset_btn' name='cancel' class="btn">Cancel</button>
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
