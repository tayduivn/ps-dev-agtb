<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
 
 

require_once('modules/Schedulers/language/en_us.lang.php');

/**
 * class SchdulerMonitor
 * TODO: writeup of functionality of this object
 */
class SchedulerMonitor extends Scheduler {
	var $socket;
	var $msg;
	var $uptime;
	var $uptimeScheduler;
	var $stop			= false;
	var $controller;	// SCNB daemon
	var $checkInTime;	// last time daemon talked to us.
	var $shutdown		= false;
	var $checkCount		= 0;
	var $maxRetry		= 3;
	
	/** 
	 * Sole constructor
	 */
	function SchedulerMonitor() {
		global $mod_strings;
		
//BEGIN SUGARCRM flav=int ONLY
		$this->msg = $mod_strings['SOCK_GREETING'];
		$this->socketAddress = $this->socketAddressMonitor;
		$this->socketPort = $this->socketPortMonitor;
		
		if($this->sendMsg("heartbeat\n", true)) {
			$GLOBAL['log']->fatal('Monitor FAILURE monitor already listening on port '.$this->socketPortMonitor);
			die();	
		} elseif($this->createListener()) {
			$GLOBALS['log']->debug('----->SC Monitor up ready to go!');
			$this->uptime = strtotime('now');
		} else {
			$GLOBALS['log']->fatal('----->Monitor FAILURE could not get listener running - another instance already running?');
		}
	}
	
	/**
	 * This function sends an email to the admin
	 */
	function sendAdminAlert($msg) {
		//TODO get this function working.
		return true;	
	}
	
