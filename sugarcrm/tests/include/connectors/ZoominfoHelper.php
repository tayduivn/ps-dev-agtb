<?php
//FILE SUGARCRM flav=pro ONLY
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