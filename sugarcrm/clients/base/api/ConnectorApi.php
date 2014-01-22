<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
class ConnectorApi extends SugarApi
{
    /**
     * internal instance of connector manager
     * @var null
     */
    public $connectorManager = null;

    public function registerApiRest()
    {
        return array(
            'getConnectors' => array(
                'reqType' => 'GET',
                'path' => array('connectors'),
                'pathVars' => array('connectors'),
                'method' => 'getConnectors',
                'shortHelp' => 'Gets connector information',
                'longHelp' => 'include/api/help/connectors_get_help.html',
            ),
        );
    }

    /**
     * gets instance conenctor manager
     * @return ConnectorManager
     */
    public function getConnectorManager()
    {
        if (empty($this->connectorManger)) {
            require_once('include/connectors/ConnectorManager.php');
            $this->connectorManager = new ConnectorManager();
        }
        return $this->connectorManager;
    }

    /**
     * gets connector metadata
     * @param Object $api api object
     * @param Array $args arguments passed from api
     * @return array
     */
    public function getConnectors($api, $args)
    {
        $cm = $this->getConnectorManager();
        // build cache
        return $cm->getUserConnectors();
    }

    /**
     * handles if given connector hash is valid
     * @param Array $args arguments passed from api
     * @throws SugarApiExceptionInvalidHash
     */
    public function validateHash($args)
    {
        if (!empty($args['connectorHash'])) {
            $cm = $this->getConnectorManager();
            $valid = $cm->isHashValid($args['connectorHash']);
            if (!$valid) {
                // hash is invalid throw api error for 412
                throw new SugarApiExceptionInvalidHash(
                    'EXCEPTION_CONNECTORS_META_OUT_OF_DATE',
                    null,
                    null,
                    0,
                    'connectors_meta_out_of_date'
                );
            }
        }
    }

}
