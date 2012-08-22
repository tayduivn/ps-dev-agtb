<?php
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

require_once('modules/Mailer/SimpleMailer.php');

class SimpleMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function testReset_LoadDefaultConfigsReplacesTheExistingConfigsWithTheDefaults_SubjectIsNull() {
		$mailer = new SimpleMailer();

		$initialConfigs = array(
			'protocol' => 'asdf', // some asinine value that could never possibly exist
		);
		$mailer->setConfigs($initialConfigs);
		$configs = $mailer->getConfigs();
		$expected = $initialConfigs['protocol'];
		$actual = $configs['protocol'];
		self::assertEquals($expected, $actual, "The protocols don't match");

		$expected = "this is a subject";
		$mailer->setSubject($expected);
		$actual = $mailer->getSubject();
		self::assertEquals($expected, $actual, "The subjects don't match");

		$mailer->reset();

		$defaultConfigs = $mailer->getConfigs();
		$expected = $initialConfigs['protocol'];
		$actual = $defaultConfigs['protocol'];
		self::assertNotEquals($expected, $actual, "The protocols shouldn't match");

		$actual = $mailer->getSubject();
		self::assertNull($actual, "The subject isn't null");
	}

	public function testMergeConfigs_NewConfigAddedToDefaultConfigs() {
		$mailer = new SimpleMailer();

		$additionalConfigs = array(
			'foo' => 'bar',
		);
		$mailer->mergeConfigs($additionalConfigs);
		$configs = $mailer->getConfigs();

		$expected = 'protocol';
		self::assertArrayHasKey($expected, $configs, "The {$expected} key is missing");

		$expected = 'foo';
		self::assertArrayHasKey($expected, $configs, "The {$expected} key is missing");

		$expected = 'bar';
		$actual = $configs['foo'];
		self::assertEquals($expected, $actual);
	}

	public function testSend() {
		//@todo test the various code paths of send()
	}
}
