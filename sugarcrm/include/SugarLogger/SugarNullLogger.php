<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 ********************************************************************************/

/**
 * Null logger, used for slim entry points that run from preDispatch.php
 * @api
 */
class SugarNullLogger
{
    /**
     * Overloaded method that ignores the log request
     *
     * @param string $method
     * @param string $message
     */
    public function __call($method, $message)
    {
    }
}

