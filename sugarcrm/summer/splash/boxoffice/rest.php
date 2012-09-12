<?php
require 'BoxOffice.php';
require 'config.php';
require 'lib/Slim/Slim.php';
$app = new Slim();
$app->config(array('debug' => true));
error_reporting(E_ALL);
$box = new BoxOffice($dbconfig);

/**
 * Generic wrapper to return box results
 */
$responder = function() use($app, $box) {
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    if($box->isOk()) {
    	if(empty($app->data)) {
    		$app->halt(500, "Unexpected empty data");
    	}
    	if($app->data === true) {
    	    $app->data = array("success" => true);
    	}
    	echo json_encode($app->data);
    } else {
    	$app->response()->status($box->getStatus());
    	echo json_encode(array("error" => $box->getError()));
    }
};

/**
 * Access check for restricted API entries
 */
$access_check = function() use($app, $box) {
    // TODO: here will be the actual access control
    $give_access = true;
    if(!$give_access) {
        $app->halt(403);
    }
};

$app->error(function (Exception $e) use ($app) {
    $app->halt(500, $e->getMessage());
});

/**
 * function for authenticating users
 */
$app->post('/rest/users/login', $access_check, function() use ($app, $box)
{
   $remote = $app->request()->params('remote');
   if($remote) {
       $app->data = $box->authenticateRemoteUser($app->request()->params('email'), $app->request()->params('rid'));
   } else {
       $app->data = $box->authenticateUser($app->request()->params('email'), $app->request()->params('password'));
   }
}, $responder);

/**
 * Get user by email
 */
$app->get('/rest/users', $access_check, function() use ($app, $box)
{
    $app->data = $box->getUser($app->request()->params('email'));
}, $responder);

/**
 * Register new user
 */
$app->post('/rest/users', $access_check, function() use ($app, $box)
{
    $app->data = $box->registerUser(
        $app->request()->params('email'),
        $app->request()->params('password'),
        json_decode($app->request()->params('data'), true),
        $app->request()->params('status')
    );
}, $responder);

/**
 * Sends confirmation to the user
 */
$app->post('/rest/users/confirmation', $access_check, function() use ($app, $box)
{
    $app->data = $box->generateConfirmation(
        $app->request()->params('email'),
        $app->request()->params('type'),
        $app->request()->params('expires')
    );
}, $responder);

/**
 * Activates the user
 */
$app->post('/rest/users/activate', $access_check, function() use ($app, $box)
{
    $app->data = $box->generateConfirmation(
        $app->request()->params('email'),
        $app->request()->params('hash'),
        $app->request()->params('ip')
    );
}, $responder);

/**
 * Set users tokens
 */
$app->post('/rest/users/:id/tokens', $access_check, function($id) use ($app, $box)
{
    $app->data = $box->generateConfirmation(
        $id,
        $app->request()->params('token'),
        $app->request()->params('refresh_token'),
        $app->request()->params('expires')
    );
}, $responder);

/**
 * Get user instances
 */
$app->get('/rest/users/:id/instances', $access_check, function($id) use ($app, $box)
{
	$app->data = $box->getUserInstances($id, $app->request()->params('instance'));
}, $responder);

/**
 * Delete user session by user ID
 */
$app->delete('/rest/users/:id/session', $access_check, function($id) use ($app, $box)
{
    $app->data = $box->deleteSession($id, $app->request()->params('instance'));
}, $responder);

/******* CLIENT API ***********/

/**
 * Get user's instances by session ID
 */
$app->get('/rest/sessions/:id/instances', function($id) use ($app, $box)
{
	$app->data = $box->getUsersInstances($id);
}, $responder);

/**
 * Invite user to instance
 */
$app->post('/rest/sessions/:id/invite', function($id) use ($app, $box)
{
	$app->data = $box->getUsersInstances($app->request()->params('email'));
}, $responder);

/**
 * Get modules accessible to user on this instance
 */
$app->get('/rest/sessions/:id/modules', function($id) use ($app, $box)
{
    $app->data = $box->getUserModules($id);
}, $responder);

/**
 * Get config by session ID
 */
$app->get('/rest/sessions/:id', function($id) use ($app, $box)
{
    $app->data = $box->getConfig($id);
}, $responder);

/**
 * Selects a given instance and bootstraps it into the session
 */
$app->post('/rest/sessions/:id', function($id) use ($app, $box)
{
    $app->data = $box->selectInstance($app->request()->params('user'), $app->request()->params('email'), $id);
}, $responder);

/**
 * Deletes user session
 */
$app->delete('/rest/sessions/:id', function($id) use ($app, $box)
{
	$app->data = $box->deleteSessionById($id);
}, $responder);

$app->run();

