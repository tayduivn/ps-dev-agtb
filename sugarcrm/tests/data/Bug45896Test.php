<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * @brief Try to test download.php for php notices
 * @ticket 45896
 * @author mgusev@sugarcrm.com
 */
class Bug45896Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $curl = null;
    private $sessionName = '';
    private $sessionId = '';
    private $backup = array();
    private $user = null;

    /**
     * @brief Here we create valid session for anonymous user
     * @return void
     */
    public function setUp()
    {
        if (extension_loaded('suhosin') == true)
        {
            $configuration = ini_get_all('suhosin', false);
            if ($configuration['suhosin.session.encrypt'] == true)
            {
                $this->markTestSkipped('We can\'t fake session if encryption of session is used');
                return true;
            }
        }

        $this->backup['session.use_cookies'] = ini_get('session.use_cookies');
        ini_set('session.use_cookies', false);
        $this->backup['session.use_only_cookies'] = ini_get('session.use_only_cookies');
        ini_set('session.use_only_cookies', false);
        session_cache_limiter('');

        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_URL, $GLOBALS['sugar_config']['site_url']);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_NOBODY, true);
        $headers = curl_exec($this->curl);
        $error = curl_errno($this->curl);
        if ($error != 0)
        {
            $this->fail('Curl returns incorrect code');
        }

        $headers = explode("\r\n", trim($headers));
        foreach ($headers as $header)
        {
            $header = explode(': ', $header, 2);
            if ($header[0] == 'Set-Cookie')
            {
                $header[1] = explode('; ', $header[1]);
                $header = reset($header[1]);
                $header = explode('=', $header, 2);
                $this->sessionName = $header[0];
                $this->sessionId = $header[1];
            }
        }

        session_write_close();
        session_start();
        session_regenerate_id();
        $this->sessionId = session_id();
        $_SESSION['authenticated_user_id'] = $this->user->id;
        $_SESSION['authenticated_user_language'] = $GLOBALS['sugar_config']['default_language'];
        $_SESSION['unique_key'] = $GLOBALS['sugar_config']['unique_key'];
        if ($_SESSION['unique_key'] == false)
        {
            $this->fail('You must set unique_key value in config.php');
        }
        session_write_close();
    }

    /**
     * @brief query strings for download random file
     * @return array
     */
    public function getQueryString()
    {
        return array(
            array('entryPoint=download&id=643da5f0-513c-0933-5222-4e521fc84036&type=SugarFieldImage&isTempFile=1'),
            array('entryPoint=download&id=WeShouldTryToDownloadIncorrectFile&type=SugarFieldImage&isTempFile=1'),
            array('entryPoint=download&id=WeShouldTryToDownloadIncorrectFile&type=SugarFieldImage'),
            array('entryPoint=download&id=WeShouldTryToDownloadIncorrectFile&type=2&isTempFile=1')
        );
    }

    /**
     * @brief try to download files and to check response for notices
    * @dataProvider getQueryString
     * @group 45896
     *
    * @param array $queryString query string to download any file url
    */
    public function testDownload($queryString)
    {
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_NOBODY, false);
        curl_setopt($this->curl, CURLOPT_URL, $GLOBALS['sugar_config']['site_url'].'?'.$queryString);
        curl_setopt(
            $this->curl,
            CURLOPT_COOKIE,
            'ck_login_id_20='.$this->user->id.'; '.
                'ck_login_language_20=en_us; '.
                'ck_login_theme_20=Sugar; '.
                'globalLinksOpen=true; '.
                'sugar_theme_gm_current=All; '.
                'sugar_user_theme=Sugar; '.
                $this->sessionName.'='.$this->sessionId
        );
        $content = curl_exec($this->curl);
        $error = curl_errno($this->curl);
        $stat = curl_getinfo($this->curl);
        if ($error != 0) // need only valid curl result
        {
            $this->fail('Curl returns incorrect code');
        }
        elseif ($stat['http_code'] != 200) // need only success header
        {
            $this->fail('Incorrect HTTP code for test');
        }

        // getting headers
        $content = explode("\r\n\r\n", $content, 2);
        $content[0] = explode("\r\n", $content[0]);
        $headers = array();
        foreach ($content[0] as $header)
        {
            $header = explode(': ', $header, 2);
            if (count($header) != 2)
            {
                continue;
            }
            $headers[strtolower($header[0])] = $header[1];
        }
        $content = $content[1];

        // parse for type of content
        $headers['content-type'] = explode('/', $headers['content-type'], 2);
        $headers['content-type'] = strtolower(reset($headers['content-type']));

        // thinking what image and application type is valid, text is our test place, other types are fail
        switch ($headers['content-type']) {
            case 'image' :
            case 'application' :
                {
                    $this->assertNotEmpty($content, 'Content should be not empty');
                }
                break;
            case 'text' :
                {
                    $this->assertContains(
                        $content,
                        array(
                             'Not a Valid Entry Point',
                             'Error. This type is not valid.',
                             'Invalid File Reference'
                        ),
                        'Got php notice'
                    );
                }
                break;
            default :
                {
                    $this->fail('Received unknown content type');
                }
        }
    }

    /**
     * @brief closing curl connection and restore php.ini parameters
     * @return void
     */
    public function tearDown()
    {
        curl_close($this->curl);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        session_start();
        session_regenerate_id(true);
        foreach ($this->backup as $k=>$v)
        {
            ini_set($k, $v);
        }
    }
}
