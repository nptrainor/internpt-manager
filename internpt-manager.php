#!/usr/bin/env php
<?php
/**
* 2025 06 26
* v0.9.51
* Requirements:
* 
* MIT License

Copyright (c) 2025 N P Trainor

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 * ANSI
 * Escape character
 */
namespace internpt_manager;
define ('ESC', "\033");

/**
 * ANSI colours
 */
define('ANSI_BLACK', ESC."[30m");
define('ANSI_RED', ESC."[31m");
define('ANSI_GREEN', ESC."[32m");
define('ANSI_YELLOW', ESC."[33m");
define('ANSI_BLUE', ESC."[34m");
define('ANSI_MAGENTA', ESC."[35m");
define('ANSI_CYAN', ESC."[36m");
define('ANSI_WHITE', ESC."[37m");

/**
 * ANSI background colours
 */
define('ANSI_BACKGROUND_BLACK', ESC."[40m");
define('ANSI_BACKGROUND_RED', ESC."[41m");
define('ANSI_BACKGROUND_GREEN', ESC."[42m");
define('ANSI_BACKGROUND_YELLOW', ESC."[43m");
define('ANSI_BACKGROUND_BLUE', ESC."[44m");
define('ANSI_BACKGROUND_MAGENTA', ESC."[45m");
define('ANSI_BACKGROUND_CYAN', ESC."[46m");
define('ANSI_BACKGROUND_WHITE', ESC."[47m");

/**
 * ANSI styles
 */
define('ANSI_BOLD', ESC."[1m");
define('ANSI_ITALIC', ESC."[3m"); 
define('ANSI_UNDERLINE', ESC."[4m");
define('ANSI_STRIKETHROUGH', ESC."[9m");

/**
 * Clear all ANSI styling
 */
define('ANSI_CLOSE', ESC."[0m");

define('STAR', "*");

/*
 * Redirect stdout to stderr
 */
define('R',' 2>&1');

function info($text) { echo ESC.ANSI_WHITE.' '.$text.ANSI_CLOSE.PHP_EOL; }

function error($text) { echo ESC.ANSI_BOLD.ANSI_RED.' '.$text.ANSI_CLOSE.PHP_EOL; } 

function success($text) { echo ESC.ANSI_BOLD.ANSI_YELLOW.' '.$text.ANSI_CLOSE.PHP_EOL; }

function finaltext($text) { 
	$stars = '';
	for($x = 0; $x < strlen(trim($text))+4; $x++){
		$stars .= STAR;
	}
	echo ESC.ANSI_BOLD.ANSI_GREEN.$stars.PHP_EOL.'* '.$text.' *'.PHP_EOL.$stars.ANSI_CLOSE.PHP_EOL; }










