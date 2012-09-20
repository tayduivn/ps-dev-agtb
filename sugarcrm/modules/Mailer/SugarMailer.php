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

require_once "SimpleMailer.php";

class SugarMailer extends SimpleMailer
{
    private $includeDisclosure = false;
    private $disclosureContent;

    /**
     * @param MailerConfiguration
     */
    public function __construct(MailerConfiguration $mailerConfig) {
        parent::__construct($mailerConfig);
        $this->retrieveDisclosureSettings();
    }

    /**
     * Optionally set notes (Sugar Documents and Uploaded Files)
     *
     * @param $notesArray  array of note beans
     */
    public function setNotes(array $notesArray) {
        $this->notes = $notesArray;
    }

    /**
     * a potential solution to allow for manipulation of the message parts at send time without actually
     * changing the message parts beyond repair
     */
    public function send() {
        $this->prepareMessageContent();

        parent::send();
    }

    /**
     * Handles any final updates to document prior to sending. Updates include Charset translation for all
     * visual parts of the email abd optional inclusion of administrator-defined Disclosure Text
     */
    protected function prepareMessageContent() {
        global $locale, $sugar_config;
        $charset = $locale->getPrecedentPreference("default_email_charset");
        $siteUrl = $sugar_config["site_url"];

        $from = $this->headers->getFrom();
        $from->setName($locale->translateCharset($from->getName(), "UTF-8", $charset));
        $this->headers->setFrom($from);

        $subject = $this->headers->getSubject();
        $subject = from_html($locale->translateCharset($subject, "UTF-8", $charset));
        $this->setSubject($subject);

        $this->setTextBody(from_html($locale->translateCharset($this->textBody, "UTF-8", $charset)));

        $htmlBody = from_html($locale->translateCharset($this->htmlBody, "UTF-8", $charset));

        // HTML email RFC compliance
        if (strpos($htmlBody, "<html") === false) {
            $langHeader = get_language_header();
            $head       = <<<eoq
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" {$langHeader}>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
<title>{$subject}</title>
</head>
<body>
eoq;
            $htmlBody = "{$head}{$htmlBody}</body></html>";
        }

        if ($this->includeDisclosure) {
            $htmlBody .= "<br />&nbsp;<br />{$this->disclosureContent}"; //@todo why do we include &nbsp;?
            $htmlBody .= "\r\r{$this->disclosureContent}"; //@todo why are we using /r?
        }

        // replace references to cache/images with cid tag
        $htmlBody = str_replace(sugar_cached("images/"), "cid:", $htmlBody);

        // replace any embeded images using cache/images for src url
        $htmlBody = $this->convertInlineImageToEmbeddedImage(
            $htmlBody,
            "(?:{$siteUrl})?/?cache/images/",
            sugar_cached("images/")
        );

        // replace any embeded images using the secure entryPoint for src url
        $htmlBody = $this->convertInlineImageToEmbeddedImage(
            $htmlBody,
            "(?:{$siteUrl})?index.php[?]entryPoint=download&(?:amp;)?[^\"]+?id=",
            "upload://",
            true
        );

        $this->setHtmlBody($htmlBody);
    }

    /**
     * Replace images with locations specified by regex with cid: images and attach needed files.
     *
     * @param string $body
     * @param string $regex        Regular expression
     * @param string $localPrefix Prefix where local files are stored
     * @param bool   $object       Use attachment object
     * @return string
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

                $this->addEmbeddedImage($fileLocation, $cid, $filename, Encoding::Base64, $mimeType);
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
