<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Set up an array of Jobs with the appropriate metadata
 * 'jobName' => array (
 * 		'X' => 'name',
 * )
 * 'X' should be an increment of 1
 * 'name' should be the EXACT name of your function
 *
 * Your function should not be passed any parameters
 * Always  return a Boolean. If it does not the Job will not terminate itself
 * after completion, and the webserver will be forced to time-out that Job instance.
 * DO NOT USE sugar_cleanup(); in your function flow or includes.  this will
 * break Schedulers.  That function is called at the foot of cron.php
 */

/**
 * This array provides the Schedulers admin interface with values for its "Job"
 * dropdown menu.
 */
$job_strings = array (
	0 => 'refreshJobs',
	1 => 'pollMonitoredInboxes',
	2 => 'runMassEmailCampaign',
    5 => 'pollMonitoredInboxesForBouncedCampaignEmails',
	3 => 'pruneDatabase',
	4 => 'trimTracker',
	/*4 => 'securityAudit()',*/
	//BEGIN SUGARCRM flav=pro ONLY
    6 => 'processWorkflow',
	7 => 'processQueue',
    9 => 'updateTrackerSessions',
	//END SUGARCRM flav=pro ONLY
    12 => 'sendEmailReminders',
    //BEGIN SUGARCRM flav=pro ONLY
    13 => 'performFullFTSIndex',
    //END SUGARCRM flav=pro ONLY
    15 => 'cleanJobQueue',
    //BEGIN SUGARCRM flav=pro ONLY
    //Add class to build additional TimePeriods as necessary
    16 => 'class::SugarJobCreateNextTimePeriod',
    17 => 'class::SugarJobHeartbeat',
    //END SUGARCRM flav=pro ONLY
    20 => 'cleanOldRecordLists',
    //BEGIN SUGARCRM flav=int ONLY
	999 => 'testEmail',
    //END SUGARCRM flav=int ONLY

);

/**
 * Job 0 refreshes all job schedulers at midnight
 * DEPRECATED
 */
function refreshJobs() {
	return true;
}


/**
 * Job 1
 */
