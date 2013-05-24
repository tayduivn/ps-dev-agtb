<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
*("License") which can be viewed at http://www.sugarcrm.com/EULA.
*By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
*not use this file except in compliance with the License. Under the terms of the license, You
*shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
*otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
*service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
*of a third party.  Use of the Software may be subject to applicable fees and any use of the
*Software without first paying applicable fees is strictly prohibited.  You do not have the
*right to remove SugarCRM copyrights from the source code or user interface.
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution.  See full license for requirements.
*Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
*to the License for the specific language governing these rights and limitations under the License.
*Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/
require_once 'vendors/Zend/Service/Amazon/S3.php';
require_once 'vendors/Zend/Service/Amazon/S3/Stream.php';

/**
 * S3 uploads driver
 * @api
 */
class SugarUploadS3 extends UploadStream
{
    protected $s3;
    protected $s3dir;
    protected $path;
    protected $localpath;
    protected $write;
    protected $bucket;
    protected $metadata;

    const S3_STREAM_NAME='uploads3';

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * Initialize the data
     * Not doing it in ctor due to the bug in PHP where ctor is not called on some stream ops
     */
    protected function init()
    {
        if(!empty($this->s3)) {
            return;
        }
        if(empty($GLOBALS['sugar_config']['aws'])
            || empty($GLOBALS['sugar_config']['aws']['aws_key'])
            || empty($GLOBALS['sugar_config']['aws']['aws_secret'])
            || empty($GLOBALS['sugar_config']['aws']['upload_bucket'])
            ) {
            $GLOBALS['log']->fatal("S3 keys are not set!");
            throw new Exception("S3 keys are not set!");
        }
        // TODO: add location support for buckets
        $this->metadata = array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>Zend_Service_Amazon_S3::S3_ACL_PRIVATE);
        $this->s3 = new Zend_Service_Amazon_S3($GLOBALS['sugar_config']['aws']['aws_key'], $GLOBALS['sugar_config']['aws']['aws_secret']);
        $this->s3->registerAsClient(self::S3_STREAM_NAME);
        $this->bucket = $GLOBALS['sugar_config']['aws']['upload_bucket'];
    }

    /**
     * Convert upload url to form bucket/filename by converting all /s but last to -
     * @param string $path
     * @return string
     */
    public function urlToObject($path, $prefix = false)
    {
        $object = substr($path, strlen(self::STREAM_NAME)+3); // upload://
        if($prefix) {
            return self::S3_STREAM_NAME."://".$this->bucket."/".$object;
        } else {
            return $this->bucket."/".$object;
        }
    }


    /**
     * Call Zend_Service_Amazon_S3_Stream function with given args
     * @param strinf $func Function
     * @param array $args arguments
     */
    protected function callS3($func, $args)
    {
        $s3stream = new Zend_Service_Amazon_S3_Stream();
        if(count($args) > 0) {
            $args[0] = $this->urlToObject($args[0], true);
        }
        return call_user_func_array(array($s3stream, $func), $args);
    }

   /**
     * Register new file added to uploads by external means
     * @param string $path
     * @return boolean
     */
    public function registerFile($path)
    {
        return $this->s3->putFileStream(parent::getFSPath($path), $this->urlToObject($path),
            $this->metadata);
    }

   /**
     * Fetch file if exists from S3 to local copy
     * @param string $path
     * @return string
     */
    public function fetchFile($path)
    {
        $localpath = parent::getFSPath($path);
        if (!file_exists($localpath)) {
            // TODO: can uploads be modified?
            $s3obj = $this->s3->getObjectStream($this->urlToObject($path));
            if (!empty($s3obj)) {
                copy($s3obj->getStreamName(), $localpath);
            }
        }
        return $localpath;
    }

    /**
     * Is this path an upload URL path?
     * @param string $path
     * @return boolean
     */
    public function isUploadUrl($path)
    {
        return substr($path, strlen(self::STREAM_NAME)+3) == self::STREAM_NAME."://";
    }

    public function dir_closedir()
    {
        $this->s3dir = null;
    }

    public function dir_opendir ($path, $options )
    {
        $this->init(); // because of php bug not calling stream ctor
        $this->s3dir = $this->s3->getObjectsAndPrefixesByBucket($this->bucket,
            array("prefix" => $this->urlToObject($path), "delimiter" => "/")
        );
        if(!empty($this->s3dir)) {
            $this->dir_rewinddir();
            return true;
        }
        return false;
    }

    public function dir_readdir()
    {
        if(empty($this->s3dir)) return false;
        // Go first through all prefixes then though all objects
        $pref = current($this->s3dir['prefixes']);
        if($pref !== false) {
            next($this->s3dir['prefixes']);
            return rtrim($pref, '/');
        }
        $obj = current($this->s3dir['objects']);
        if($obj !== false) {
        	next($this->s3dir['objects']);
        }
        return $obj;
    }

    public function dir_rewinddir()
    {
        if(empty($this->s3dir)) return false;
        reset($this->s3dir['prefixes']);
        reset($this->s3dir['objects']);
    }

    public function rename($path_from, $path_to)
    {
        parent::rename($path_from, $path_to);
        $this->init(); // because of php bug not calling stream ctor
        if($this->isUploadUrl($path_to)) {
            if($this->isUploadURL($path_from)) {
                // from S3 to S3 - copy there
                $this->s3->copyObject($this->urlToObject(path_from), $this->urlToObject(path_to));
            } else {
                // from local to S3 - just register the copy, parent did the local part
                $this->registerFile($path);
            }
        }
        if($this->isUploadURL($path_from)) {
            $this->s3->removeObject($this->urlToObject($path_from));
        }
        return true;
    }

    public function stream_flush ()
    {
        parent::stream_flush();
        if($this->write) {
            if(file_exists($this->path) && filesize($this->path)) {
                $this->registerFile($this->path);
            }
        }
    }

    public function stream_open($path, $mode)
    {
        $this->path = $path;
        $this->localpath = parent::getFSPath($path);
        if (strpbrk($mode, 'wax')) {
            // writing - do nothing, we'll catch it on flush()
            $this->write = true;
        } else {
            // reading
            if(!file_exists($this->localpath)) {
                $this->fetchFile($path);
            }
        }
        return parent::stream_open($path, $mode);
    }

    public function unlink($path)
    {
        $this->init(); // because of php bug not calling stream ctor
        @unlink(parent::getFSPath($path));
        return $this->callS3("unlink", func_get_args());
    }

    public function url_stat($path, $flags)
    {
        $this->init(); // because of php bug not calling stream ctor
        if(file_exists(parent::getFSPath($path))) {
            return parent::url_stat($path, $flags);
        }
        $stat = $this->callS3("url_stat", func_get_args());
        if(empty($stat['size'])) {
            return false;
        }
        return $stat;
    }
}