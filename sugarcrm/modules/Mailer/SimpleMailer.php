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

require_once 'lib/phpmailer/class.phpmailer.php';
require_once 'lib/phpmailer/class.smtp.php';
require_once 'BaseMailer.php';

class SimpleMailer extends BaseMailer
{
	const Protocol   = 'smtp';
	const SecureNone = '';
	const SecureSsl  = 'ssl';
	const SecureTls  = 'tls';

	public function loadDefaultConfigs() {
		parent::loadDefaultConfigs();

		$defaults = array(
			'smtp.host'         => 'localhost',
			'smtp.port'         => 25,
			'smtp.secure'       => self::SecureNone,
			'smtp.authenticate' => false,
			'smtp.username'     => '',
			'smtp.password'     => '',
		);

		$this->mergeConfigs($defaults);
	}

	public function send() {
		try {
			$mailer = new PHPMailer(true); // use PHPMailer with exceptions
			$this->transferConfigurations($mailer);
			$this->connectToHost($mailer);
			$this->transferHeaders($mailer);
			$this->transferRecipients($mailer);
			$this->transferBody($mailer);
			$this->transferAttachments($mailer);

			if (!$mailer->IsError()) {
				$mailer->Send();
			}

			if ($mailer->IsError()) {
				throw new MailerException($mailer->ErrorInfo);
			}
		} catch (MailerException $me) {
			//@todo consider using status codes and grouping them based on the error level that should be used
			// so that different error levels can be logged
			// could also catch different Exception classes that extend MailerException and log at the level
			// particular to that exception type
			$me->log('error');
			return false;
		}

		return true;
	}

	private function transferConfigurations(PHPMailer &$mailer) {
        // explicitly set the language even though PHPMailer will do it on its own
        if (!$mailer->SetLanguage()) {
            //@todo do we really care if it fails since english will be used anyway?
            throw new MailerException("Failed to load the language file");
        }

		// transfer the basic configurations
		$mailer->Mailer   = self::Protocol;
		$mailer->Hostname = $this->configs['hostname'];
		$mailer->CharSet  = $this->configs['charset'];
		$mailer->Encoding = $this->configs['encoding'];
		$mailer->WordWrap = $this->configs['wordwrap'];

		// transfer the smtp configurations
		$mailer->Host       = $this->configs['smtp.host'];
		$mailer->Port       = $this->configs['smtp.port'];
		$mailer->SMTPSecure = $this->configs['smtp.secure'];
		$mailer->SMTPAuth   = $this->configs['smtp.authenticate'];
		$mailer->Username   = $this->configs['smtp.username'];
		$mailer->Password   = $this->configs['smtp.password']; //@todo do we need to wrap this value in from_html()?
	}

	private function connectToHost(PHPMailer &$mailer) {
		try {
            $mailer->smtp = new SMTP();
            $mailer->SmtpConnect();
        } catch (Exception $e) {
            //@todo need to tell the class what error messages to use, so the following is for reference only
//			global $app_strings;
//			if(isset($this->oe) && $this->oe->type == "system") {
//				$this->SetError($app_strings['LBL_EMAIL_INVALID_SYSTEM_OUTBOUND']);
//			} else {
//				$this->SetError($app_strings['LBL_EMAIL_INVALID_PERSONAL_OUTBOUND']);
//			}
			throw new MailerException("Failed to connect to the remote server");
		}
	}

