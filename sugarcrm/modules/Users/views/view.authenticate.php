<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/MVC/View/SidecarView.php';
require_once "include/api/RestService.php";
require_once 'clients/base/api/OAuth2Api.php';
require_once 'include/SugarOAuth2/SugarOAuth2Server.php';

class UsersViewAuthenticate extends ViewSidecar
{

    /**
     * Constructor
     *
     * @see SidecarView::SidecarView()
     */
    public function __construct($bean = null, $view_object_map = array())
    {
        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;
        parent::__construct($bean, $view_object_map);
    }

    public function preDisplay()
    {
        if(session_id()) {
            // kill old session
            session_destroy();
        }
        SugarAutoLoader::load('custom/include/RestService.php');
        $restServiceClass = SugarAutoLoader::customClass('RestService');
        $service = new $restServiceClass();
        SugarOAuth2Server::getOAuth2Server(); // to load necessary classes

        $oapi = new OAuth2Api();
        $args = $_REQUEST;
        $args['client_id'] = 'sugar';
        $args['client_secret'] = '';
        if (!empty($_REQUEST['SAMLResponse'])) {
            $args['grant_type'] = SugarOAuth2Storage::SAML_GRANT_TYPE;
            $args['assertion'] = $_REQUEST['SAMLResponse'];
        } else {
            if(empty($args['grant_type'])) {
                $args['grant_type'] = OAuth2::GRANT_TYPE_USER_CREDENTIALS;
            }
        }
        try {
            $this->authorization = $oapi->token($service, $args);
        } catch (Exception $e) {
            $GLOBALS['log']->error("Login exception: " . $e->getMessage());
        }
        parent::preDisplay();
    }
}
