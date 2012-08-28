<?php
session_start();
chdir('../..');
define('sugarEntry', true);
require_once('summer/splash/BoxOfficeClient.php');
require_once 'summer/splash/boxoffice/lib/Slim/Slim.php';
require_once 'summer/splash/boxoffice/lib/EasyRpService.php';
$app = new Slim();
$app->config(array('debug' => true));
error_reporting(E_ALL &~ E_STRICT);
restore_error_handler();
$box = BoxOfficeClient::getInstance();

/**
 * function for authenticating users
 */
$app->get('/rest/users/callback', function() use ($app, $box)
{
    //$app->response()->header('Content-Type', 'text/html;charset=utf-8');
    $api = new EasyRpService($box->getSetting('google_key'));
    $result = $api->verify($box->getSetting('top_url')."summer/splash/rest/users/callback", $_SERVER['QUERY_STRING']);
    if(empty($result['verifiedEmail'])) {
        $data = json_encode(array("error" => "Failed to authenticate. Please contact support."));
    }else if(substr_count($result['verifiedEmail'], '@sugarcrm.com') != 1){
                $data = json_encode(array("error" => "Summer is currently in a private beta. Thank you for your interest!"));
    } else {
        $email = $result['verifiedEmail'];

        session_destroy();
        session_start();
        if($box->getUser($email, false)) {
            // existing user
            $data = $box->authenticateUser($email, null, $result['identifier']);
        } else {
            // new user
            $box->createRemoteUser($email, array("first_name" => $result['firstName'], "last_name" => $result['lastName'],
                "photo" => $result['photoUrl'], "remote_id" => $result['identifier']
                ));
            $data = $box->authenticateUser($email, null, $result['identifier']);
        }
        if($data['user']['status'] == 'Active') {
            if(!empty($result['oauthAccessToken'])) {
                $box->setUserTokens($result['oauthAccessToken'],
                    empty($result['oauthRefreshToken'])?'':$result['oauthRefreshToken'], $result['oauthExpireIn']);
            }
            $data = json_encode($data);
        } else {
            $data = json_encode(array("error" => "Your account is not active. Please contact support."));
        }
    }
    $_SESSION['gauth_data'] = $data;
    $app->response()->header('Location', '../../index.php');
END;
}
);

// $app->get('/rest/dump', function() use ($app, $box)
// {
//     echo "<pre>";
//    var_dump($_REQUEST);
// });

// $app->post('/rest/dump', function() use ($app, $box)
// {
// 	echo "<pre>";
// 	var_dump($_REQUEST);
// });

// $app->get('/rest/users/authuri', function() use ($app, $box)
// {
//     $app->response()->header('Content-Type', 'application/json;charset=utf-8');

//     $api = new EasyRpService($box->getSetting('google_key'));
//     $res = $api->getUrl("smalyshev@gmail.com", $box->getSetting('top_url')."summer/splash/rest/dump");
//     if($res) {
//     	echo $res['authUri']."&access_type=offline";
//     }
// });

// $app->get("/rest/users/authkey", function() use ($app, $box)
// {
//     $req['code'] = $app->request()->params("key");
//     $req['redirect_uri'] = $box->getSetting('top_url')."summer/splash/rest/dump";
//     $req['client_id'] = '';
//     $req['client_secret'] = '';
//     $req['grant_type'] =  'authorization_code';
//     $ch = curl_init();
// 	curl_setopt_array($ch, array(
// 		CURLOPT_URL => "https://accounts.google.com/o/oauth2/token",
// 		CURLOPT_RETURNTRANSFER => 1,
// 		CURLOPT_POSTFIELDS => $req));
//     $response = curl_exec($ch);
// 	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     var_dump($http_code, $response);
// });