function pollMonitoredInboxes() {

    $_bck_up = array('team_id' => $GLOBALS['current_user']->team_id, 'team_set_id' => $GLOBALS['current_user']->team_set_id);
	$GLOBALS['log']->info('----->Scheduler fired job of type pollMonitoredInboxes()');
	global $dictionary;
	global $app_strings;


	require_once('modules/Emails/EmailUI.php');

	$ie = BeanFactory::getBean('InboundEmail');
	$emailUI = new EmailUI();
	$r = $ie->db->query('SELECT id, name FROM inbound_email WHERE is_personal = 0 AND deleted=0 AND status=\'Active\' AND mailbox_type != \'bounce\'');
	$GLOBALS['log']->debug('Just got Result from get all Inbounds of Inbound Emails');

	while($a = $ie->db->fetchByAssoc($r)) {
		$GLOBALS['log']->debug('In while loop of Inbound Emails');
		$ieX = BeanFactory::getBean('InboundEmail', $a['id'], array('disable_row_level_security' => true));
        $GLOBALS['current_user']->team_id = $ieX->team_id;
        $GLOBALS['current_user']->team_set_id = $ieX->team_set_id;
		$mailboxes = $ieX->mailboxarray;
		foreach($mailboxes as $mbox) {
			$ieX->mailbox = $mbox;
			$newMsgs = array();
			$msgNoToUIDL = array();
			$connectToMailServer = false;
			if ($ieX->isPop3Protocol()) {
				$msgNoToUIDL = $ieX->getPop3NewMessagesToDownloadForCron();
				// get all the keys which are msgnos;
				$newMsgs = array_keys($msgNoToUIDL);
			}
			if($ieX->connectMailserver() == 'true') {
				$connectToMailServer = true;
			} // if

			$GLOBALS['log']->debug('Trying to connect to mailserver for [ '.$a['name'].' ]');
			if($connectToMailServer) {
				$GLOBALS['log']->debug('Connected to mailserver');
				if (!$ieX->isPop3Protocol()) {
					$newMsgs = $ieX->getNewMessageIds();
				}
				if(is_array($newMsgs)) {
					$current = 1;
					$total = count($newMsgs);
					require_once("include/SugarFolders/SugarFolders.php");
					$sugarFolder = new SugarFolder();
					$groupFolderId = $ieX->groupfolder_id;
					$isGroupFolderExists = false;
					$users = array();
					if ($groupFolderId != null && $groupFolderId != "") {
						$sugarFolder->retrieve($groupFolderId);
						$isGroupFolderExists = true;
						//BEGIN SUGARCRM flav=pro ONLY
						$_REQUEST['team_id'] = $sugarFolder->team_id;
						$_REQUEST['team_set_id'] = $sugarFolder->team_set_id;
						//END SUGARCRM flav=pro ONLY
					} // if
					$messagesToDelete = array();
					if ($ieX->isMailBoxTypeCreateCase()) {
						$users[] = $sugarFolder->assign_to_id;
						//BEGIN SUGARCRM flav=pro ONLY
						require_once("modules/Teams/TeamSet.php");
						require_once("modules/Teams/Team.php");
						$GLOBALS['log']->debug('Getting users for teamset');
						$teamSet = BeanFactory::getBean('TeamSets');
						$usersList = $teamSet->getTeamSetUsers($sugarFolder->team_set_id, true);
						$GLOBALS['log']->debug('Done Getting users for teamset');
						$users = array();
						foreach($usersList as $userObject) {
							if ($userObject->is_group) {
								continue;
							} // if
							$users[] = $userObject->id;
						} // foreach

						//END SUGARCRM flav=pro ONLY
						$distributionMethod = $ieX->get_stored_options("distrib_method", "");
						if ($distributionMethod != 'roundRobin') {
							$counts = $emailUI->getAssignedEmailsCountForUsers($users);
						} else {
							$lastRobin = $emailUI->getLastRobin($ieX);
						}
						$GLOBALS['log']->debug('distribution method id [ '.$distributionMethod.' ]');
					}
					foreach($newMsgs as $k => $msgNo) {
						$uid = $msgNo;
						if ($ieX->isPop3Protocol()) {
							$uid = $msgNoToUIDL[$msgNo];
						} else {
							$uid = imap_uid($ieX->conn, $msgNo);
						} // else
						if ($isGroupFolderExists) {
							//BEGIN SUGARCRM flav=pro ONLY
							$_REQUEST['team_id'] = $sugarFolder->team_id;
							$_REQUEST['team_set_id'] = $sugarFolder->team_set_id;
							//END SUGARCRM flav=pro ONLY
							if ($ieX->importOneEmail($msgNo, $uid)) {
								// add to folder
								$sugarFolder->addBean($ieX->email);
								if ($ieX->isPop3Protocol()) {
									$messagesToDelete[] = $msgNo;
								} else {
									$messagesToDelete[] = $uid;
								}
								if ($ieX->isMailBoxTypeCreateCase()) {
									$userId = "";
									if ($distributionMethod == 'roundRobin') {
										if (sizeof($users) == 1) {
											$userId = $users[0];
											$lastRobin = $users[0];
										} else {
											$userIdsKeys = array_flip($users); // now keys are values
											$thisRobinKey = $userIdsKeys[$lastRobin] + 1;
											if(!empty($users[$thisRobinKey])) {
												$userId = $users[$thisRobinKey];
												$lastRobin = $users[$thisRobinKey];
											} else {
												$userId = $users[0];
												$lastRobin = $users[0];
											}
										} // else
									} else {
										if (sizeof($users) == 1) {
											foreach($users as $k => $value) {
												$userId = $value;
											} // foreach
										} else {
											asort($counts); // lowest to highest
											$countsKeys = array_flip($counts); // keys now the 'count of items'
											$leastBusy = array_shift($countsKeys); // user id of lowest item count
											$userId = $leastBusy;
											$counts[$leastBusy] = $counts[$leastBusy] + 1;
										}
									} // else
									$GLOBALS['log']->debug('userId [ '.$userId.' ]');
									$ieX->handleCreateCase($ieX->email, $userId);
								} // if
							} // if
						} else {
								if($ieX->isAutoImport()) {
									$ieX->importOneEmail($msgNo, $uid);
								} else {
									/*If the group folder doesn't exist then download only those messages
									 which has caseid in message*/
									$ieX->getMessagesInEmailCache($msgNo, $uid);
									$email = BeanFactory::getBean('Emails');
									$header = imap_headerinfo($ieX->conn, $msgNo);
									$email->name = $ieX->handleMimeHeaderDecode($header->subject);
									$email->from_addr = $ieX->convertImapToSugarEmailAddress($header->from);
									$email->reply_to_email  = $ieX->convertImapToSugarEmailAddress($header->reply_to);
									if(!empty($email->reply_to_email)) {
										$contactAddr = $email->reply_to_email;
									} else {
										$contactAddr = $email->from_addr;
									}
									$mailBoxType = $ieX->mailbox_type;
									//BEGIN SUGARCRM flav=pro ONLY
									if (($mailBoxType == 'support') || ($mailBoxType == 'pick')) {
										if(!class_exists('aCase')) {

										}
										$c = BeanFactory::getBean('Cases');
										$GLOBALS['log']->debug('looking for a case for '.$email->name);
										if ($ieX->getCaseIdFromCaseNumber($email->name, $c)) {
											$ieX->importOneEmail($msgNo, $uid);
										} else {
											$ieX->handleAutoresponse($email, $contactAddr);
										} // else
									} else {
									//END SUGARCRM flav=pro ONLY
										$ieX->handleAutoresponse($email, $contactAddr);
									//BEGIN SUGARCRM flav=pro ONLY
									} // else
									//END SUGARCRM flav=pro ONLY
								} // else
						} // else
						$GLOBALS['log']->debug('***** On message [ '.$current.' of '.$total.' ] *****');
						$current++;
					} // foreach
					// update Inbound Account with last robin
					if ($ieX->isMailBoxTypeCreateCase() && $distributionMethod == 'roundRobin') {
						$emailUI->setLastRobin($ieX, $lastRobin);
					} // if

				} // if
				if ($isGroupFolderExists)	 {
					$leaveMessagesOnMailServer = $ieX->get_stored_options("leaveMessagesOnMailServer", 0);
					if (!$leaveMessagesOnMailServer) {
						if ($ieX->isPop3Protocol()) {
							$ieX->deleteMessageOnMailServerForPop3(implode(",", $messagesToDelete));
						} else {
							$ieX->deleteMessageOnMailServer(implode($app_strings['LBL_EMAIL_DELIMITER'], $messagesToDelete));
						}
					}
				}
			} else {
				$GLOBALS['log']->fatal("SCHEDULERS: could not get an IMAP connection resource for ID [ {$a['id']} ]. Skipping mailbox [ {$a['name']} ].");
				// cn: bug 9171 - continue while
			} // else
		} // foreach
		imap_expunge($ieX->conn);
		imap_close($ieX->conn, CL_EXPUNGE);
	} // while
    $GLOBALS['current_user']->team_id = $_bck_up['team_id'];
    $GLOBALS['current_user']->team_set_id = $_bck_up['team_set_id'];
	return true;
}