	private function transferHeaders(PHPMailer &$mailer) {
		// packageHeaders() will throw an exception if errors occur and that exception will be caught by send()
		$headers = $this->headers->packageHeaders();

		foreach ($headers as $key => $value) {
			switch ($key) {
				case EmailHeaders::From:
					$mailer->From = $value[0];
					$mailer->FromName = $value[1]; //@todo might not want to require this value
					break;
				case EmailHeaders::ReplyTo:
					//$mailer->ClearReplyTos(); // only necessary if the PHPMailer object can be re-used
                    try {
                        if ($mailer->AddReplyTo($value[0], $value[1])) { //@todo might not want to require the second value
                            // doesn't matter what the message is since we're going to eat phpmailerExceptions
                            throw new phpmailerException();
                        }
                    } catch (Exception $e) {
                        throw new MailerException("Failed to add the Reply-To header");
                    }
					break;
				case EmailHeaders::Sender:
					$mailer->Sender = $value;
					break;
				case EmailHeaders::MessageId:
					$mailer->MessageID = $value;
					break;
				case EmailHeaders::Priority:
					$mailer->Priority = $value;
					break;
				case EmailHeaders::DispositionNotificationTo:
					$mailer->ConfirmReadingTo = $value;
					break;
				case EmailHeaders::Subject:
					$mailer->Subject = $value;
					break;
				default:
					// it's not known, so it must be a custom header
					$mailer->AddCustomHeader("{$key}:{$value}");
					break;
			}
		}
	}

	private function transferRecipients(PHPMailer &$mailer) {
		//$mailer->ClearAllRecipients(); // only necessary if the PHPMailer object can be re-used
		$to = $this->recipients->getTo();
		$cc = $this->recipients->getCc();
		$bcc = $this->recipients->getBcc();

		//@todo should you be able to initiate a send without any To recipients?
		foreach ($to as $recipient) {
			$recipient->decode();

            try {
                $mailer->AddAddress($recipient->getEmail(), $recipient->getName());
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
                //throw new MailerException("Failed to add the recipient known by " . $recipient->getEmail());
            }
		}

		foreach ($cc as $recipient) {
			$recipient->decode();

            try {
                $mailer->AddCC($recipient->getEmail(), $recipient->getName());
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
                //throw new MailerException("Failed to add the CC recipient known by " . $recipient->getEmail());
            }
		}

		foreach ($bcc as $recipient) {
			$recipient->decode();

            try {
                $mailer->AddBCC($recipient->getEmail(), $recipient->getName());
            } catch (Exception $e) {
                // eat the exception for now as we'll send to as many valid recipients as possible
                //throw new MailerException("Failed to add the BCC recipient known by " . $recipient->getEmail());
            }
		}
	}

	/**
	 * @throws MailerException
	 */
	protected function transferBody(PHPMailer &$mailer) {
		$hasText = $this->hasMessagePart($this->textBody);
		$hasHtml = $this->hasMessagePart($this->htmlBody);

		if (!$hasText && !$hasHtml) {
			throw new MailerException("No email body was provided");
		}

		if ($hasHtml) {
			$mailer->IsHTML(true);
			$mailer->Encoding = self::EncodingBase64; // so that embedded images are encoding properly
			$mailer->Body = $this->htmlBody;
		}

		if ($hasText && $hasHtml) {
			$mailer->AltBody = $this->textBody;
		} elseif ($hasText) {
			$mailer->Body = $this->textBody;
		} else {
			// you should never actually send an email without a plain-text part, but we'll allow it (for now)
			//throw new MailerException("No text body was provided");
		}
	}

	/**
	 * Transfers both file attachments and embedded images to PHPMailer.
	 *
	 * @throws MailerException
	 */
	private function transferAttachments(PHPMailer &$mailer) {
		//$mailer->ClearAttachments(); // only necessary if the PHPMailer object can be re-used

		foreach ($this->attachments as $attachment) {
			if ($attachment instanceof Attachment) {
				if (!$mailer->AddAttachment(
					$attachment->getPath(),
					$attachment->getName(),
					$attachment->getEncoding(),
					$attachment->getMimeType())
				) {
					throw new MailerException("Invalid attachment");
				}
			} elseif ($attachment instanceof EmbeddedImage) {
				if (!$mailer->AddEmbeddedImage(
					$attachment->getPath(),
					$attachment->getCid(),
					$attachment->getName(),
					$attachment->getEncoding(),
					$attachment->getMimeType())
				) {
					throw new MailerException("Invalid image");
				}
			} else {
				throw new MailerException("Invalid file");
			}
		}
	}
}
