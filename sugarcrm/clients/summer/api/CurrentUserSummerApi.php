<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once("clients/base/api/CurrentUserApi.php");
class CurrentUserSummerApi extends CurrentUserApi {
    public function registerApiRest() {
        return array(
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'retrieveCurrentUser',
                'shortHelp' => 'Returns current user',
                'longHelp' => 'include/api/help/me.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'updateCurrentUser',
                'shortHelp' => 'Updates current user',
                'longHelp' => 'include/api/help/me.html',
            ),
            'updatePassword' =>  array(
                'reqType' => 'PUT',
                'path' => array('me','password'),
                'pathVars'=> array(''),
                'method' => 'updatePassword',
                'shortHelp' => "Updates current user's password",
                'longHelp' => 'include/api/help/change_password.html',
            ),
            'verifyPassword' =>  array(
                'reqType' => 'POST',
                'path' => array('me','password'),
                'pathVars'=> array(''),
                'method' => 'verifyPassword',
                'shortHelp' => "Verifies current user's password",
                'longHelp' => 'include/api/help/verify_password.html',
            ),
            'updateTour' => array(
                'reqType' => 'PUT',
                'path' => array('me', 'tour'),
                'pathVars' => array(),
                'method' => 'updateTour',
                'shortHelp' => 'Sets current user tour flag to disabled.',
                'longHelp' => 'include/api/help/me.html',
            ),
        );
    }

    /**
     * Retrieves the current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveCurrentUser($api, $args) {
        $current_user = $this->getUserBean();
        $data = parent::retrieveCurrentUser($api, $args);

        $box = BoxOfficeClient::getInstance();
        $inst = $box->getCurrentInstance();
        $data['current_user']['instance_name'] = $inst['name'];
        $data['current_user']['instance_id'] = $inst['id'];

        $tour_pref = $current_user->getPreference('show_tour');

        $data['current_user']['show_tour'] = true;
        if($tour_pref === false) {
            $data['current_user']['show_tour'] = false;
        }
        return $data;
    }

    public function updateTour($api, $args) {
        $current_user = $this->getUserBean();
        $current_user->setPreference('show_tour', false);
        return $this->retrieveCurrentUser($api, $args);
    }
}
