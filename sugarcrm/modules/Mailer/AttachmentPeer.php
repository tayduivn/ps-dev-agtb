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

require_once "MailerException.php"; // requires MailerException in order to throw exceptions of that type
require_once "EmbeddedImage.php";   // requires Attachment and EmbeddedImage, which imports Attachment

/**
 * This class encapsulates properties and behavior of an attachment so that a common interface can be expected
 * no matter what package is being used to deliver email.
 */
class AttachmentPeer
{
    /**
     * Constructs an attachment from the SugarBean that is passed in.
     *
     * @static
     * @access public
     * @param SugarBean $bean required
     * @return Attachment
     * @throws MailerException
     */
    public static function attachmentFromSugarBean(SugarBean $bean) {
        $filePath = null;
        $fileName = null;
        $mimeType = "";

        if ($bean instanceof Document) {
            if (empty($bean->id)) {
                throw new MailerException(
                    "Invalid Attachment: document not found",
                    MailerException::InvalidAttachment
                );
            }
            $document_revision_id = $bean->document_revision_id;
            $documentRevision = new DocumentRevision();
            if (!empty($document_revision_id)) {
                $documentRevision->retrieve($bean->document_revision_id);
            }
            if (empty($document_revision_id) || $documentRevision->id != $document_revision_id) {
                throw new MailerException(
                    "Invalid Attachment: Document with Id (" . $bean->id . ")  contains an invalid or empty revision id: (" . $document_revision_id . ")",
                    MailerException::InvalidAttachment
                );
            }
            $bean = $documentRevision;
        }

        $beanName = get_class($bean);
        switch ($beanName) {
            case "Note":
            case "DocumentRevision":
                $filePath = "upload/{$bean->id}";
                $fileName = empty($bean->filename) ? $bean->name : $bean->filename;
                $mimeType = empty($bean->file_mime_type) ? $mimeType : $bean->file_mime_type;
                break;
            default:
                throw new MailerException(
                    "Invalid Attachment: SugarBean '{$beanName}' not supported as an Email Attachment",
                    MailerException::InvalidAttachment
                );
                break;
        }

        // Path must Exist and Must be a Regular File
        if (!is_file($filePath)) {
            throw new MailerException(
                "Invalid Attachment: file not found: {$filePath}",
                MailerException::InvalidAttachment
            );
        }

        $attachment = new Attachment($filePath, $fileName, Encoding::Base64, $mimeType);

        return $attachment;
    }


    /**
     * Constructs an embedded image from the SugarBean that is passed in.
     *
     * @static
     * @access public
     * @param SugarBean $bean required
     * @param $cid required
     * @return EmbeddedImage
     * @throws MailerException
     */
    public static function embeddedImageFromSugarBean(SugarBean $bean, $cid) {
        $beanName = get_class($bean);
        $filePath = null;
        $fileName = null;
        $mimeType = "";

        switch ($beanName) {
            case "Note":
                $filePath = "upload/{$bean->id}";
                $fileName = empty($bean->filename) ? $bean->name : $bean->filename;
                $mimeType = empty($bean->file_mime_type) ? $mimeType : $bean->file_mime_type;
                break;
            default:
                throw new MailerException(
                    "Invalid Attachment: SugarBean '{$beanName}' not supported as an Email EmbeddedImage",
                    MailerException::InvalidAttachment
                );
                break;
        }

        // Path must Exist and Must be a Regular File
        if (!is_file($filePath)) {
            throw new MailerException(
                "Invalid Attachment: file not found: {$filePath}",
                MailerException::InvalidAttachment
            );
        }

        $embeddedImage = new EmbeddedImage($cid, $filePath, $fileName, Encoding::Base64, $mimeType);

        return $embeddedImage;
    }
}