/**
 * Job 2
 */
function runMassEmailCampaign() {
	if (!class_exists('LoggerManager')){

	}
	$GLOBALS['log'] = LoggerManager::getLogger('emailmandelivery');
	$GLOBALS['log']->debug('Called:runMassEmailCampaign');

	if (!class_exists('DBManagerFactory')){
		require('include/database/DBManagerFactory.php');
	}

	global $beanList;
	global $beanFiles;
	require("config.php");
	require('include/modules.php');
	if(!class_exists('AclController')) {
		require('modules/ACL/ACLController.php');
	}

	require('modules/EmailMan/EmailManDelivery.php');
	return true;
}

/**
 *  Job 3
 */
function pruneDatabase() {
	$GLOBALS['log']->info('----->Scheduler fired job of type pruneDatabase()');
	$backupDir	= sugar_cached('backups');
	$backupFile	= 'backup-pruneDatabase-GMT0_'.gmdate('Y_m_d-H_i_s', strtotime('now')).'.php';

	$db = DBManagerFactory::getInstance();
	$tables = $db->getTablesArray();
	$queryString = array();

	if(!empty($tables)) {
		foreach($tables as $kTable => $table) {
			// find tables with deleted=1
			$columns = $db->get_columns($table);
			// no deleted - won't delete
			if(empty($columns['deleted'])) continue;

			$custom_columns = array();
			if(array_search($table.'_cstm', $tables)) {
			    $custom_columns = $db->get_columns($table.'_cstm');
			    if(empty($custom_columns['id_c'])) {
			        $custom_columns = array();
			    }
			}

			$qDel = "SELECT * FROM $table WHERE deleted = 1";
			$rDel = $db->query($qDel);

			// make a backup INSERT query if we are deleting.
			while($aDel = $db->fetchByAssoc($rDel, false)) {
				// build column names

				$queryString[] = $db->insertParams($table, $columns, $aDel, null, false);

				if(!empty($custom_columns) && !empty($aDel['id'])) {
                    $qDelCstm = 'SELECT * FROM '.$table.'_cstm WHERE id_c = '.$db->quoted($aDel['id']);
                    $rDelCstm = $db->query($qDelCstm);

                    // make a backup INSERT query if we are deleting.
                    while($aDelCstm = $db->fetchByAssoc($rDelCstm)) {
                        $queryString[] = $db->insertParams($table, $custom_columns, $aDelCstm, null, false);
                    } // end aDel while()

                    $db->query('DELETE FROM '.$table.'_cstm WHERE id_c = '.$db->quoted($aDel['id']));
                }
			} // end aDel while()
			// now do the actual delete
			$db->query('DELETE FROM '.$table.' WHERE deleted = 1');
		} // foreach() tables

		if(!file_exists($backupDir) || !file_exists($backupDir.'/'.$backupFile)) {
			// create directory if not existent
			mkdir_recursive($backupDir, false);
		}
		// write cache file

		write_array_to_file('pruneDatabase', $queryString, $backupDir.'/'.$backupFile);
		return true;
	}
	return false;
}


