<?php

require_once 'clients/base/api/ListApi.php';

class NotificationsApi extends ListApi
{
    public function registerApiRest()
    {
        return array(
            'list' => array(
                'reqType' => 'GET',
                'path' => array('Notifications', 'pull'),
                'pathVars' => array('module'),
                'method' => 'listModule',
                'ignoreMetaHash' => true,
            ),
        );
    }
}