function preFlight($command)
{

	/** check whether the escaped command is available to execute, else print help and die  **/	
$cmd_array = array("install","start","stop","uninstall","status","remote_status","clear_cache","force_stop","credentials","getTerminalWidth","webdav_status","check","help","restart");	

if( (!in_array($command,$cmd_array,'strict'))) {

	InternxtConnection::doHelp();

die();

}




/*
 * Other preflight checks before the Internpt Manager can be executed
 * It is fine that there are so many and that they will run every time because this is not something that will be used everyday
 */


/* check correct permissions and ownership
if(fileowner(__FILE__) != 1000 || filegroup(__FILE__) != 0)
{
        error('Oh dear! This file should be owned by root alone');
info('Please change ownership via the following command as root:'); 
info('chown -f root:root '.__FILE__);
info('After you have done this, run '.__FILE__.' '.$command.' again');
die();

}


if( substr(sprintf("%o",fileperms(__FILE__)),-4) != '0770' )
{
        error('Oh dear! This file should have strict permissions');
info('Please change permissions via the following command as root: $ chmod -f 0700 '.__FILE__);
info('After you have done this, run '.__FILE__.' '.$command.' again');
die();
}

	* */

    $phpversion_array = explode('.', phpversion());
    if ((int)$phpversion_array[0].$phpversion_array[1] < 56) {
	    error('Oh dear! The minimum version of PHP required is 5.6, while you are using '.$phpversion_array[0].' '.$phpversion_array[1]);
info('After you have corrected this, run '.__FILE__.' '.$command.' again');
	    die();
    }

    if(!extension_loaded('posix')) {
	    	error('Oh dear! Posix required.');info('Install "php-process"'); 
		info('After you have done this, run '.__FILE__.' '.$command.' again');
	    	die();
    }

// Check that the Internxt CLI is installed
    $exec = 'internxt --version '.R;
    exec($exec,$output,$result);
    if($result != 0)
    {
	    error('Oh dear! Please install the Internxt CLI as detailed below. You will need to do this as root.');
		info('After you have done this, run '.__FILE__.' '.$command.' again');
	    info('https://help.internxt.com/en/articles/9720556-how-to-install-the-internxt-cli');
	die();
    }

// Check that Rclone is installed
    $exec = 'rclone --version '.R;
    exec($exec,$output,$result);
    if($result != 0)
    {
	    error('Oh dear! Please install Rclone and create a Remote to the Internxt WebDav Server as detailed below.');
	    info('Note: A standard user should create the rclone remote.');
	    info('So, if Jess is going to place her Internxt files in her home directory (eg.at /home/jess/Internxt), then she should create the rclone remote.');
	    info('See below for more details:');
	    finaltext('https://help.internxt.com/en/articles/9766579-how-to-connect-internxt-drive-to-rclone-through-webdav');
		info('After you have done this, run '.__FILE__.' '.$command.' again');
	die();
    }
/*
    /root/.internpt/start.json - where the initial information is found
 */
$c_json = (posix_getpwuid(posix_getuid())['dir'].'/.internpt/credentials.json');

if(!file_exists($c_json) || !is_readable($c_json)) {
	if($command == 'install')
	{
//	InternxtConnection::doCredentials();
		error('The credentials file needs to be created and filled in BEFORE this software can be installed');
$c= '
{
        "username":"xxxxxxxxxxxx",
        "uid":"1000",
        "gid":"1000",
        "mountpoint":"/home/xxxxxxxxxxxx/Internxt",
        "status_directory":"/home/xxxxxxxxxxxx/.internpt",
        "internxt_email":"xxxxxxxxxxxx@example.com",
        "internxt_password":"my excellent password by xxxxxxxxxxxx"
        "internpt_log":"/var/log/internpt-manager/xxxxxxxxxxxx-combined.log"
        "rclone_remote":"Internxt",
        "rclone_log":"/var/log/rclone/xxxxxxxxxxxx-internpt.log",
        "rclone_config":"/home/xxxxxxxxxxxx/.config/rclone/rclone.conf"
}
';
		$directory = dirname('/root/.internpt');
			if(!is_dir($directory)) exec('mkdir '.$directory);
		if(!is_file($c_json)) {
			$handle = fopen($c_json,'w+');
			fwrite($handle,$c);
		}

		info('To help you, the system has just created an template file at '.$c_json);
		info('Once you have filled in the correct details, execute the following command to install     '.__FILE__.' install ');
		info('Want to know how to fill in the file correctly?'); info('Execute '.__FILE__.' credentials    for instructions ');
		info('After you have updated the details, run '.__FILE__.' '.$command.' again');

	}else{
	error('Oh dear! Unable to read the credentials file at the following location. '.$c_json);
	InternxtConnection::doCredentials();
	}
	die();
}else{
	// the credentials file exists, but is it the template?
			$contents = file_get_contents($c_json);
			if(str_contains($contents,'xxxxxxxxxxxx@example.com'))
			{
		error('The information in the '.$c_json.' file is the same at the system template. ');
		info('Want to know how to fill in the file correctly?'); info('Execute '.__FILE__.' credentials    for instructions ');
		info('After you have updated the details, run '.__FILE__.' '.$command.' again');
		die();
			}
}

	    
return true;
}



if(null !== @$argv[1])
{
	// potentially contains a command
	$command=escapeshellcmd($argv[1]);
}else{
	// print help
	$command='help';
}



// check everything is in place
// if not, then preFlight checks should die gracefully
preFlight($command);

// everything is tickety-boo

// read the credentials file
$internpt = new InternxtConnection(posix_getpwuid(posix_getuid())['dir'].'/.internpt/credentials.json',$command);


// do what has been asked
switch($command)
{

	case "install":
		$internpt->doWriteLog("** Install Called **".PHP_EOL);
		$internpt->doInit();
	break;

	case "start":
		$internpt->doWriteLog("** Start Called **".PHP_EOL);
		    info("Internpt Manager Start Requested");
		$internpt->startWebDav();
		$internpt->startService();
                finaltext('Internpt Service Started ✓');
	break;

	case "stop":
		$internpt->doStop();
	break;

	case "force_stop":
		$internpt->doWriteLog("** Force Stop Called **".PHP_EOL);
		info("Internpt Manager Force Stop Requested");
		$internpt->doStop();
		$internpt->doClearCache();
		$internpt->doResetFolder();
                finaltext('Internpt Force Stop Activated ✓');
	break;

	case "clear_cache":
		$internpt->doWriteLog("** Clear Cache Called **".PHP_EOL);
		$internpt->stopService();
		$internpt->doUnmount();
		$internpt->doClearCache();
//		$internpt->startWebDav();
//		$internpt->startService();
	break;

	case "restart":
		$internpt->doWriteLog("** Restart Called **".PHP_EOL);
		$internpt->doRestart();
	break;



	case "status":
		$internpt->doWriteLog("** Service Status Called **".PHP_EOL);
		$internpt->getStatus();
	break;

	case "remote_status":
		$internpt->doWriteLog("** Remote Status Called **".PHP_EOL);
		$internpt->getRemoteStatus(true);
	break;

	case "credentials":
		$internpt->doWriteLog("** doCredentials Called **".PHP_EOL);
		$internpt->doCredentials();
	break;

	case "getTerminalWidth":
		$internpt->getTerminalWidth();
	break;

	case "webdav_status":
		$internpt->doWriteLog("** WebDav Status Called **".PHP_EOL);
		$internpt->getWebDavStatus(true);
	break;

	case "check":
		$internpt->doWriteLog("** Check Called **".PHP_EOL);
		$internpt->doLogin();
		$internpt->doCheck();
	break;





	case "uninstall":
		$internpt->doUninstall();
	break;

	default:
		$internpt->doHelp();
}
// we done what has been asked
die();