///**
// * Job 4
// */

//function securityAudit() {
//	// do something
//	return true;
//}

function trimTracker()
{
    global $sugar_config, $timedate;
	$GLOBALS['log']->info('----->Scheduler fired job of type trimTracker()');
	$db = DBManagerFactory::getInstance();

	$admin = Administration::getSettings('tracker');
	require('modules/Trackers/config.php');
	$trackerConfig = $tracker_config;

    require_once('include/utils/db_utils.php');
    $prune_interval = !empty($admin->settings['tracker_prune_interval']) ? $admin->settings['tracker_prune_interval'] : 30;
	foreach($trackerConfig as $tableName=>$tableConfig) {

		//Skip if table does not exist
		if(!$db->tableExists($tableName)) {
		   continue;
		}

	    $timeStamp = db_convert("'". $timedate->asDb($timedate->getNow()->get("-".$prune_interval." days")) ."'" ,"datetime");
		if($tableName == 'tracker_sessions') {
		   $query = "DELETE FROM $tableName WHERE date_end < $timeStamp";
		} else {
		   $query = "DELETE FROM $tableName WHERE date_modified < $timeStamp";
		}

	    $GLOBALS['log']->info("----->Scheduler is about to trim the $tableName table by running the query $query");
		$db->query($query);
	} //foreach
    return true;
}

/* Job 5
 *
 */
