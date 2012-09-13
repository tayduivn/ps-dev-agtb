<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/********************************************************************************
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

require_once 'Encoding.php'; // needs the valid encodings defined in Encoding

/**
 * This class encapsulates properties and behavior of an attachment so that a common interface can be expected
 * no matter what package is being used to deliver email.
 */
class Attachment
{
    // protected members
    protected $path;        // Path to the file being attached.
    protected $name;        // Name of the file to be used to identify the attachment.
    protected $encoding;    // The encoding used on the file. Should be one of the valid encodings from Encoding.
    protected $mimeType;    // Should be a valid MIME type.

    /**
     * @access public
     * @param string      $path     required
     * @param null|string $name     Should be a string, but null is acceptable if the path will be used for the name.
     * @param string      $encoding
     * @param string      $mimeType
     */
    public function __construct($path, $name = null, $encoding = Encoding::Base64, $mimeType = 'application/octet-stream') {
        $this->setPath($path);
        $this->setName($name);
        $this->setEncoding($encoding);
        $this->setMimeType($mimeType);
    }

    /**
     * @access public
     * @param string $path required
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @access public
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @access public
     * @param null|string $name required Should be a string, but null is acceptable if the path will be used for the name.
     */
    public function setName($name) {
        if (!is_string($name) || $name == '') {
            // derive the name from the path if the name is invalid
            $name = basename($this->path);
        }

        $this->name = trim($name);
    }

    /**
     * @access public
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @access public
     * @param string $encoding
     */
    public function setEncoding($encoding = Encoding::Base64) {
        $this->encoding = $encoding;
    }

    /**
     * @access public
     * @return string
     */
    public function getEncoding() {
        return $this->encoding;
    }

    /**
     * @access public
     * @param string $mimeType
     */
    public function setMimeType($mimeType = 'application/octet-stream') {
        $this->mimeType = $mimeType;
    }

    /**
     * @access public
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * Returns an array representation of the attachment.
     *
     * @access public
     * @return array Array of key value pairs representing the properties of the attachment.
     */
    public function getAsArray() {
        return array(
            'path'     => $this->getPath(),
            'name'     => $this->getName(),
            'encoding' => $this->getEncoding(),
            'mimetype' => $this->getMimeType(),
        );
    }
}
