<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

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

require_once('MailerConfiguration.php');
require_once('SimpleMailer.php');
require_once('include/OutboundEmail/OutboundEmail.php');

class SugarMailer extends SimpleMailer
{
    private $locale;
    private $sugar_config;
    private $admin_settings;
    private $notes;

    /**
     * @param MailerConfiguration
     */
    public function __construct(MailerConfiguration $mailerConfig) {
        global $locale;
        global $sugar_config;

        $admin = new Administration();
        $admin->retrieveSettings();
        $this->admin_settings = $admin->settings;
        $this->locale         = $locale;
        $this->sugar_config   = $sugar_config;

        parent::__construct($mailerConfig);
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
        $this->handleAttachments();

        parent::send();
    }


    /**
     * Handles any final updates to document prior to sending. Updates include Charset translation for all
     * visual parts of the email abd optional inclusion of administrator-defined Disclosure Text
     */
    protected function prepareMessageContent() {
        $OBCharset = $this->locale->getPrecedentPreference('default_email_charset');

        if (isset($this->admin_settings['disclosure_enable']) && !empty($this->admin_settings['disclosure_enable'])) {
            $disclosureText = $this->admin_settings['disclosure_text'];
            $this->htmlBody .= "<br />&nbsp;<br />{$disclosureText}";
            $this->textBody .= "\r\r{$disclosureText}";
        }

        $headers        = $this->headers;
        $this->htmlBody = from_html($this->locale->translateCharset(trim($this->htmlBody), 'UTF-8', $OBCharset));
        $this->textBody = from_html($this->locale->translateCharset(trim($this->textBody), 'UTF-8', $OBCharset));
        $subjectUTF8    = from_html(trim($headers->getSubject()));
        $subject        = $this->locale->translateCharset($subjectUTF8, 'UTF-8', $OBCharset);
        $headers->setSubject($subject);

        // HTML email RFC compliance
        if (strpos($this->htmlBody, '<html') === false) {
            $langHeader     = get_language_header();
            $head           = <<<eoq
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" {$langHeader}>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$OBCharset}" />
<title>{$subject}</title>
</head>
<body>
eoq;
            $this->htmlBody = $head . $this->htmlBody . "</body></html>";
        }

        $from = $headers->getFrom();
        $from->setName($this->locale->translateCharset(trim($from->getName()), 'UTF-8', $OBCharset));
        $headers->setFrom($from);
    }

    /**
     *
     */
    protected function handleAttachments() {
        //replace references to cache/images with cid tag
        $this->htmlBody = str_replace(sugar_cached('images/'), 'cid:', $this->htmlBody);

        if (empty($this->notes)) {
            return;
        }

        $this->replaceImageByRegex("(?:{$this->sugar_config['site_url']})?/?cache/images/", sugar_cached("images/"));

        //Replace any embeded images using the secure entryPoint for src url.
        $this->replaceImageByRegex("(?:{$this->sugar_config['site_url']})?index.php[?]entryPoint=download&(?:amp;)?[^\"]+?id=", "upload://", true);

        //Handle regular attachments.
        foreach ($this->notes as $note) {
            $mime_type     = 'text/plain';
            $file_location = '';
            $filename      = '';

            if ($note->object_name == 'Note') {
                if (!empty($note->file->temp_file_location) && is_file($note->file->temp_file_location)) {
                    $file_location = $note->file->temp_file_location;
                    $filename      = $note->file->original_file_name;
                    $mime_type     = $note->file->mime_type;
                } else {
                    $file_location = "upload://{$note->id}";
                    $filename      = $note->id . $note->filename;
                    $mime_type     = $note->file_mime_type;
                }
            } elseif ($note->object_name == 'DocumentRevision') { // from Documents
                $filename      = $note->id . $note->filename;
                $file_location = "upload://$filename";
                $mime_type     = $note->file_mime_type;
            }

            $filename = substr($filename, 36, strlen($filename)); // strip GUID	for PHPMailer class to name outbound file
            if (!$note->embed_flag) {
                $this->addAttachment($file_location, $filename, 'base64', $mime_type);
            }
        }
    }

    /**
     * Replace images with locations specified by regex with cid: images
     * and attach needed files
     *
     * @param string $regex        Regular expression
     * @param string $local_prefix Prefix where local files are stored
     * @param bool   $object       Use attachment object
     */
    protected function replaceImageByRegex($regex, $local_prefix, $object = false) {
        preg_match_all("#<img[^>]*[\s]+src[^=]*=[\s]*[\"']($regex)(.+?)[\"']#si", $this->htmlBody, $matches);
        $i = 0;
        foreach ($matches[2] as $match) {
            $filename      = urldecode($match);
            $cid           = $filename;
            $file_location = $local_prefix . $filename;
            if (!file_exists($file_location))
                continue;
            if ($object) {
                if (preg_match('#&(?:amp;)?type=([\w]+)#i', $matches[0][$i], $typematch)) {
                    switch (strtolower($typematch[1])) {
                        case 'documents':
                            $beanname = 'DocumentRevisions';
                            break;
                        case 'notes':
                            $beanname = 'Notes';
                            break;
                    }
                }
                $mime_type = "application/octet-stream";
                if (isset($beanname)) {
                    $bean = SugarModule::get($beanname)->loadBean();
                    $bean->retrieve($filename);
                    if (!empty($bean->id)) {
                        $mime_type = $bean->file_mime_type;
                        $filename  = $bean->filename;
                    }
                }
            } else {
                $mime_type = "image/" . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            }
            $this->AddEmbeddedImage($file_location, $cid, $filename, 'base64', $mime_type);
            $i++;
        }
        //replace references to cache with cid tag
        $this->htmlBody = preg_replace("|\"$regex|i", '"cid:', $this->htmlBody);
        // remove bad img line from outbound email
        $this->htmlBody = preg_replace('#<img[^>]+src[^=]*=\"\/([^>]*?[^>]*)>#sim', '', $this->htmlBody);
    }
}