function pollMonitoredInboxesForBouncedCampaignEmails() {
	$GLOBALS['log']->info('----->Scheduler job of type pollMonitoredInboxesForBouncedCampaignEmails()');
	global $dictionary;


	$ie = BeanFactory::getBean('InboundEmail');
	$r = $ie->db->query('SELECT id FROM inbound_email WHERE deleted=0 AND status=\'Active\' AND mailbox_type=\'bounce\'');

	while($a = $ie->db->fetchByAssoc($r)) {
		$ieX = BeanFactory::getBean('InboundEmail', $a['id'], array('disable_row_level_security' => true));
		$ieX->connectMailserver();
        $GLOBALS['log']->info("Bounced campaign scheduler connected to mail server id: {$a['id']} ");
		$newMsgs = array();
		if ($ieX->isPop3Protocol()) {
			$newMsgs = $ieX->getPop3NewMessagesToDownload();
		} else {
			$newMsgs = $ieX->getNewMessageIds();
		}

		//$newMsgs = $ieX->getNewMessageIds();
		if(is_array($newMsgs)) {
			foreach($newMsgs as $k => $msgNo) {
				$uid = $msgNo;
				if ($ieX->isPop3Protocol()) {
					$uid = $ieX->getUIDLForMessage($msgNo);
				} else {
					$uid = imap_uid($ieX->conn, $msgNo);
				} // else
                 $GLOBALS['log']->info("Bounced campaign scheduler will import message no: $msgNo");
				$ieX->importOneEmail($msgNo, $uid, false,false);
			}
		}
		imap_expunge($ieX->conn);
		imap_close($ieX->conn);
	}

	return true;
}

//BEGIN SUGARCRM flav=pro ONLY
/**
 * Job 6
 */
function processWorkflow() {
	include_once('process_workflow.php');
	return true;
}

/**
 * Job 7
 */
function processQueue() {
    include_once('process_queue.php');
    return true;
}
//END SUGARCRM flav=pro ONLY


//BEGIN SUGARCRM flav=pro ONLY
/**
 * Job 9
 */
function updateTrackerSessions() {
    global $sugar_config, $timedate;
	$GLOBALS['log']->info('----->Scheduler fired job of type updateTrackerSessions()');
	$db = DBManagerFactory::getInstance();
    require_once('include/utils/db_utils.php');
	//Update tracker_sessions to set active flag to false
	$sessionTimeout = db_convert("'".$timedate->getNow()->get("-6 hours")->asDb()."'" ,"datetime");
	$query = "UPDATE tracker_sessions set active = 0 where date_end < $sessionTimeout";
	$GLOBALS['log']->info("----->Scheduler is about to update tracker_sessions table by running the query $query");
	$db->query($query);
	return true;
}
//END SUGARCRM flav=pro ONLY

/**
 * Job 12
 */
function sendEmailReminders(){
	$GLOBALS['log']->info('----->Scheduler fired job of type sendEmailReminders()');
	require_once("modules/Activities/EmailReminder.php");
	$reminder = new EmailReminder();
	return $reminder->process();
}

//BEGIN SUGARCRM flav=pro ONLY
function performFullFTSIndex()
{
    require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');
    $indexer = new SugarSearchEngineFullIndexer();
    $indexer->initiateFTSIndexer();
    $GLOBALS['log']->info("FTS Indexer initiated.");
    return true;
}
//END SUGARCRM flav=pro ONLY

/**
 * Job 20
 */
function cleanOldRecordLists() {
    global $timedate;

	$GLOBALS['log']->info('----->Scheduler fired job of type cleanOldRecordLists()');
    $delTime = time()-3600; // Nuke anything an hour old. 

    $hourAgo = $timedate->asDb($timedate->getNow()->modify("-1 hour"));
    
    $db = DBManagerFactory::getInstance();
    
    $query = "DELETE FROM record_list WHERE date_modified < '".$db->quote($hourAgo)."'";
    $db->query($query,true);

	return true;
}


//BEGIN SUGARCRM flav=int ONLY
/**
 * Job 999
 */
