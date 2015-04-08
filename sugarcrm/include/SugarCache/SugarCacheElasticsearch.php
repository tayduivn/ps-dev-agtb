<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 *
 * SugarCache backend using \Sugarcrm\Sugarcrm\Elasticsearch
 *
 * This backend needs to be explicitly enabled using $sugar_config before it
 * becomes available. The configured SearchEngine needs to implement the
 * SugarCache capability exposing the key/value store.
 *
 *      $sugar_config['external_cache_enabled_elasticsearch'] = true;
 *
 * When enabled make sure to disable other cache backends which get
 * automatically selected because of a better priority.
 *
 */
class SugarCacheElasticsearch extends SugarCacheAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $_priority = 990;

    /**
     * @var SugarConfig
     */
    protected $sugarConfig;

    /**
     * @var SugarCacheInterface
     */
    protected $engine;

    /**
     * Ctor
     */
    public function __construct()
    {
        parent::__construct();
        $this->sugarConfig = SugarConfig::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function useBackend()
    {
        if (!parent::useBackend()) {
            return false;
        }

        // We need to be activated through $sugar_config
        if (!$this->sugarConfig->get('external_cache_enabled_elasticsearch', false)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function _setExternal($key, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function _getExternal($key)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function _clearExternal($key)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function _resetExternal()
    {
    }
}
