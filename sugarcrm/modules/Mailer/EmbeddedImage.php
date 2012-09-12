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

require_once 'Attachment.php'; // requires Attachment in order to extend it

/**
 * This class encapsulates properties and behavior of an embedded image, which is a type of attachment, so that a common
 * interface can be expected no matter what package is being used to deliver email.
 *
 * @extends Attachment
 */
class EmbeddedImage extends Attachment
{
    private $cid;   // The Content-ID used to reference the image in the message.

    /**
     * @access public
     * @param string      $path     required
     * @param string      $cid      required
     * @param null|string $name     Should be a string, but null is acceptable if the path will be used for the name.
     * @param string      $encoding
     * @param string      $mimeType
     */
    public function __construct($path, $cid, $name = null, $encoding = Encoding::Base64, $mimeType = 'application/octet-stream') {
        $this->setCid($cid);
        parent::__construct($path, $name, $encoding, $mimeType);
    }

    /**
     * @access public
     * @param string $cid
     */
    public function setCid($cid) {
        $this->cid = $cid;
    }

    /**
     * @return string
     */
    public function getCid() {
        return $this->cid;
    }

    /**
     * Returns an array representation of the embedded image by adding the Content-ID to the array resulting from
     * calling the parent method of the same name.
     *
     * @access public
     * @return array    Array of key value pairs representing the properties of the attachment.
     */
    public function getAsArray() {
        $image = parent::getAsArray();
        $image['cid'] = $this->getCid();

        return $image;
    }
}