function testEmail() {
	// dev only, sends incrementally named emails to agent1/2@sugarcrm.com

	$e = BeanFactory::getBean('Emails');
	$r = $e->db->query('SELECT count(*) AS c FROM emails WHERE deleted=0 AND type="inbound"');
	$a = $e->db->fetchByAssoc($r);
	$e->name = 'Email '.($a['c'] + 1);
	$e->from_addr = 'autotest@example.com';
	$e->from_name = 'Auto Test';
	$e->cc_addrs_arr = array();
	$e->bcc_addrs_arr = array();
	$e->to_addrs_arr = array(0 => array('display'=>'Agent 1', 'email'=>'agent1@sugarcrm.com'),
							 1 => array('display'=>'Agent 2', 'email'=>'agent2@sugarcrm.com'),
							);
	$e->description = '
Although 2D style was starting to look like a lost art on the PSP system, Taito\'s Exit, which comes out December 15th in Japan, will prove otherwise. Today some more details have rolled in about the life of Mr. Escape and what kind of perils he faces.

First up is the mini-map. A potential problem with running around the building rescuing people is that you might lose your bearings, which wouldn\'t be good since you are always fighting against the clock. But now a mini-map has been revealed that instantly takes you to an simple map done up in a retro Atari looking style that shows the entire level layout, the location of trapped victims, and where Mr. Escape is currently located.

Next up is a rundown of usable items you can find in levels. Fire extinguishers can be used to clear paths, ropes and ladders to move between floors after the building ladders have burned down, keys to open locked doors, and flashlights should be useful for finding your way through dark areas. Of course there will be more items than just these, but these starting few should give an idea of what will be available.

When moving around the buildings you need to watch out for all types of hazards that may get in your way. Automatic shutters can lock down in the event of fires, floors may crumble beneath--especially if the person you\'re rescuing is heavy--and even the floors themselves can become electrified. To get around these situations you\'ll need to use not only your items, but look around for levers that activate objects in the stage such as sprinklers, ladders, or doors.

Now when it comes to those in peril, after finding them they\'ll be able to help Mr. Escape out in a variety of ways. By splitting up the characters you can have them activate levers or get them to reach areas that Mr. Escape could normally not reach. For instance kids can crawl through small spaces to find items, and then they can pass them back to Mr. Escape. Mr. Escape has to be careful though because if an innocent person gets injured, a character named Jet will fly in on his jetpack and steal them from Mr. Escape.

All in all, Exit looks like an exciting action puzzle game that should showoff the wonderful 2D capabilities of the PSP system. More likely than not, a US announcement should happen soon after the Japan release next month. But even if it doesn\'t, since the PSP is region-free system, importing is always a possibility.';
	$e->send();
	return true;
}
//END SUGARCRM flav=int ONLY

function cleanJobQueue($job)
{
    $td = TimeDate::getInstance();
    // soft delete all jobs that are older than cutoff
    $soft_cutoff = 7;
    if(isset($GLOBALS['sugar_config']['jobs']['soft_lifetime'])) {
        $soft_cutoff = $GLOBALS['sugar_config']['jobs']['soft_lifetime'];
    }
    $soft_cutoff_date = $job->db->quoted($td->getNow()->modify("- $soft_cutoff days")->asDb());
    $job->db->query("UPDATE {$job->table_name} SET deleted=1 WHERE status='done' AND date_modified < ".$job->db->convert($soft_cutoff_date, 'datetime'));
    // hard delete all jobs that are older than hard cutoff
    $hard_cutoff = 21;
    if(isset($GLOBALS['sugar_config']['jobs']['hard_lifetime'])) {
        $hard_cutoff = $GLOBALS['sugar_config']['jobs']['hard_lifetime'];
    }
    $hard_cutoff_date = $job->db->quoted($td->getNow()->modify("- $hard_cutoff days")->asDb());
    $job->db->query("DELETE FROM {$job->table_name} WHERE status='done' AND date_modified < ".$job->db->convert($hard_cutoff_date, 'datetime'));
    return true;
}

if (SugarAutoLoader::existing('custom/modules/Schedulers/_AddJobsHere.php')) {
	require('custom/modules/Schedulers/_AddJobsHere.php');
}

$extfile = SugarAutoLoader::loadExtension('schedulers');
if($extfile) {
    require $extfile;
}

$extfile = SugarAutoLoader::loadExtension('app_schedulers');
if($extfile) {
    require $extfile;
}