class InternxtConnection{
	private $loghandle;
	private readonly string $action;
	private readonly string $username;
	private readonly string $uid;
	private readonly string $guid;
	private readonly string $internxt_email;
	private readonly string $internxt_password;
	private readonly string $mountpoint;
	private readonly string $status_directory;
	private readonly string $internpt_log;
	private readonly string $rclone_log;
	private readonly string $rclone_config;
	private readonly array $credentials;
	private readonly string $credentials_file;
	private readonly string $service_file;
	private readonly string $service;
	private readonly string $tty;
	private readonly string $unit;

	public function __construct($credentials_file,$action = 'install')
	{
		$this->credentials_file = $credentials_file;
		$this->credentials = json_decode(file_get_contents($this->credentials_file),true);
		$this->username = $this->credentials['username'];
		$this->uid = $this->credentials['uid'];
		$this->guid = $this->credentials['guid'];
		$this->mountpoint = $this->credentials['mountpoint'];
		$this->status_directory = $this->credentials['status_directory'];
		$this->internxt_email = $this->credentials['internxt_email'];
		$this->internxt_password = $this->credentials['internxt_password'];
		$this->rclone_log = $this->credentials['rclone_log'];
		$this->rclone_remote = $this->credentials['rclone_remote'];
		$this->rclone_config = $this->credentials['rclone_config'];
		$this->action = $action;
		$this->service_file = $this->credentials['username'].'-internpt-manager.service';
		$this->unit = '/etc/systemd/system/'.$this->service_file;
		$this->doCreateLogs();

	}