$app->post('/rest/users/authenticate', function() use ($app, $box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $password = $app->request()->params('password');
    if (!$email = $app->request()->params('email')) {
        $email = "gmail.com";
    };

    $api = new EasyRpService($box->getSetting('google_key'));
    $res = $api->getUrl($email, $box->getSetting('top_url')."summer/splash/rest/users/callback");
    if($res) {
        $add = "&access_type=offline";
        $user = $box->getUser($email, false);
        if(empty($user) || empty($user['refresh_token'])) {
            // if we need refresh token, we'd need to request approval
            $add .= "&approval_prompt=force";
        }
        echo json_encode(array("popup" => $res['authUri'].$add));
        return;
    }
    if(substr_count($email, '@sugarcrm.com') != 1){
           echo json_encode(array("error" => "Summer is currently in a private beta. Thank you for your interest!"));

      }else{

    session_destroy();
    session_start();
    if ($data = $box->authenticateUser($email, $password)) {
        switch ($data['user']['status']) {
            case 'Pending Confirmation':
                echo json_encode(array("error" => "Your account needs to be activated. Please check your email for the activation code. <div><a href='#' onclick='login.resendActivation(" . json_encode($email) . ")'>Resend Activation Code</a></div>"));
                break;
            case 'Active':
                echo json_encode($data);
                break;
            default:
                echo json_encode(array("error" => "Your account needs to be activated. Please contact support."));
        }

    } else {
        echo json_encode(array("error" => "Please check your email and password."));
    }
}
});

/**
 * sends a new authentication email to validate an email address
 */
$app->post('/rest/users/resendActivation', function() use ($app, $box)
{

    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    try {
        $email = $app->request()->params('email');
        if ($box->resendActivation($email)) {
            echo json_encode(array("info" => "An activation email was sent to " . $email));
        } else {
            echo json_encode(array("info" => "This account is already activated. Please try logging in again"));
        }
    } catch (Exception $e) {
        echo json_encode(array("error" => "Please register for an account"));
    }
});

/**
 * Selects a given instance and bootstraps it into the session
 */
$app->get('/rest/instances/:id', function($id) use ($app, $box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    try {
        $sessid = $box->selectInstance($id);
        if(empty($sessid)) {
            echo json_encode(array("error" => "This instance is not ready yet. Please try again a bit later"));
            return;
        }
        $config = $box->getConfig();
        echo json_encode(array('url' => $config['site_url']."summer/index.php?token=$sessid"));
    } catch (Exception $e) {

        echo json_encode(array("error" => "An error occurred please try again." . $e->getMessage()));
    }
});

/**
 * Returns list of instances for current user
 */
$app->get('/rest/instances', function() use ($app, $box)
{
	$app->response()->header('Content-Type', 'application/json;charset=utf-8');
	try {
        $inst = $box->getUserInstances();
	    echo json_encode($inst);
	} catch (Exception $e) {

		echo json_encode(array("error" => "An error occurred please try again." . $e->getMessage()));
	}
});

/**
 * Registers or updates a given user
 */
$app->post('/rest/users', function() use ($app, $box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $password = $app->request()->params('password');
    $password2 = $app->request()->params('password2');
    $email = $app->request()->params('email');
    $first_name = $app->request()->params('first_name');
    $last_name = $app->request()->params('last_name');
    $company = $app->request()->params('company');
    $last_name = $app->request()->params('last_name');
    if (empty($first_name) || empty($last_name) || empty($password) || empty($password2) || empty($email)) {
        $error = 'You are missing the following required field(s): ';
        $missingFields = array();
        if (empty($first_name)) $missingFields[] = 'First Name';
        if (empty($last_name)) $missingFields[] = 'Last Name';
        if (empty($password) || empty($password2)) $missingFields[] = 'Password';
        if (empty($email)) $missingFields[] = 'Email Address';
        $error .= implode($missingFields, ', ');
        echo json_encode(array("error" => $error));
    } else if ($password != $password2) {
        echo json_encode(array("error" => "Password fields do not match"));
    }
    elseif (strlen($password) < 8) {
        echo json_encode(array("error" => "Your password must be at least 8 characters long"));
    }
    elseif ($box->getUser($email, false)) {
        echo json_encode(array("error" => "This email address already exists. Please try resetting your password"));
    } else {
        $box->registerUser($email, $password, array("first_name" => $first_name, "last_name" => $last_name, "company" => $company));
        echo json_encode(array("success" => "You are almost ready! You just need to activate your account! A welcome message with an activation code was just sent to " . $email));
    }

});

/**
 *
 */
$app->post('/rest/users/resetpassword', function() use ($app, $box){
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $email = $app->request()->params('email');
    try{
        $box->requestPasswordReset($email);
        echo json_encode(array("info" => "A reset password email was sent to " . $email));

    }catch(Exception $e){
        echo json_encode(array("error" => "Please register for an account"));
    }

});
$app->run();

