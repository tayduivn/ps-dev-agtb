<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
class ZoominfoTestHelper {
    const STREAM_NAME = "zoominfo";

    public function __construct()
    {
        stream_wrapper_register(self::STREAM_NAME, 'ZoominfoMockStream', STREAM_IS_URL);
    }

    public function __destruct()
    {
        // ...
        stream_wrapper_unregister(self::STREAM_NAME);
    }

    public function url($type='query')
    {
        return self::STREAM_NAME."://$type/query?pc=";
    }
}

class ZoominfoMockStream
{
    public $query_params = array();
    protected $data = '';

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $dir = dirname(__FILE__);
        $urlinfo = parse_url($path);
        $this->query_params = array();
        parse_str($urlinfo['query'], $this->query_params);
        $smarty = new Sugar_Smarty();
        foreach($this->query_params as $name => $value) {
            $smarty->assign($name, $value);
        }
        $this->data = $smarty->fetch($dir."/".$urlinfo['host']."-zoominfo.xml");
        $this->position = 0;
        return true;
    }

    function stream_close()
    {
        $this->data = '';
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_eof()
    {
        return $this->position >= strlen($this->data);
    }
}