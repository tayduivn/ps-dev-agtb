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
 * Session handler for Sugar
 */
class SugarSessionHandler extends SessionHandler
{
    protected $max_session;
    protected $session_start;

    public function __construct()
    {
        if (!empty($GLOBALS['sugar_config']['max_session_time'])) {
            $this->max_session = $GLOBALS['sugar_config']['max_session_time'];
        }
    }

    public function open($save_path,$session_id)
    {
        if (parent::open($save_path, $session_id) && !empty($this->max_session)) {
            $this->session_start = time();
        }
    }

    public function close()
    {
        parent::close();
        if (!empty($this->max_session) && !empty($this->session_start)) {
            $length = time() - $this->session_start;
            if($length > $GLOBALS['sugar_config']['max_session_time'] && !empty($GLOBALS['log'])) {
                $GLOBALS['log']->error("[SessionLock] Session time too long: $length seconds");
            }
        }
    }
}
