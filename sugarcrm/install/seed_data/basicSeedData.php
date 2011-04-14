<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: basicSeedData.php 19273 2007-01-12 17:59:46Z liliya $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true && $_SESSION['setup_db_type'] != 'mssql') {
	$case_seed_names = array(
		'プラグが差し込めません',
		'システムが異常に高速に動作中',
		'カスタマイズの支援について',
		'追加ライセンスの購入について',
		'間違ったブラウザを使用する場合の警告メッセージ'
	);
	$note_seed_names_and_Descriptions = array(
		array('お客様情報の追加','3,000人のお客様にコンタクトすること'),
		array('コール情報','再コールにより電話。いい話になった。'),
		array('誕生日','担当者は10月生まれ'),
		array('お歳暮','お歳暮は歓迎される。来年のためにリスト化すること。')
	);
	$call_seed_data_names = array(
		'提案について詳細情報を得ること',
		'メッセージを残した',
		'都合が悪いとのこと。掛けなおし',
		'レビュープロセスの討議'
	);
} else {
	$case_seed_names = array(
		'Having Trouble Plugging It In',
		'System is Performing Too Fast',
		'Need assistance with large customization',
		'Need to Purchase Additional Licenses',
		'Warning message when using the wrong browser'
	);
	$note_seed_names_and_Descriptions = array(
		array('More Account Information','This could turn into a 3,000 user opportunity'),
		array('Call Information','We had a call.  The call went well.'),
		array('Birthday Information','The Owner was born in October'),
		array('Holliday Gift','The holliday gift was appreciated.  Put them on the list for next year as well.')
	);
	$call_seed_data_names = array(
		'Get More information on the proposed deal',
		'Left a message',
		'Bad time, will call back',
		'Discuss Review Process'
	);
}

?>
