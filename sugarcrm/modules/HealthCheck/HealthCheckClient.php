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
 * Class HealthCheckClient
 */
class HealthCheckClient
{
    const SERVER_URL = "https://updates.sugarcrm.com/sortinghat.php";

    /**
     * @param $key
     * @param $logFilePath
     * @return bool
     */
    public function send($key, $logFilePath)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::SERVER_URL);
        curl_setopt($ch, CURLOPT_POST, true);

        $fields = array(
            'key' => $key,
            "log" => "@$logFilePath",
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $response = curl_exec($ch);

        $GLOBALS['log']->fatal(__CLASS__ . $response);
        $GLOBALS['log']->fatal(__CLASS__ . curl_error($ch));

        curl_close($ch);

        return strpos($response, "Saved:") !== false;
    }
}