	/**
	 * This function kills the Scheduler
	 * @return	boolean	Success
	 */
	function killScheduler() {
		if($this->sendMsg("die\n")) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * This function starts the Scheduler
	 */
	function startScheduler() {
		if(empty($sugar_config)) {
			
		}
		global $sugar_config;
		$GLOBALS['log']->debug('----->Monitor starting Scheduler thread');
		$this->uptimeScheduler = mktime();
		$job = new Job();
		$job->object_assigned_name = 'SchedulerDaemon';
		
		if($job->fire($sugar_config['site_url'].'/index.php?entryPoint=schedulers&type=sncb', 1)) {
			return true;
		} else {
			return false;
		}
	}

	/** 
	 * This function sends a message to the Daemon
	 * @param	$msg	Message to send to Daemon
	 * @param	$self	Boolean to flag whether detecting double startup
	 * @return	boolean	Success
	 */
	function sendMsg($msg, $self=false) {
		$GLOBALS['log']->debug('----->Monitor sending ['.trim($msg).'] to Daemon');
		$s = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
		
		if($self) {
			$address = $this->socketAddressMonitor;
			$port = $this->socketPortMonitor;
		} else {
			$address = $this->socketAddressDaemon;
			$port = $this->socketPortDaemon;
		}
		
		// ask if daemon is still alive
		if(@socket_connect($s, $address, $port)) { // silencing this because it may fail on 'ping'
			if(socket_write($s, $msg, strlen($msg))) {
				$GLOBALS['log']->debug('----->Monitor sent message');
				usleep(100);
				$GLOBALS['log']->debug('----->Monitor closing outbound port');
				socket_shutdown($s, 2);
				socket_close($s);
				return true;
			} else {
				$GLOBALS['log']->debug('----->Monitor FAILURE could not socket_send() message to Daemon');
				usleep(100);
				$GLOBALS['log']->debug('----->Monitor closing outbound port');
				socket_shutdown($s, 2);
				socket_close($s);
				return false;
			}
		} else {
			$GLOBALS['log']->debug('----->Monitor FAILURE could not socket_connect() to Daemon');
			usleep(100);
			$GLOBALS['log']->debug('----->Monitor closing outbound port');
			socket_shutdown($s, 2);
			socket_close($s);
			return false; 
		}
	}
	
	/** 
	 * This function listens on the socket for this object and deals with admin input
	 */
	function listen() {
		$GLOBALS['log']->debug('----->Monitor Monitor running listen();');
		$buf = '';
		$ack = "ack\n";
		
		$pingAt = mktime() + 10;
		
		while($this->stop == false) {
			
			if(!$this->shutdown) { // if we manually shutdown the service, don't try to ping
				if($pingAt <= mktime()) { 
					$pingAt = mktime() + 10;// every 10 secs
					$this->checkCount++;
					$GLOBALS['log']->debug('----->Monitor pinging SD :: next ping at: '.$pingAt);

					if(!$this->sendMsg("ping\n")) {
						$GLOBALS['log']->fatal('Monitor FAILURE could not ping SD.  Attempting to restart SD.');
						if($this->startScheduler()) {
							$GLOBALS['log']->fatal('----->Monitor re/started SD.');
							$this->checkCount = 0;
						} else {
							$GLOBALS['log']->fatal('Monitor FAILURE could not restart SD.');
							if($this->checkCount >= $this->maxRetry) {
								$GLOBALS['log']->fatal('Monitor FAILURE could not PING Daemon '.$this->maxRetry.' times.  Ceasing all attempts.');
								$this->shutdown = true;
							}
						}
					} else {
						$GLOBALS['log']->debug('Monitor PINGed Daemon successfully.  Resetting checkCount.');
						$this->checkCount = 0;
					}
				}
			} else {
				$GLOBALS['log']->debug('Monitor FAILURE lost contact with Daemon! Sending admin alert email.');
				$this->sendAdminAlert('Monitor lost contact with the Daemon at GMT: '.gmdate('Y-m-d H:i:s', strtotime('now')));
			}

			if(($socketInbound = @socket_accept($this->socket)) < 0) { // we hold here until input arrives
				$GLOBALS['log']->fatal('----->Monitor FAILURE could not accept connection on socket.');
				break;
			}
			
			if(is_resource($socketInbound)) {
			
				socket_write($socketInbound, $this->msg, strlen($this->msg)); // send greeting
				
				while($this->stop == false) {
					if(!is_resource($socketInbound)) {
						break;
					}

					if(($buf = socket_read($socketInbound, 2048, PHP_NORMAL_READ)) !== false) { 
					
						if(	(socket_last_error($socketInbound) == 104) ||
							(socket_last_error($socketInbound) == 154)
						) {  // "unable to read from socket"
							$GLOBALS['log']->fatal('----->Monitor lost socket connection!  Sending SOS and killing self!');
							// send help me msg
							$this->sendMsg("help\n");
							usleep(200);
							@socket_shutdown($socketInbound, 2);
							@socket_shutdown($this->socket, 2);
							@socket_close($socketInbound);  // close both, 1 or both may already be dead, so do it silently
							@socket_close($this->socket);
							//$this = null; // kill myself
						}
						
						if(!$buf = trim($buf)) { // check for null, socket_read() on nothing is '0'
							continue;
						}
						
						if($buf == 'heartbeat') {
							$GLOBALS['log']->debug('----->Monitor got HEARTBEAT.  Updating check-in time');
							$this->checkInTime = strtotime('now');
							break;
						}

						if($buf == 'help') {
							$GLOBALS['log']->debug('----->Monitor got HELP request message!  Attempting to restart Daemon');
							if($this->startScheduler()) {
								$msg .= "\n\n[ Scheduler daemon has been STARTED ]\n";
							} else {
								$msg .= "\n\n[ Scheduler daemon could not be STARTED - please check the logs for the error. ]\n";
							}
						}

						if($buf == 'quit') {
							$GLOBALS['log']->debug('----->Monitor got QUIT');
							$msg = "\nSchedulerMonitor received QUIT command.\nGoodbye.\n";
							socket_write($socketInbound, $msg, strlen($msg));  // output feedback to admin
							break;
						}
						
						if($buf == 'start') {
							$GLOBALS['log']->debug('----->Monitor got START');
							$msg = "\nSchedulerMonitor received START command.\nStarting SchedulersDaemon daemon.\n";
							usleep(100);
							if($this->startScheduler()) {
								$msg .= "\n\n[ Scheduler daemon has been STARTED ]\n";
							} else {
								$msg .= "\n\n[ Scheduler daemon could not be STARTED - please check the logs for the error. ]\n";
							}
							
							socket_write($socketInbound, $msg, strlen($msg));  // output feedback to admin
							continue;
						}
		
						if($buf == 'restart') {
							$GLOBALS['log']->debug('----->Monitor got RESTART');
							$msg = "\nSchedulerMonitor received RESTART command.\nRestarting SchedulersDaemon daemon.\n";
							if($this->killScheduler()) {
								if($this->startScheduler()) {
									$msg .= "\n\n[ Scheduler daemon has been RESTARTED ]\n";
								}
							} else {
								$msg .= "\n\n[ Scheduler daemon could not be RESTARTED - please check the logs for the error. ]\n";
							}
							socket_write($socketInbound, $msg, strlen($msg));  // output feedback to admin
							continue;
						}
						
						if($buf == 'shutdown') {
							$GLOBALS['log']->debug('----->Monitor got SHUTDOWN');
							$this->shutdown = true;
							$msg = "\nSchedulerMonitor received SHUTDOWN command.\nSchedulersDaemon daemon will be shutdown.\n";
							if($this->killScheduler()) {
								$msg .= "\n\n[ Scheduler daemon has been SHUTDOWN ]\n";	
							} else {
								$msg .=	"\n\n[ Scheduler daemon could not be SHUTDOWN - please check the logs for the error. ]\n";
							}
							usleep(100);
							socket_write($socketInbound, $msg, strlen($msg));  // output feedback to admin
							usleep(100);
							continue;
						}
						if($buf == 'status') {
							$GLOBALS['log']->debug('----->Monitor got STATUS');
							$msg = "\nSchedulerMonitor received STATUS command.\nDaemon status:\n";
							$msg .= "UPTIME: ";
							$msg .= mktime() - $this->uptimeScheduler."secs\n";
							usleep(100);
							socket_write($socketInbound, $msg, strlen($msg));  // output feedback to admin
							usleep(100);
							continue;
						}
						if($buf == 'die') {
							$GLOBALS['log']->fatal('----->Monitor got DIE');
							$msg = "\nSchedulerMonitor received DIE command.\nKilling both Daemon AND Monitor.\n";
							
							$this->sendMsg("die\n");
							$GLOBALS['log']->debug('----->Monitor sent DIE to Daemon.');
							
							socket_write($socketInbound, $msg, strlen($msg));
							
							@socket_shutdown($socketInbound, 2);
							@socket_shutdown($this->socket, 2);
							@socket_close($socketInbound);  // close both, 1 or both may already be dead, so do it silently
							@socket_close($this->socket);
							$GLOBALS['log']->debug('----->Monitor closed both Inbound and Outbound ports');
							
							//$this = null; // kill myself
							$GLOBALS['log']->debug('----->Monitor NULLed self.');
							die();
						}
						
					} elseif (socket_last_error($socketInbound) == 104) {  // "unable to read from socket"
						$GLOBALS['log']->fatal('----->Monitor lost socket connection!  Sending SOS and killing self!');
						// send help me msg
						$this->sendMsg("help\n");
						@socket_shutdown($socketInbound, 2);
						@socket_shutdown($this->socket, 2);
						@socket_close($socketInbound);  // close both, 1 or both may already be dead, so do it silently
						@socket_close($this->socket);
						//$this = null; // kill myself
					} // end if(socket_read())
				}
				$GLOBALS['log']->debug('----->Monitor closing inbound socket');
				@socket_shutdown($socketInbound, 2);
				@socket_close($socketInbound); // this may cascade a close to the sockets.
				usleep(100);
			} // end if(is_resource())
			usleep(100);
		}
		$GLOBALS['log']->debug('----->Monitor FAILURE closing listen socket (due to error)');
		@socket_shutdown($this->socket, 2);
		@socket_close($this->socket);
		
	}

	/**
	 * This function sets up a socket listener on the IP and Port specified in the class definition.
	 * If successful, the class attribute "socketListen" is available as the listen socket for this object.
	 * @return	boolean	success
	 */
	function createListener() {
		$GLOBALS['log']->debug('----->Monitor/SD Setting up socket with address: ('.$this->socketAddress.') and port: ('.$this->socketPort.')');
		
		$opt = array(	'SO_BROADCAST' => SO_BROADCAST,	
						'SO_DONTROUTE' => SO_DONTROUTE,
						'SO_DEBUG' => SO_DEBUG,		
						'SO_ERROR' => SO_ERROR,
						'SO_FREE' => SO_FREE,		
						'SO_KEEPALIVE' => SO_KEEPALIVE,
						'SO_LINGER' => SO_LINGER,		
						'SO_NOSERVER' => SO_NOSERVER,
						'SO_OOBINLINE' => SO_OOBINLINE,	
						'SO_RCVBUF' => SO_RCVBUF,
						'SO_RCVLOWAT' => SO_RCVLOWAT,	
						'SO_RCVTIMEO' => SO_RCVTIMEO,
						'SO_REUSEADDR' => SO_REUSEADDR,	
						'SO_SNDBUF' => SO_SNDBUF,
						'SO_SNDLOWAT' => SO_SNDLOWAT,	
						'SO_SNDTIMEO' => SO_SNDTIMEO,
						'SO_TYPE' => SO_TYPE);
		
		// CREATE
		$s = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));
		if($s === false) {
			$GLOBALS['log']->debug('SM/SD FAILURE socket creation failed: '.socket_strerror(socket_last_error()) );
			$GLOBALS['log']->debug('SM/SD FAILURE not creating listener');
			return false;	
		} elseif(!socket_bind($s, $this->socketAddress, $this->socketPort)) { // BIND
			$GLOBALS['log']->debug('SM/SD FAILURE socket bind failed: '.socket_strerror(socket_last_error()) );
			$GLOBALS['log']->debug('SM/SD FAILURE not creating listener');
			return false;
		} elseif(!socket_listen($s, 5)) {
			$GLOBALS['log']->debug('SM/SD FAILURE socket listen failed: '.socket_strerror(socket_last_error()) );
			$GLOBALS['log']->debug('SM/SD FAILURE not creating listener');
			return false;
		} else {
			socket_set_option($s, SOL_SOCKET, SO_LINGER,1);
			socket_set_option($s, SOL_SOCKET,SO_REUSEADDR,1);
			socket_set_nonblock($s);
			$this->socket = $s;
			$GLOBALS['log']->debug('----->Monitor/SD socket creation successful! ');
			
			$GLOBALS['log']->debug('----->Monitor/Daemon SO_* socket options');
			foreach($opt as $k => $option) {
				$pp_opt = socket_get_option($s, SOL_SOCKET, $option);
				if(is_array($pp_opt)) { 
					foreach($pp_opt as $k2 => $v) {
						$GLOBALS['log']->debug('----->'.$k.'::'.$k2.':::'.$v);
					}
				} else {
					$GLOBALS['log']->debug('----->'.$k.'::'.$pp_opt);
				}
			}
			return true;
		}
	}
	
} // end SchedulerMonitor class def
//END SUGARCRM flav=int ONLY
?>
