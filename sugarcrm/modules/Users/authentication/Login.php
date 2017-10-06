<?php

interface Login
{
    /**
     * Decides how to authenticate User when he/she logs-in.
     *
     * @param string $username
     * @param string $password
     * @param bool $fallback
     * @param array $params
     *
     * @return boolean
     */
    public function loginAuthenticate($username, $password, $fallback = false, array $params = []);
}
