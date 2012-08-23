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

require_once 'EmailIdentity.php';

interface IMailer
{
	public function __construct();

	public function reset();

	/**
	 * Initialize or replace the configurations with the defaults for this sending strategy.
	 */
	public function loadDefaultConfigs();

	/**
	 * Use this method to replace the default configurations. This will replace the previous configurations;
	 * it will not merge the configurations.
	 *
	 * @param array $configs
	 */
	public function setConfigs($configs);

	/**
	 * Merge the passed in configurations with the existing configurations.
	 *
	 * @param array $configs
	 */
	public function mergeConfigs($configs);

	/**
	 * Replace a specific configuration. Doesn't allow for overwriting smtp configs, for now.
	 *
	 * @param string $config
	 * @param mixed  $value
	 */
	public function setConfig($config, $value);

	/**
	 * @return array
	 */
	public function getConfigs();

	/**
	 * @param $id
	 */
	public function setMessageId($id);

	/**
	 * @param int $priority 1 = High, 3 = Normal, 5 = Low
	 */
	public function setPriority($priority = 3);

	/**
	 * @param bool $request
	 */
	public function setRequestConfirmation($request = false);

	/**
	 * @param EmailIdentity $from
	 */
	public function setFrom(EmailIdentity $from);

	/**
	 * @param EmailIdentity $replyTo
	 */
	public function setReplyTo(EmailIdentity $replyTo);

	/**
	 * @param EmailIdentity $sender
	 */
	public function setSender(EmailIdentity $sender);

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsTo($recipients = array());

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsCc($recipients = array());

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsBcc($recipients = array());

	/**
	 * @param string $subject
	 */
	public function setSubject($subject);

	/**
	 * @return string
	 */
	public function getSubject();

	/**
	 * @param string $textBody
	 */
	public function setTextBody($textBody);

	/**
	 * @param string $htmlBody
	 */
	public function setHtmlBody($htmlBody);

	public function addAttachment($path, $name = null, $encoding = 'base64', $mimeType = 'application/octet-stream');

	public function addEmbeddedImage($path, $cid, $name = null, $encoding = 'base64', $mimeType = 'application/octet-stream');

	public function send();
}
