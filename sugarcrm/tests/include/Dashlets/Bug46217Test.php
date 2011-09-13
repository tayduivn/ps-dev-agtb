<?php

/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

/**
 * Created: Sep 12, 2011
 */
include_once('include/Dashlets/DashletRssFeedTitle.php');

class Bug46217Test extends Sugar_PHPUnit_Framework_TestCase {

	public $rssFeedClass;
	
	public function setUp() {
		$this->rssFeedClass = new DashletRssFeedTitle("");
	}
	
	public function tearDown() {
		unset($this->rssFeedClass);
	}
	
	public function dataProviderCorrectParse() {
		return array(
			array('<?xml version="1.0" encoding="UTF-8"?>
				<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>
				<title>France Info</title>
				<link>http://www.france-info.com</link>
				<description>France Info - A la Une</description>
				<image>
				<url>http://www.france-info.com/IMG/siteon0.gif</url>
				<title>France Info</title>
				<link>http://www.france-info.com</link>
				</image>', 
				
				'France Info'
			),
			array('<?xml version="1.0" encoding="UTF-8" ?>
				<rss version="2.0">
				<channel>
				<title><![CDATA[RSS Title]]></title>
				<description>This is an example of an RSS feed</description>
				<link>http://www.someexamplerssdomain.com/main.html</link>
				<lastBuildDate>Mon, 06 Sep 2010 00:01:00 +0000 </lastBuildDate>
				<pubDate>Mon, 06 Sep 2009 16:45:00 +0000 </pubDate>',
				
				'RSS Title'
			),
		);
	}
	
	/**
	 * @dataProvider dataProviderCorrectParse
	 */
	public function testCorrectTitleParse($rssFeed, $expectedTitle) {
		$this->rssFeedClass->contents = $rssFeed;
		$this->rssFeedClass->getTitle();
		$this->assertEquals($expectedTitle, $this->rssFeedClass->title);
		$this->rssFeedClass->convertEncoding();
		$this->assertEquals($expectedTitle, $this->rssFeedClass->title);
	}
}