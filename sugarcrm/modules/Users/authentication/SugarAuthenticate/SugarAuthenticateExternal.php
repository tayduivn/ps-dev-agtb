<?php

/**
 * External Auth interface
 */
interface SugarAuthenticateExternal
{
    /**
     * Get URL to follow to get logged in
     */
    public function getLoginUrl();
}