	public function doInit()
	{
		info("Initialising Internpt Service Manager");
		$this->doUnmount();
		$this->doLogin();
		$this->createStatusDirectory();
		$this->startWebDav();
		$this->createServiceFile();
		$this->enableServiceFile();
		$this->doDaemonReload();
		$this->startService();
		$this->createWebDavStatusFile(1); $this->createStatusFile(1);
	}


public function createStatusDirectory() {
	/* create directory where status files are held
	 * created at install / update
	 * @returns void
	 */
	if(!is_dir($this->status_directory))
	{
		exec("/usr/bin/mkdir ".$this->status_directory);
		exec('/usr/bin/chown -Rf '.$this->uid.':'.$this->guid.' '.$this->status_directory);
		exec("/usr/bin/chmod -Rf 700 ".$this->status_directory);
	}

}


public function doRestart() {
/*
 *	do restart upon failure
 *@returns void
 */
		$this->doUnmount();
		$this->stopWebDav();
		$this->stopService();
		$this->startWebDav();
		$this->startService();
}

public function doMountCheck() {
    /* checks whether mountpoint is successfully mounted 
     * @returns bool
        * */
	$result = 1;
			$this->doWriteLog("doCheckMount Called");

			$exec= "/usr/bin/cat /proc/mounts | grep ".$this->mountpoint." | wc -l"; 

			$count = 0;
			while( exec($exec,$mo,$mr) ) {
				if(implode($mo) > 2) break;
			}
			$this->doWriteLog("Number of Drive Files: ".implode($mo));



	
				
			$exec = "ls -1 ".$this->mountpoint." | wc -l; echo ".R;
			while( $dir = scandir($this->mountpoint)) {

				if(count($dir)>2){
					$result = 1;	
					break;
				}
			$this->doWriteLog("$count Checking for Live Mount Point..");
            $count++;
if($count == 20000) break;
			}

			if($result == 1)
			{
			success("Internxt Drive is Mounted ✓");
			}else{
			error("Internxt Drive NOT mounted");
			error("Doing Restart");
			$this->doRestart();
			}
		
            return $result;


}


public function doCheck() {
/*
*	Checks whether when the Systemd Service is online that the webdav service is also running
*	*********************** Needs to be changed so that it checks the status file - it can then tell what to do... if the webdav service is not running, the then whole shebang should be restart (ie. stop and then start)
 */

	$ss = $this->getRemoteStatus(true);
	$ws = $this->getWebDavStatus(true);


		$this->doWriteLog("Checking Internpt Service Manager Online: ".$ss);
		$this->doWriteLog("Checking WebDav Service Status Online: ".$ws);
		if(false === boolval($this->doMountCheck())) { 
		$this->doWriteLog("Checking WebDav Mount Status - Not Mounted");
		$this->doWriteLog("Executing Restart");
			$this->doRestart();
		return;
	       	}

		info("Checking Internpt Service Manager Online: ".$ss);
	if($ss == 1) {

		if($ws == 0)
		{
		    info("webdav service not running ".$ws);
			 $this->doWriteLog("Webdav Server offline while Internpt Service is running. Restarting all Services");
			$this->doRestart();
		}
        else{
		     info("webdav service is running ".$ws);
			 $this->doWriteLog("Webdav Server online while Internpt Service is running. No further action");
             return;
        }

	}else{
		info("Internpt Service Stopped - no further action");
		$this->doWriteLog("Internpt Service Manager NOT running - No further action");
	}
        return;
}



public function doCreateLogs(){
/*
 *	Create the log files for internpt-manager
*/

		$directory = dirname($this->credentials['internpt_log']);
		if(!is_dir($directory)) exec('mkdir '.$directory);


		if($this->action == 'install')
		{

			if(!is_file($this->credentials['internpt_log']))
			{
			       	exec('/usr/bin/touch '.$this->credentials['internpt_log'],$output,$result);
				success('Created Internpt Manager Log ✓');
			}


		exec('chown -Rf '.$this->credentials['username'].':root '.$this->credentials['internpt_log']);
		exec('chmod 664 '.$this->credentials['internpt_log']);
		}


		try {
		$this->loghandle = fopen($this->credentials['internpt_log'],'a');
		} catch(Exception $e) {
			error('Unable to open Internpt Manager Log '.$e);
		}
}

public function doDeleteLog(){
/*
 *	Delete the log files for the specific internpt-manager user
*/


		info("Internpt Manager Log Deletion Requested");
		fclose($this->loghandle);
		exec('rm -f '.$this->credentials['internpt_log']);
		finaltext("Internpt Manager log for ".$this->credentials['username']." deleted ✓");
}

public function doStop() {
/*
 *	Stop internpt-manager processes
*/
		$this->doWriteLog("Internpt Manager Stop Requested");
		info("Internpt Manager Stop Requested");
		$this->stopService();
		$this->doUnmount();
		$this->stopWebDav();
                finaltext('Internpt Service stopped ✓');
}

public function doUninstall() {
/*
 *	Stop internpt-manager processes, uninstall installed files and logs
*/

		$this->doWriteLog("Internpt Manager Stop Requested");

		info("Uninstalling Internpt Service Manager");
		$this->stopService();
		$this->disableServiceFile();
		$this->doDaemonReload();
		$this->removeServiceFile();	
		$this->doDaemonReload();
		$this->doDeleteLog();
		finaltext("Internpt Service Manager Files removed");
		info("Delete the internpt-manager file (".__FILE__.") to finalise the uninstall process");
}



public function doLogin() {
/*
 *	Login to Internxt CLI
*/


		$exec = "/usr/bin/internxt login -x -e ".$this->internxt_email." -p '".$this->internxt_password."' ".R;
		exec($exec,$output,$result);
		
			if(str_contains(implode($output),'Succesfully logged in')) {
				/*** Successful Login ***/

                        	$this->doWriteLog("Successfully logged into Internxt Account");
				success('Logged into Internxt Account ✓');

			}else{

				/*** Login Failure ***/

                        $this->doWriteLog("Failed to log into Internxt Account");

		$exec = "/usr/bin/internxt login -x -e ".$this->internxt_email." -p '".$this->internxt_password."' ".R;
		exec($exec,$output,$result);
		
				if(str_contains(implode($output),'Succesfully logged in')) {
				/*** Successful Login ***/

                        		$this->doWriteLog("Successfully logged into Internxt Account");
					success('Logged into Internxt Account ✓');

				}else{

					/*** Login Failure ***/

                        		$this->doWriteLog("Failed to log into Internxt Account");
					error('Oh dear! Unable to Log into Internxt Account');
					info('Check you are using the correct email and password to access your Internxt Account');
					die();
				}
			}
 

} // end doLogin


/*******************************
 *   Start WebDav Functions
 *******************************/

public function startWebDav() {
/*** 
 * Enable  WebDav via Internxt CLI 
 * ***/

$exec = "/usr/bin/internxt webdav enable ".R;
exec($exec,$output,$result);
        /*** Attempt Start of WebDav ***/

	$this->doWriteLog("WebDav Check ".implode($output));

                if(str_contains(implode($output),'online'))
                {

                        $this->doWriteLog("WebDav successfully enabled");
                    if($this->action <> 'check')    success("WebDav Service enabled ✓");
			$this->createWebDavStatusFile(1);
		}else{
			/*** Enabling WebDav Failed ***/
                        $this->doWriteLog("WebDav Not enabled");
			$this->createWebDavStatusFile(0);

                        error('Oh dear! Unable to enable WebDav');
                die();

		}

} // end startWebdav



public function stopWebDav() {
/*** 
 * Disable  WebDav via Internxt CLI 
 * ***/


$exec = "/usr/bin/internxt webdav disable ".R;
exec($exec,$output,$result);
        /*** Attempt Stop of WebDav Service ***/
		if(intval($result) == 0)
                {

                        $this->doWriteLog("WebDav successfully disabled");
                        success('Webdav Service stopped ✓');
			$this->createWebDavStatusFile(0);
		}else{
			/*** Stopped WebDav Failed ***/

                        $this->doWriteLog("WebDav NOT disabled - ".implode($output));
                        error('Oh dear! Unable to stop WebDav Service');

		}

} // end stopWebdav


public function getWebDavStatus($integeronly = false) { 
/*** 
 * Get  WebDav Status 
 *
 * if integeronly:
 * 0 - offline
 * 1 - online
 * -1 - unavailable
 *
 * else
 *
 * textual results
 * @return int
 * ***/

	$exec = "/usr/bin/internxt webdav status ".R;

	exec($exec,$output,$result);
	if($result == 0)
	{
		if(str_contains(implode($output),"status: online"))
		{
			$r = 1;
			$this->createWebDavStatusFile(1);
                        $this->doWriteLog("Webdav Status: Online");
		}else{
                        $this->doWriteLog('Webdav Status: Offline');
			$this->createWebDavStatusFile(0);
			$r = 0;
		}
	}else{
		$r = -1;
                        $this->doWriteLog('Webdav Status: Unavailable'.implode($output));
			$this->createWebDavStatusFile(-1);
	}

// return 
	if($integeronly){
	       	echo $r;
       		return $r;
	}
	else
	{

		if($r == -1){
			error('WebDav Service is unavalable');
		}elseif($r == 1){
			finaltext('WebDav Service is Active');
		}elseif($r == 0){
			error('WebDav Service is Offline');
		}
	}

}


public function createWebDavStatusFile($status) {
	/* create / update webdav_status file
	 * webdav_status file is made available for such things as Waybar to read
	 * @parameter status
	 * 1 - active
	 * 2 - stopped 
	 * -1 - unavailable 
	 * @return void
	 * */

	if(!is_dir($this->status_directory)) $this->createStatusDirectory();
	
	$wb = fopen($this->status_directory.'/webdav_status','w+'); // open status file - usable by external scripts
			fwrite($wb,$status);
			fclose($wb);
		exec('/usr/bin/chown '.$this->uid.':'.$this->guid.' '.$this->status_directory);
		exec("/usr/bin/chmod -Rf 700 ".$this->status_directory);

			return;
}
	


/*******************************
 *   End WebDav Functions
 *******************************/

public function doClearCache() {
/** 
 * Cleans the vfs-cache in Rclone
 *
 * Usage: called before doMount
 *
 * This is particularly useful when uploads get stuck in the cache.
 * For example, Internxt doesnt play nice files/folders beginning with '.'
 * These files/folders can remain in the cache and clog everything up
 * Question: should this be optional? Or, part of a credentials.json?
 * 
 **/	 
$folder = rtrim($this->rclone_remote,':');
$exec = '/usr/bin/rm -Rf /tmp/rclone/vfs/vfs/'.$folder;
exec($exec);
$this->doWriteLog($exec);
}


public function doResetFolder() {
/** 
 * Removes and deletes the Internpt folder 
 * Recreates the Internpt folder
 *
 *
 **/	 
    $folder = $this->mountpoint;
$exec = '/usr/bin/chattr -i '.$this->mountpoint;
$exec .= '&& /usr/bin/rm -Rf '.$this->mountpoint;
$exec .= '&& /usr/bin/mkdir '.$this->mountpoint;
$exec .= '&& /usr/bin/chmod -Rf 0770 '.$this->mountpoint;
$exec .= '&& /usr/bin/chown -Rf '.$this->uid.':'.$this->guid.' '.$this->mountpoint;
exec($exec);
$this->doWriteLog('Reset Folder Called: '.$exec);
}



public function createServiceFile() {
/*** 
 * Write Systemd Service files for rclone remote
 * ***/


$service =
"[Unit]
Description=Internxt Drive (rclone)
AssertPathIsDirectory=".$this->mountpoint."
Wants=network-online.target
After=network-online.target

[Service]
Type=notify
ExecStart=/usr/bin/rclone mount --config=".$this->rclone_config." --timeout 5h --allow-other --allow-non-empty --no-check-certificate --buffer-size 1G --cache-tmp-upload-path=/tmp/rclone/upload --cache-chunk-path=/tmp/rclone/chunks --cache-workers=16 --cache-writes --vfs-cache-mode full --vfs-cache-max-size=90G --cache-dir=/tmp/rclone/vfs --vfs-read-chunk-size-limit 500M  --vfs-read-chunk-size 64M --cache-db-path=/tmp/rclone/db --no-modtime --drive-use-trash --stats=0 --log-file ".$this->rclone_log." --log-level INFO --checkers=16 --bwlimit=180M --rc-addr=localhost:5575 --gid ".$this->guid." --uid ".$this->uid." --dir-cache-time=72h --cache-info-age=60m --rc ".$this->rclone_remote.":/ ".$this->mountpoint."
ExecStop=/usr/bin/fusermount -u -z ".$this->mountpoint."
Restart=on-failure
[Install]
WantedBy=default.target";
//
			//
	$unit_file = fopen($this->unit,"w");
	fwrite($unit_file,$service);

	if(!file_exists($this->unit))
	{
                        $this->doWriteLog("Unable to write to ".$unit_file." - ".$implode($output));
			error("Unable to write to Internpt Service Manager File");
			die();
	}

} // end createServiceFile



