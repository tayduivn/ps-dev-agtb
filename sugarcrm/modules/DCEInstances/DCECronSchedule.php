<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
class DCECronSchedule extends SugarBean{
	/*
	 * GUID of this record
	 */
	public $id;
	/*
	 * The GUID of the instance this record relates to
	 */
	public $instance_id;
	/*
	 * Keeps track of whether a cron_manager has picked up this record to process the
	 * cron.
	 */
	public $is_locked;
	/*
	 * When the lock was picked up.
	 */
	public $lock_date;
	/*
	 * Once the lock is released, when can the cron be run again.
	 */
	public $next_execution_time;
	/*
	 * The ip address of the machine running the cron job.
	 */
	public $server_ip;
	/*
	 * The table name
	 */
	public $table_name = "dcecronschedules";
	/*
	 * The module directory this bean lives in
	 */
	public $module_dir = "DCEInstances";
	/*
	 * The name of this object.
	 */
	public $object_name = "DCECronSchedule";
	
	/**
	 * Constructor - create a new instance of DCECronSchedule as well as parent SugarBean.
	 *
	 */
	public function __construct(){
		parent::SugarBean();
	}
	
}

?>