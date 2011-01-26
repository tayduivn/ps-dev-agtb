<?PHP
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class SugarFeed extends Basic {
	var $new_schema = true;
	var $module_dir = 'SugarFeed';
	var $object_name = 'SugarFeed';
	var $table_name = 'sugarfeed';
	var $importable = false;

		var $id;
		var $name;
		var $date_entered;
		var $date_modified;
		var $modified_user_id;
		var $modified_by_name;
		var $created_by;
		var $created_by_name;
		var $description;
		var $deleted;
		var $created_by_link;
		var $modified_user_link;
		//BEGIN SUGARCRM flav=pro ONLY
		var $team_id;
		var $team_name;
		var $team_link;
		//END SUGARCRM flav=pro ONLY
		var $assigned_user_id;
		var $assigned_user_name;
		var $assigned_user_link;

	function SugarFeed(){
		parent::Basic();
	}

    static function activateModuleFeed( $module, $updateDB = true ) {
        if ( $module != 'UserFeed' ) {
            // UserFeed is a fake module, used for the user postings to the feed
            // Don't try to load up any classes for it
            $fileList = SugarFeed::getModuleFeedFiles($module);

            foreach ( $fileList as $fileName ) {
                $feedClass = substr(basename($fileName),0,-4);

                require_once($fileName);
                $tmpClass = new $feedClass();
                $tmpClass->installHook($fileName,$feedClass);
            }
        }
        if ( $updateDB == true ) {

            $admin = new Administration();
            $admin->saveSetting('sugarfeed','module_'.$admin->db->quote($module),'1');
        }
    }

    static function disableModuleFeed( $module, $updateDB = true ) {
        if ( $module != 'UserFeed' ) {
            // UserFeed is a fake module, used for the user postings to the feed
            // Don't try to load up any classes for it
            $fileList = SugarFeed::getModuleFeedFiles($module);

            foreach ( $fileList as $fileName ) {
                $feedClass = substr(basename($fileName),0,-4);

                require_once($fileName);
                $tmpClass = new $feedClass();
                $tmpClass->removeHook($fileName,$feedClass);
            }
        }

        if ( $updateDB == true ) {

            $admin = new Administration();
            $admin->saveSetting('sugarfeed','module_'.$admin->db->quote($module),'0');
        }
    }

    static function flushBackendCache( ) {
        // This function will flush the cache files used for the module list and the link type lists
        sugar_cache_clear('SugarFeedModules');
        if ( file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/moduleCache.php') ) {
            unlink($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/moduleCache.php');
        }

        sugar_cache_clear('SugarFeedLinkType');
        if ( file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/linkTypeCache.php') ) {
            unlink($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/linkTypeCache.php');
        }
    }


    static function getModuleFeedFiles( $module ) {
        $baseDirList = array('modules/'.$module.'/SugarFeeds/', 'custom/modules/'.$module.'/SugarFeeds/');

        // We store the files in a list sorted by the filename so you can override a default feed by
        // putting your replacement feed in the custom directory with the same filename
        $fileList = array();

        foreach ( $baseDirList as $baseDir ) {
            if ( ! file_exists($baseDir) ) {
                continue;
            }
            $d = dir($baseDir);
            while ( $file = $d->read() ) {
                if ( $file{0} == '.' ) { continue; }
                if ( substr($file,-4) == '.php' ) {
                    // We found one
                    $fileList[$file] = $baseDir.$file;
                }
            }
        }

        return($fileList);
    }

    static function getActiveFeedModules( ) {
        // Stored in a cache somewhere
        $feedModules = sugar_cache_retrieve('SugarFeedModules');
        if ( $feedModules != null ) {
            return($feedModules);
        }

        // Already stored in a file
        if ( file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/moduleCache.php') ) {
            require_once($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/moduleCache.php');
            sugar_cache_put('SugarFeedModules',$feedModules);
            return $feedModules;
        }

        // Gotta go looking for it

        $admin = new Administration();
        $admin->retrieveSettings();

        $feedModules = array();
        if ( isset($admin->settings['sugarfeed_enabled']) && $admin->settings['sugarfeed_enabled'] == '1' ) {
            // Only enable modules if the feed system is enabled
            foreach ( $admin->settings as $key => $value ) {
                if ( strncmp($key,'sugarfeed_module_',17) === 0 ) {
                    // It's a module setting
                    if ( $value == '1' ) {
                        $moduleName = substr($key,17);
                        $feedModules[$moduleName] = $moduleName;
                    }
                }
            }
        }


        sugar_cache_put('SugarFeedModules',$feedModules);
        if ( ! file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed') ) { mkdir_recursive($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed'); }
        $fd = fopen($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/moduleCache.php','w');
        fwrite($fd,'<'."?php\n\n".'$feedModules = '.var_export($feedModules,true).';');
        fclose($fd);

        return $feedModules;
    }

    static function getAllFeedModules( ) {
        // Uncached, only used from the admin panel and during installation currently
        $feedModules = array('UserFeed'=>'UserFeed');

        $baseDirList = array('modules/', 'custom/modules/');
        foreach ( $baseDirList as $baseDir ) {
            if ( ! file_exists($baseDir) ) {
                continue;
            }
            $d = dir($baseDir);
            while ( $module = $d->read() ) {
                if ( file_exists($baseDir.$module.'/SugarFeeds/') ) {
                    $dFeed = dir($baseDir.$module.'/SugarFeeds/');
                    while ( $file = $dFeed->read() ) {
                        if ( $file{0} == '.' ) { continue; }
                        if ( substr($file,-4) == '.php' ) {
                            // We found one
                            $feedModules[$module] = $module;
                        }
                    }
                }
            }
        }

        return($feedModules);
    }

    /**
     * pushFeed2
     * This method is a wrapper to pushFeed
     *
     * @param $text String value of the feed's description
     * @param $bean The SugarBean that is triggering the feed
     * @param $link_type boolean value indicating whether or not feed is a link type
     * @param $link_url String value of the URL (for link types only)
     */
    static function pushFeed2($text, $bean, $link_type=false, $link_url=false) {
            self::pushFeed($text, $bean->module_dir, $bean->id
//BEGIN SUGARCRM flav=pro ONLY
                                ,$bean->team_id
//END SUGARCRM flav=pro ONLY
								,$bean->assigned_user_id
								,$link_type
								,$link_url
//BEGIN SUGARCRM flav=pro ONLY
                                ,$bean->team_set_id
//END SUGARCRM flav=pro ONLY
            );
    }

	static function pushFeed($text, $module, $id,
		//BEGIN SUGARCRM flav=pro ONLY
		$team_id,
		//END SUGARCRM flav=pro ONLY
		$record_assigned_user_id=false,
		$link_type=false,
		$link_url=false
		//BEGIN SUGARCRM flav=pro ONLY
		,$team_set_id=''
		//END SUGARCRM flav=pro ONLY
		) {
		$feed = new SugarFeed();
		if(empty($text) || !$feed->ACLAccess('save', true) )return;
		if(!empty($link_url)){
            $linkClass = SugarFeed::getLinkClass($link_type);
            if ( $linkClass !== FALSE ) {
                $linkClass->handleInput($feed,$link_type,$link_url);
            }
        }
        $text = strip_tags(from_html($text));
		$text = '<b>{this.CREATED_BY}</b> ' . $text;
		$feed->name = substr($text, 0, 255);
		if(strlen($text) > 255){
			$feed->description = substr($text, 255, 510);
		}

		if ( $record_assigned_user_id === false ) {
			$feed->assigned_user_id = $GLOBALS['current_user']->id;
		} else {
			$feed->assigned_user_id = $record_assigned_user_id;
		}
		$feed->related_id = $id;
		$feed->related_module = $module;
		//BEGIN SUGARCRM flav=pro ONLY
		$feed->team_id = $team_id;
		$feed->team_set_id = empty($team_set_id) ? $team_id : $team_set_id;
		//END SUGARCRM flav=pro ONLY
		$feed->save();
	}

    static function getLinkTypes() {
        static $linkTypeList = null;

        // Fastest, already stored in the static variable
        if ( $linkTypeList != null ) {
            return $linkTypeList;
        }

        // Second fastest, stored in a cache somewhere
        $linkTypeList = sugar_cache_retrieve('SugarFeedLinkType');
        if ( $linkTypeList != null ) {
            return($linkTypeList);
        }

        // Third fastest, already stored in a file
        if ( file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/linkTypeCache.php') ) {
            require_once($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/linkTypeCache.php');
            sugar_cache_put('SugarFeedLinkType',$linkTypeList);
            return $linkTypeList;
        }

        // Slow, have to actually collect the data
        $baseDirs = array('custom/modules/SugarFeed/linkHandlers/','modules/SugarFeed/linkHandlers');

        $linkTypeList = array();

        foreach ( $baseDirs as $dirName ) {
            if ( !file_exists($dirName) ) { continue; }
            $d = dir($dirName);
            while ( $file = $d->read() ) {
                if ( $file{0} == '.' ) { continue; }
                if ( substr($file,-4) == '.php' ) {
                    // We found one
                    $typeName = substr($file,0,-4);
                    $linkTypeList[$typeName] = $typeName;
                }
            }
        }

        sugar_cache_put('SugarFeedLinkType',$linkTypeList);
        if ( ! file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed') ) { mkdir_recursive($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed'); }
        $fd = fopen($GLOBALS['sugar_config']['cache_dir'].'modules/SugarFeed/linkTypeCache.php','w');
        fwrite($fd,'<'."?php\n\n".'$linkTypeList = '.var_export($linkTypeList,true).';');
        fclose($fd);

        return $linkTypeList;
    }

    static function getLinkClass( $linkName ) {
        $linkTypeList = SugarFeed::getLinkTypes();

        // Have to make sure the linkName is on the list, so they can't pass in linkName's like ../../config.php ... not that they could get anywhere if they did
        if ( ! isset($linkTypeList[$linkName]) ) {
            // No class by this name...
            return FALSE;
        }

        if ( file_exists('custom/modules/SugarFeed/linkHandlers/'.$linkName.'.php') ) {
            require_once('custom/modules/SugarFeed/linkHandlers/'.$linkName.'.php');
        } else {
            require_once('modules/SugarFeed/linkHandlers/'.$linkName.'.php');
        }

        $linkClassName = 'FeedLinkHandler'.$linkName;

        $linkClass = new $linkClassName();

        return($linkClass);
    }

	function get_list_view_data(){
		$data = parent::get_list_view_data();
		if ( !isset($data['TEAM_NAME']) )
		    $data['TEAM_NAME'] = '';
		$delete = '';
		if (ACLController::moduleSupportsACL($data['RELATED_MODULE']) && !ACLController::checkAccess($data['RELATED_MODULE'], 'view', $data['CREATED_BY'] == $GLOBALS['current_user']->id) && !ACLController::checkAccess($data['RELATED_MODULE'], 'list', $data['CREATED_BY'] == $GLOBALS['current_user']->id)){
			$data['NAME'] = '';
			return $data;
		}
        if(is_admin($GLOBALS['current_user']) || (isset($data['CREATED_BY']) && $data['CREATED_BY'] == $GLOBALS['current_user']->id) ) {
            $delete = ' - <a id="sugarFeedDeleteLink'.$data['ID'].'" href="#" onclick=\'SugarFeed.deleteFeed("'. $data['ID'] . '", "{this.id}"); return false;\'>'. $GLOBALS['app_strings']['LBL_DELETE_BUTTON_LABEL'].'</a>';
        }
		$data['NAME'] .= $data['DESCRIPTION'];
		$data['NAME'] =  '<div style="padding:3px">' . html_entity_decode($data['NAME']);
		if(!empty($data['LINK_URL'])){
            $linkClass = SugarFeed::getLinkClass($data['LINK_TYPE']);
            if ( $linkClass !== FALSE ) {
                $data['NAME'] .= $linkClass->getDisplay($data);
            }
		}
        $data['NAME'] .= '<div class="byLineBox"><span class="byLineLeft">';
		$data['NAME'] .= $this->getTimeLapse($data['DATE_ENTERED']) . '&nbsp;</span><div class="byLineRight"><a id="sugarFeedReplyLink'.$data['ID'].'" href="#" onclick=\'SugarFeed.buildReplyForm("'.$data['ID'].'", "{this.id}", this); return false;\'>'.$GLOBALS['app_strings']['LBL_EMAIL_REPLY'].'</a>' .$delete. '</div></div>';

        $data['NAME'] .= $this->fetchReplies($data);
		return  $data ;
	}

    function fetchReplies($data) {
        $seedBean = new SugarFeed;

        $replies = $seedBean->get_list('date_entered',"related_module = 'SugarFeed' AND related_id = '".$data['ID']."'");

        if ( count($replies['list']) < 1 ) {
            return '';
        }


        $replyHTML = '<div class="clear"></div><blockquote>';

        foreach ( $replies['list'] as $reply ) {
            // Setup the delete link
            $delete = '';
            if(is_admin($GLOBALS['current_user']) || $data['CREATED_BY'] == $GLOBALS['current_user']->id) {
                $delete = '<a id="sugarFieldDeleteLink'.$reply->id.'" href="#" onclick=\'SugarFeed.deleteFeed("'. $reply->id . '", "{this.id}"); return false;\'>'. $GLOBALS['app_strings']['LBL_DELETE_BUTTON_LABEL'].'</a>';
            }

            $image_url = 'include/images/blank.gif';
            if ( isset($reply->created_by) ) {
                $user = loadBean('Users');
                $user->retrieve($reply->created_by);
                if ( !empty($user->picture) ) {
                    $image_url = 'index.php?entryPoint=download&id='.$user->picture.'&type=SugarFieldImage&isTempFile=1';
                }
            }
            $replyHTML .= '<div style="float: left; margin-right: 3px; width: 50px; height: 50px;"><img src="'.$image_url.'" style="max-width: 50px; max-height: 50px;"></div> ';
            $replyHTML .= str_replace("{this.CREATED_BY}",get_assigned_user_name($reply->created_by),html_entity_decode($reply->name)).'<br>';
            $replyHTML .= '<div class="byLineBox"><span class="byLineLeft">'. $this->getTimeLapse($reply->date_entered) . '&nbsp;</span><div class="byLineRight">  &nbsp;' .$delete. '</div></div><div class="clear"></div>';
        }

        $replyHTML .= '</blockquote>';
        return $replyHTML;

    }

	static function getTimeLapse($startDate)
	{
		$seconds = $GLOBALS['timedate']->getNow()->ts - $GLOBALS['timedate']->fromUser($startDate)->ts;
		$minutes =   $seconds/60;
		$seconds = $seconds % 60;
		$hours = floor( $minutes / 60);
		$minutes = $minutes % 60;
		$days = floor( $hours / 24);
		$hours = $hours % 24;
		$weeks = floor( $days / 7);
		$days = $days % 7;
		$result = '';
		if($weeks == 1){
			$result = translate('LBL_TIME_LAST_WEEK','SugarFeed').' ';
			return $result;
		}else if($weeks > 1){
			$result .= $weeks . ' '.translate('LBL_TIME_WEEKS','SugarFeed').' ';
			if($days > 0) {
                $result .= $days . ' '.translate('LBL_TIME_DAYS','SugarFeed').' ';
            }
		}else{
			if($days == 1){
				$result = translate('LBL_TIME_YESTERDAY','SugarFeed').' ';
				return $result;
			}else if($days > 1){
				$result .= $days . ' '. translate('LBL_TIME_DAYS','SugarFeed').' ';
			}else{
				if($hours == 1) {
                    $result .= $hours . ' '.translate('LBL_TIME_HOUR','SugarFeed').' ';
                } else {
                    $result .= $hours . ' '.translate('LBL_TIME_HOURS','SugarFeed').' ';
                }
				if($hours < 6){
					if($minutes == 1) {
                        $result .= $minutes . ' ' . translate('LBL_TIME_MINUTE','SugarFeed'). ' ';
                    } else {
                        $result .= $minutes . ' ' . translate('LBL_TIME_MINUTES','SugarFeed'). ' ';
                    }
				}
				if($hours == 0 && $minutes == 0) {
                    if($seconds == 1 ) {
                        $result = $seconds . ' ' . translate('LBL_TIME_SECOND','SugarFeed');
                    } else {
                        $result = $seconds . ' ' . translate('LBL_TIME_SECONDS','SugarFeed');
                    }
                }
			}
		}
		return $result . ' ' . translate('LBL_TIME_AGO','SugarFeed');
    }

}