	public function enableServiceFile()
	{
/*** 
 * Enable Systemd Service files for rclone remote
 * ***/



$exec = "/usr/bin/systemctl enable ".$this->service_file.R; 
exec($exec,$eo,$er);

		if($er == 1)
		{
                        $this->doWriteLog("Unable to enable ".$this->service_file." - ".$implode($output));
			error("Oh dear! Unable to enable Internpt Service Manager file");
			error("Internxt Drive NOT enabled");
			die();
		}else{
			$this->doWriteLog("Enabledl service file - ".$this->service_file);
			success("Enabled Internpt Service Manager File ✓");
		}

} // end enableServiceFile

public function doDaemonReload() {
/*** 
 * Action Systemd Daemon Reload
 * ***/

$exec = "/usr/bin/systemctl daemon-reload".R; 
exec($exec,$output,$return);

		if($return  == 1)
		{
			$this->doWriteLog("Unable to reload daemon - ".implode($output));
			error("Oh dear! Unable to reload Internpt Service Manager file Daemon");
			die();
		}else{
			$this->doWriteLog("Reloaded daemon for Internpt Service Manager File");
			success("Reloaded Internpt Service Manager File ✓");
		}
} // end doDaemonReload



public function disableServiceFile() {
/*** 
 * Disable Systemd Service files for rclone remote
 * ***/


	$exec = "/usr/bin/systemctl disable ".$this->service_file.R;
exec($exec,$eo,$er);


		if($er == 1)
		{
			$this->doWriteLog("Unable to Disable ".$this->service_file);
			if(file_exists($this->unit))
			{
			error("Oh dear! Unable to disable Internpt Service Manager file");
			error("Internxt Drive NOT uninstalled");
			die();
			}
		}else{
			$this->doWriteLog("Successfully Disabled ".$this->service_file);
		}

			success("Disabled Internpt Service Manager File ✓");
		$this->createStatusFile(3);

	} // end disableServiceFile



	
public function getStatus() {
/*** 
 * get Systemd Service status
 *
 * 0 - inactive
 * 1 - active
 * 2 - stopped
 * 3 - unavailable
 * @returns void
 * ***/


	$exec = "/usr/bin/systemctl status ".$this->service_file.R;
	exec($exec,$output,$result);
	if(str_contains(implode($output),'internpt-manager.service not found')){
		finaltext('Internpt Service is NOT active because it is NOT installed');
		info('Execute internpt-manager install to activate');
		$this->createStatusFile(0);
	}elseif(str_contains(implode($output),'Active: failed')){
		finaltext('Internpt Service is Status is Failed');
		info('Try to start the Internpt Service');
		$this->createStatusFile(0);
	}elseif(str_contains(implode($output),'Active: active (running)')){
		finaltext('Internpt Service is Active');
		$this->createStatusFile(1);
	}elseif( str_contains(implode($output),'(code=exited, status=143)')){
		finaltext('Internpt Service is Stopped');
		$this->createStatusFile(2);
	} else{
		finaltext('Internpt Service Status is Unavailable');
		info('Has the Internpt Manager been intialised?');
		$this->createStatusFile(3);
	}

	$this->getWebDavStatus();

	$this->doMountCheck();


} // end of getStatus



	
public function getRemoteStatus($echoresult = false) {
/*** 
 * get Systemd Service status
 * @return int
 *
 * 0 - inactive
 * 1 - active
 * 2 - stopped
 * 3 - unavailable
 *
 * */
$exec = "/usr/bin/systemctl status ".$this->service_file.R;
	exec($exec,$output,$result);
	if(str_contains(implode($output),'internpt-manager.service not found')){
		// inactive
		$r = 0;
		$this->createStatusFile(0);
	}elseif(str_contains(implode($output),'Active: active (running)')){
		// active
		$r = 1;
		$this->createStatusFile(1);
	}elseif( str_contains(implode($output),'(code=exited, status=143)')){
		// stopped
		$r = 2;
		$this->createStatusFile(2);
	} else{
		// unavailable
		$r = 3;
		$this->createStatusFile(3);
	}
	if($echoresult) echo $r;
	return $r;

} // end of getRemoteStatus


public function createStatusFile($status) {
	/* create / update service status file
	 * service_status file is made available for such things as Waybar to read
	 * @parameter status
	 * 1 - active
	 * 2 - stopped 
	 * -1 - unavailable 
	 * @return void
	 * */

	if(!is_dir($this->status_directory)) $this->createStatusDirectory();
	

	$wb = fopen($this->status_directory.'/service_status','w+'); // open status file - usable by external scripts

			fwrite($wb,$status);
			fclose($wb);
		exec('/usr/bin/chown -Rf '.$this->uid.':'.$this->guid.' '.$this->status_directory);
		exec("/usr/bin/chmod -Rf 700 ".$this->status_directory);

			return;
}
	






public function startService() {
/*** 
 * Start Systemd Service 
 * ***/



	$exec = "/usr/bin/systemctl start ".$this->service_file.R;

exec($exec,$output,$result);
		if($result == 1)
		{
			$this->doWriteLog("Unable to start ".$this->service_file);
			error("Oh dear! Unable to start Internpt Service Manager file");
			error("Internxt Drive NOT enabled");
		$this->createStatusFile(0);
			die();
		}else{
			$this->doWriteLog("Internpt Service Manager File");

			info("Attempting to mount Internxt Drive...");

            if(boolval($this->doMountCheck()))
            {
			    $this->doWriteLog("Internxt Drive has Started Successfully");
		    	finaltext("Internxt Drive has started and is ready to be used ✓");
		        $this->createStatusFile(1);
            
            }else{

			    error("Unable to Mount Internxt Drive");
			    $this->doWriteLog("Internxt Drive NOT mounted");
		        $this->createStatusFile(0);
            }

		}

} // end startService


public function stopService() {
/*** 
 * Stop Systemd Service 
 * ***/

	$exec = "/usr/bin/systemctl stop ".$this->service_file.R;

exec($exec,$output,$result);
		if($result == 1)
		{
			$this->doWriteLog("Unable to stop ".$this->service_file);
			error("Oh dear! Unable to stop Internpt Service Manager file");
			error("Internxt Drive NOT enabled");
			die();
		}else{
			$this->doWriteLog("Stopped Internpt Service Manager ");
			$this->createStatusFile(0);


			success("Internpt Service Manager has been sucessfully stopped ✓");
		}
} // end stopService




public function doUnmount() {
/*** 
 * Unmount mount point
 * ***/

	$exec='/usr/bin/umount -l '.$this->mountpoint.R;
		exec($exec,$output,$result);
		sleep(3);
		if($result == 0) {
			$this->doWriteLog("Successfully Unmouted Drive");

		}else{
			$this->doWriteLog("Unable to Unmount Drive - Error Code: ".$result. ' output: '.implode($output));

					if(str_contains(implode($output), 'target is busy')) {

						info("The mount point is busy.");
					        info("Close all files/applications/terminals using the directory '".$this->mountpoint);
					        finaltext("File managers are particularly prone to causing this error as they seek to access the mounted drive. Make sure you close Nautilus/Thunar/PCManFM, etc and then call this command again.");

					} elseif(str_contains(implode($output), 'not mounted')){

						if($this->action != 'install')
						       	info("Point of Information: The mount point is not mounted, and so cannot be unmounted. No further action needed. ✓");
						return;

					} elseif(str_contains(implode($output), 'transport endpoint not connected')){
						       	info("Transport End Point Not connected as of yet");
					}

		}

		//if($this->action == 'uninstall' || $this->action == 'stop') { 
		//	finaltext(ucfirst($this->action)." request NOT completed");
	//		die();
	//	}
		if(
			$this->action <> 'restart' 
			&& $this->action <> 'install' 
		)	error("Oh dear! Unable to unmount Internxt Drive from ".$this->mountpoint);

			

} //end of Unmount


public function removeServiceFile() {
/*** 
 * Remove Systemd Service File
 * ***/


	exec("rm -f ".$this->unit,$output,$result);
	if(intval($result) != 0)
	{
			$this->doWriteLog("Unable to delete Internpt systemd file - Error Code ".$result);
                        error("Oh dear! Unable to remove Internpt systemd file at ".$this->unit.PHP_EOL);
			info("To correct this error, execute the following commands as root".PHP_EOL);
			info("rm -f ".$this->unit.PHP_EOL);
			info("systemctl --daemon-reload ".PHP_EOL);
			info("/usr/local/bin/internpt-manager uninstall".PHP_EOL);
			die();
	}else{
			$msg = " ** ".date('l jS \of F Y H:i:s')." - Internpt systemd service file deleted **  ".$result;
			$this->doWriteLog("Internpt systemd service file deleted");
			success("Internpt Systemd service file deleted ✓");
			/* daemon reload */
			$exec = "/usr/bin/systemctl daemon-reload".R; 
			exec($exec,$output,$return);

	}
} // end removeServiceFile


public function doWriteLog($msg) {
/*** 
 * Write to internpt-manager log file
 * ***/


                        fwrite($this->loghandle,PHP_EOL.date('l jS \of F Y H:i:s')." - ".$msg);

}




public static function doHelp() {
/*** 
 * Print Help
 * ***/


info(InternxtConnection::printstars());
info("This script is an unofficial Internxt Drive Service Manager 

It is to be actioned by root only.

Usage:
internpt-manager.php [install / start / stop / status / remote_status / uninstall / credentials / check / help]

CREDENTIALS: 
The first thing to do is to create the credentials file.
This information is placed here: /root/.internpt/credentials.json:
Execute   ".__FILE__." credentials  to see the necessary format and content of this file

LOGGING:
Information regarding the progress of each requested action ('install','start', etc) will be printed to the terminal and recorded in the log file specified in the above credentials file

INITIALISATION: 
On first use, the script MUST be executed with the command    ".__FILE__." install
All things being well, this will put everything in place and mount and START the required Internxt Drive.

On 'install', the script will:

-- Read key information from the file /root/.internpt/credentials.json:
-- Log into Internxt
-- Start a WebDav server
-- Create a Systemd Unit File, enables this file, and activate it so the Internxt Drive starts on boot
-- It will then start Systemd file to mount the Internxt Drive to the folder specified in the credentials file.
NOTE: 'install' will do all that is necessary to START the Internxt Drive. However, you only need to use the 'install' command once, from then on in, you can use 'start', 'stop', etc.

START:
The 'start' command is ONLY possible after the 'install' command has been executed.
The 'start' command is ONLY possible afer a corresponding 'stop' process.
The 'start' command the preferred way to start the systemd service connection, webDav server, mountpoint, etc. It retains all the information created by the 'install' process.

On 'start', the script will: 

-- Read key information from the file /root/.internpt/credentials.json:
-- Log into Internxt
-- Start a WebDav server
-- Start the systemd process
-- Mount the mountpoint

STOP:
The 'stop' process is the preferred way to safely stop the Internxt connection, webDav server, mountpoint, etc. It retains all the information created by the initialisation process. The service can be started once more by executing the   ".__FILE__." start   command.

On 'stop', the script will: 

-- Read key information from the file /root/.internpt/credentials.json:
-- Log into Internxt
-- Stop the Systemd process
-- Stop the WebDav server
-- Unmount the mountpoint

STATUS:
The 'status' process prints a string indicating the status of the service.

REMOTE STATUS:
The 'remote_status' process prints a single integer indicating the status of the service.

0 - service inactive
1 - service active 
2 - service stopped 
3 - service status unavailable

UNINSTALL: 
The 'uninstall' process is the preferred way to uninstall the facilities provided by this script.
The 'uninstall' process removes all the files created by this script, but leaves this file and the process log file (['internpt_log'] as specified in the credentials) for you to remove.

On 'uninstall', the script will:

-- execute the 'stop' process described above
-- disable and remove all the created Systemd files

HELP: 
The 'help' process prints this information.

Requirements: 

(1) PHP version >= 5.6 must be installed
(2) Rclone must be installed. For best results, choose the most recent version available.
(3) As root, follow the instructions here (https://help.internxt.com/en/articles/9720556-how-to-install-the-internxt-cli) to download the Internxt CLI
(4) As root, create a rclone WebDav remote following the instructions here ()
(5) The directory upon which the Internxt Drive should be mounted should be created. This should be done by the non-root user who needs to access the Internxt Drive. WARNING: Do not use ~/'Internxt Drive' to avoid conflicting with the official applications. 
(6) As root, in the file /etc/fuse.conf, remove the # symbol at the beginning of the line containing 'allow_use_other' 
(7) As root, create the file /root/.internpt/credentials.json. Enter the key information - execute   ".__FILE__." credentials   for more information
");
info(InternxtConnection::printstars());
}// end doHelp


public static function doCredentials(){
/*** 
 * Print Help about the Credentials file
 * ***/


	info(InternxtConnection::printstars());
	info('The credentials file contains all the information that this script needs to initialise and manage your Internxt Drive connection.

It should be created here:

/root/.internpt/credentials.json

The format it should use is as follows (use your own values as appropriate):

{
        "username":"jess",
        "uid":"1000",
        "gid":"1000",
        "mountpoint":"/home/jess/Internxt",
        "status_directory":"/home/jess/.internpt",
        "internxt_email":"jess@example.com",
        "internxt_password":"my excellent password by jess"
        "internpt_log":"/var/log/internpt-manager/jess-combined.log"
        "rclone_remote":"Internxt",
        "rclone_log":"/var/log/rclone/jess-internpt.log",
        "rclone_config":"/home/jess/.config/rclone/rclone.conf"
}

Where:
 username - the username of the owner of the Internxt Drive
 uid - the user ID used for the mount point directory and its associated contents (for example, Jess\' UID in /etc/passwd is 1000)
 gid - the group Id used for the mount point directory and its associated contents (for example, Jess\' GID in /etc/group is 1000)
 mountpoint - the place where the Internxt Drive files are to be mounted. To avoid conflicting with the official Internxt apps do NOT use "~/Internxt Drive".
 status_directory - the directory in which the Internpt Manager Service will create status files (useful for Waybar, etc)
 internxt_email - the email address associated with your Internxt Account
 internxt_password - the password associated with your Internxt Account
 internpt_log - the directory and log file to be used where messages from the '.__FILE__.' script are to be recorded - NOTE: best practice would suggest a per user log file 
 rclone_remote - the name of the rclone remote connecting to the Internxt WebDav Server 
 rclone_log - the directory and log file to be used where messages from rclone for the above remote are to be recorded - NOTE: best practice would suggest a per user log file 
 rclone_config - the rclone directory and config file setting out the details of the remote ');
	info(InternxtConnection::printstars());


} // end doCredentials

public static function getTty() {
	/* discover the tty number for this process 
	 * @return /dev/pts/[int] | null
		* */
$exec = '/usr/bin/tty';
	exec($exec,$output,$result_code);
		if($result_code == 0)
		{
			return implode($output);
		}else{
			return null;
		}
}




public static function printstars() { 
	/** print stars onto terminal - for the width of the terminal
	 * @return a line of *
		* **/
$tty = InternxtConnection::getTty();


if(str_contains(strval($tty),'pts')) {
// find from previous version
		// work out the width of the output
		$exec = 'echo $(stty -a < "'.$tty.'" | grep -Po ';
		$exec .= "'(?<=columns )\d+')";
	

	exec($exec,$output,$result_code);
	if($result_code == 0)
	{
		$width = implode($output);
		$width--;

		$stars = '';
			for($x = 0; $x < $width; $x++){
				$stars .= STAR;
			}
		echo PHP_EOL.$stars.PHP_EOL;
	}

}
else
{
	// unable to discern width of terminal and print stars
	// print default 
	echo "++++++";
}

} // end print stars








} // end InternxtConnection










?>
