<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * InboundEmailUtils provides utility functions to assist with handling
 * InboundEmail processes
 */
class InboundEmailUtils
{

    /**
     * Decodes a string of text with the specified encoding
     *
     * @param string $text the text to decode
     * @param string $encoding the encoding scheme
     * @return string the decoded text
     */
    public static function handleTransferEncoding($text, $encoding)
    {
        switch (strtolower($encoding)) {
            case 'base64':
                return base64_decode($text);
            case 'quoted-printable':
                return quoted_printable_decode($text);
            default:
                return $text;
        }
    }

    /**
     * Handles translating text from original encoding into UTF-8
     *
     * @param string text test to be re-encoded
     * @param string charset original character set
     * @return string utf8 re-encoded text
     */
    public static function handleCharsetTranslation($text, $charset)
    {
        global $locale;

        if (empty($charset)) {
            $GLOBALS['log']->debug("***ERROR: ImapOauthMailer::handleCharsetTranslation() called without a \$charset!");
            $GLOBALS['log']->debug("***STACKTRACE: ".print_r(debug_backtrace(), true));
            return $text;
        }

        // typical headers have no charset - let destination pick (since it's all ASCII anyways)
        if (strtolower($charset) == 'default' || strtolower($charset) == 'utf-8') {
            return $text;
        }

        return $locale->translateCharset($text, $charset);
    }
}
