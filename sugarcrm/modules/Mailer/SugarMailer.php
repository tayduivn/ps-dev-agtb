<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

require_once "SmtpMailer.php"; // requires SmtpMailer in order to extend it

/**
 * This class implements the additional SugarCRM-specific functionality that SmtpMailer lacks.
 *
 * @extends SmtpMailer
 */
class SugarMailer extends SmtpMailer
{
    // private members
    private $includeDisclosure = false; // true=append the disclosure to the message
    private $disclosureContent;         // the content to disclose

    /**
     * @param MailerConfiguration
     */
    public function __construct(MailerConfiguration $mailerConfig) {
        parent::__construct($mailerConfig);
        $this->retrieveDisclosureSettings();
    }

    /**
     * Adds the optional disclosure content to the message, as well as performs the same preparations that are
     * inherited from SmtpMailer.
     *
     * @access protected
     * @param string $body required
     * @return string
     */
    protected function prepareTextBody($body) {
        if ($this->includeDisclosure) {
            $body .= "\r\r{$this->disclosureContent}"; //@todo why are we using /r?
        }

        $body = parent::prepareTextBody($body);

        return $body;
    }

    /**
     * Adds the optional disclosure content to the message, as well as performs the same preparations that are
     * inherited from SmtpMailer. Additionally, converts to embedded images any inline images that are found
     * locally on the server that hosts the application instance. This extra step is done to guarantee that locally
     * referenced images can be seen by the recipient, whether the server is public or private.
     *
     * @access protected
     * @param string $body required
     * @return string
     */
    protected function prepareHtmlBody($body) {
        global $sugar_config;
        $siteUrl = $sugar_config["site_url"];

        if ($this->includeDisclosure) {
            $body .= "<br /><br />{$this->disclosureContent}";
        }

        // replace references to cache/images with cid tag
        $body = str_replace(sugar_cached("images/"), "cid:", $body);

        // replace any embeded images using cache/images for src url
        $body = $this->convertInlineImageToEmbeddedImage(
            $body,
            "(?:{$siteUrl})?/?cache/images/",
            sugar_cached("images/")
        );

        // replace any embeded images using the secure entryPoint for src url
        $body = $this->convertInlineImageToEmbeddedImage(
            $body,
            "(?:{$siteUrl})?index.php[?]entryPoint=download&(?:amp;)?[^\"]+?id=",
            "upload://",
            true
        );

        $body = parent::prepareHtmlBody($body);

        return $body;
    }

    /**
     * Replace images with locations specified by regex with cid: images and attach needed files.
     *
     * @param string $body
     * @param string $regex        Regular expression
     * @param string $localPrefix Prefix where local files are stored
     * @param bool   $object       Use attachment object
     * @return string The body with the applicable modifications.
     */
    protected function convertInlineImageToEmbeddedImage($body, $regex, $localPrefix, $object = false) {
        $i       = 0;
        $foundImages = array();
        preg_match_all("#<img[^>]*[\s]+src[^=]*=[\s]*[\"']($regex)(.+?)[\"']#si", $body, $foundImages);

        foreach ($foundImages[2] as $image) {
            $filename     = urldecode($image);
            $cid          = $filename;
            $fileLocation = $localPrefix . $filename;

            if (file_exists($fileLocation)) {
                $mimeType = null;

                if ($object) {
                    $mimeType  = "application/octet-stream";
                    $objectType = array();

                    if (preg_match("#&(?:amp;)?type=([\w]+)#i", $foundImages[0][$i], $objectType)) {
                        $beanName = null;

                        switch (strtolower($objectType[1])) {
                            case "documents":
                                $beanName = "DocumentRevisions";
                                break;
                            case "notes":
                                $beanName = "Notes";
                                break;
                        }
                    }

                    if (!is_null($beanName)) {
                        $bean = SugarModule::get($beanName)->loadBean();
                        $bean->retrieve($filename);

                        if (!empty($bean->id)) {
                            $mimeType  = $bean->file_mime_type;
                            $filename  = $bean->filename;
                        }
                    }
                } else {
                    $mimeType = "image/" . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                }

                $embeddedImage = new EmbeddedImage($fileLocation, $cid, $filename, Encoding::Base64, $mimeType);
                $this->addEmbeddedImage($embeddedImage);
                $i++;
            }
        }

        // replace references to cache with cid tag
        $body = preg_replace("|\"{$regex}|i", '"cid:', $body);

        // remove bad img line from outbound email
        $body = preg_replace('#<img[^>]+src[^=]*=\"\/([^>]*?[^>]*)>#sim', "", $body);

        return $body;
    }

    /**
     * Retrieves settings from the administrator configuration indicating whether or not to include a disclosure
     * at the bottom of an email, and if so, the content to disclose.
     *
     * @access private
     * @todo consider how this could become a merge field that is added prior to the Mailer getting created
     */
    private function retrieveDisclosureSettings() {
        $admin = new Administration();
        $admin->retrieveSettings();

        if (isset($admin->settings["disclosure_enable"]) && !empty($admin->settings["disclosure_enable"])) {
            $this->includeDisclosure = true;
            $this->disclosureContent = $admin->settings["disclosure_text"];
        }
    }
}
