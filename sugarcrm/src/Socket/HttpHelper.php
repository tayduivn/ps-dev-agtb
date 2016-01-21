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

namespace Sugarcrm\Sugarcrm\Socket;

class HttpHelper extends \SugarHttpClient
{
    /**
     * @var bool
     */
    protected $lastStatus = false;

    /**
     * This function checks site availability.
     *
     * @param string $url
     * @return bool
     */
    public function ping($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        $this->lastStatus = (200 == $retcode);
        return $this->isSuccess();
    }

    /**
     * Performs socket server request
     *
     * @param string $url
     * @param string $args
     * @return bool|mixed
     */
    public function getRemoteData($url, $args = '')
    {
        $response = $this->callRest(
            $url,
            $args,
            array(CURLOPT_HTTPHEADER => array("Content-Type: application/json"))
        );
        $this->lastStatus = ($this->getLastError() === '');
        return $this->isSuccess() ? json_decode($response, true) : false;
    }

    /**
     * Returns last operation status
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->lastStatus;
    }

}
