<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Creates demo data for the team table
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: TeamDemoData.php,v 1.7 2006/06/06 17:57:53 majed Exp $

class TeamDemoData
{
	var $_team;
	var $_large_scale_test;

	/**
	 * Constructor for creating demo data for teams
	 */
	function TeamDemoData($seed_team, $large_scale_test = false)
	{
		$this->_team = $seed_team;
		$this->_large_scale_test = $large_scale_test;
	}
	
	/**
	 * 
	 */
	function create_demo_data()
	{
		if (!$this->_team->retrieve("East"))
		{
			$this->_team->create_team("East", "This is the team for the East", "East");
		}

		if (!$this->_team->retrieve("West"))
		{
			$this->_team->create_team("West", "This is the team for the West", "West");
		}

		if($this->_large_scale_test)
		{
			$team_list = $this->_seed_data_get_team_list();
			foreach($team_list as $team_name)
			{
				$this->_quick_create($team_name);
			}
		}
		
		// Create the west team memberships
		$this->_team->retrieve("West");
		$this->_team->add_user_to_team("sarah_id");
		$this->_team->add_user_to_team("sally_id");
		$this->_team->add_user_to_team("max_id");

		// Create the east team memberships
		$this->_team->retrieve("East");
		$this->_team->add_user_to_team("will_id");
		$this->_team->add_user_to_team("chris_id");
		
	}

	function create_demo_data_jp()
	{
		if (!$this->_team->retrieve("Eastイースト"))
		{
			$this->_team->create_team("Eastイースト", "これは東のためのチームです。", "east");
		}

		if (!$this->_team->retrieve("Westウエスト"))
		{
			$this->_team->create_team("Westウエスト", "これは西のためのチームです。 ", "west");
		}

		if($this->_large_scale_test)
		{
			$team_list = $this->_seed_data_get_team_list();
			foreach($team_list as $team_name)
			{
				$this->_quick_create($team_name);
			}
		}
		
		// Create the west team memberships
		$this->_team->retrieve("west");
		$this->_team->add_user_to_team("sarah_id");
		$this->_team->add_user_to_team("sally_id");
		$this->_team->add_user_to_team("max_id");

		// Create the east team memberships
		$this->_team->retrieve("east");
		$this->_team->add_user_to_team("will_id");
		$this->_team->add_user_to_team("chris_id");
		
	}

	
	/**
	 * 
	 */
	function get_random_team()
	{
		$team_list = $this->_seed_data_get_team_list();
		$team_list_size = count($team_list);
		$random_index = mt_rand(0,$team_list_size-1);
		
		return $team_list[$random_index];
	}
	
	/**
	 * 
	 */
	function _seed_data_get_team_list()
	{
		$teams = Array();

		$teams[] = "north";
		$teams[] = "south";
		$teams[] = "east";
		$teams[] = "west";
		$teams[] = "left";
		$teams[] = "right";
		$teams[] = "in";
		$teams[] = "out";
		$teams[] = "fly";
		$teams[] = "walk";
		$teams[] = "crawl";
		$teams[] = "pivot";
		$teams[] = "money";
		$teams[] = "dinero";
		$teams[] = "shadow";
		$teams[] = "roof";
		$teams[] = "sales";
		$teams[] = "pillow";
		$teams[] = "feather";

		return $teams;
	}
	
	/**
	 * 
	 */
	function _quick_create($name)
	{
		if (!$this->_team->retrieve($name))
		{
			$this->_team->create_team($name, "This is the team for the $name", $name);
		}
	}
	
	
}
?>
