<?php

namespace Sugarcrm\Sugarcrm\Trigger;

class HttpHelper extends \SugarHttpClient
{

    /**
     * This function checks site availability.
     *
     * @param string $url
     * @return bool
     */
    public function ping($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (200 === $retcode);
    }

    /**
     * Performs socket server request
     *
     * @param string $method (post or delete)
     * @param string $url
     * @param string $args
     * @return bool was request performed successfully
     */
    public function send($method, $url, $args = '')
    {
        $curl = curl_init($url);

        $curlOpts = $this->getCurlOpts(array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($args)),
            CURLOPT_POSTFIELDS => $args
        ));

        curl_setopt_array($curl, $curlOpts);

        $GLOBALS['log']->debug("HTTP client call: $method $url -> " . var_export($args, true));
        $response = curl_exec($curl);

        // Handle error
        if ($response === false) {
            $this->last_error = 'ERROR_REQUEST_FAILED';
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $GLOBALS['log']->error("HTTP client: cURL call failed for $method '$url': error $curl_errno: $curl_error");
            return false;
        }

        $GLOBALS['log']->debug("HTTP client response: $response");
        curl_close($curl);

        return true;
    }
}
