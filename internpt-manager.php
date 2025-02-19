#!/usr/bin/env php
<?php
/**
* 2025 02 19
* v0.6
* Requirements:
* 
* check for presence of rclone
* create doCredentials - list what should be put in the credentials file
* set out the params and returns
**
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

function printstars() { 
	/** print stars onto terminal - for the width of the terminal
	 * @return a line of *
		* **/

/* discover the tty number for this process */
	$exec = '/usr/bin/tty';
	exec($exec,$output,$result_code);

	if($result_code == 0)
	{
		// work out the width of the output
		$tty = implode($output);
		$exec = 'echo $(stty -a < "'.$tty.'" | grep -Po ';
		$exec .= "'(?<=columns )\d+')";
	}

	exec($exec,$output,$result_code);

	if($output[0] == $tty)
	{
		$width = $output[1];

		$stars = '';
			for($x = 0; $x < $width; $x++){
				$stars .= STAR;
			}
		echo PHP_EOL.$stars.PHP_EOL;
	}

}



function preFlight($command)
{

	/** check whether the escaped command is available to execute, else print help and die  **/	
$cmd_array = array("install","start","stop","uninstall","status","remote_status","credentials","getTerminalWidth","webdav_status","check","help","restart");	

if( (!in_array($command,$cmd_array,'strict'))) {

	InternxtConnection::doHelp();

die();

}




/*
 * Other preflight checks before the Internpt Manager can be executed
 * It is fine that there are so many and that they will run every time because this is not something that will be used everyday
 */


/* check correct permissions and ownership */
if(fileowner(__FILE__) != 0 || filegroup(__FILE__) != 0)
{
        error('Oh dear! This file should be owned by root alone');
info('Please change ownership via the following command as root:'); 
info('chown -f root:root '.__FILE__);
info('After you have done this, run '.__FILE__.' '.$command.' again');
die();

}


if( substr(sprintf("%o",fileperms(__FILE__)),-4) != '0700' )
{
        error('Oh dear! This file should have strict permissions');
info('Please change permissions via the following command as root: $ chmod -f 0700 '.__FILE__);
info('After you have done this, run '.__FILE__.' '.$command.' again');
die();
}


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
		$internpt->doInit();
	break;

	case "start":
		$internpt->startWebDav();
		$internpt->startService();
	break;

	case "stop":
		$internpt->doStop();
	break;

	case "restart":
		$internpt->doStop();
		$internpt->startWebDav();
		$internpt->startService();
	break;



	case "status":
		$internpt->doStatus();
	break;

	case "remote_status":
		$internpt->getRemoteStatus();
	break;

	case "credentials":
		$internpt->doCredentials();
	break;

	case "getTerminalWidth":
		$internpt->getTerminalWidth();
	break;

	case "webdav_status":
		$internpt->getWebDavStatus();
	break;

	case "check":
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
	private readonly string $internpt_log;
	private readonly string $rclone_log;
	private readonly string $rclone_config;
	private readonly array $credentials;
	private readonly string $credentials_file;
	private readonly string $service_file;
	private readonly string $service;
	private readonly string $unit;

	public function __construct($credentials_file,$action = 'install')
	{
		$this->credentials_file = $credentials_file;
		$this->credentials = json_decode(file_get_contents($this->credentials_file),true);
		$this->username = $this->credentials['username'];
		$this->uid = $this->credentials['uid'];
		$this->guid = $this->credentials['guid'];
		$this->mountpoint = $this->credentials['mountpoint'];
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
		$this->startWebDav();
		$this->createServiceFile();
		$this->enableServiceFile();
		$this->doDaemonReload();
		$this->startService();
	}

public function doCheck() {
/*
*	Checks whether when the Systemd Service is online that the webdav service is also running
 */
		//	 $this->doWriteLog("Checking whether both Internpt Service and Webdav Server are running");
	if($this->getRemoteStatus()) {
	//	$this->doWriteLog("Check Internpt Service Manager running".$this->getWebDavStatus());
		if(false === $this->getWebDavStatus())
		{
			 $this->doWriteLog("Webdav Server offline while Internpt Service is running. Restarting WebDav");
			 $this->startWebDav();
		}
	}else{
	//	$this->doWriteLog("Internpt Service Manager NOT running");
	}
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
		       		success('Created Internpt Manager Log ✓'.implode($output).' r'.$result);
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


		fclose($this->loghandle);
		exec('rm -f '.$this->credentials['internpt_log']);
		info("Internpt Manager log for ".$this->credentials['username']." deleted");
}

public function doStop() {
/*
 *	Stop internpt-manager processes
*/

		$this->doWriteLog("Internpt Manager Stop Requested");
		info("Stopping Internpt Service Manager...");
		$this->stopService();
		$this->doUnmount();
		$this->stopWebDav();
		finaltext("The Internpt Service Manager has stopped");
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
		info("Delete the file internpt-manager to complete the uninstall");
}



public function doLogin() {
/*
 *	Login to Internxt CLI
*/


		$exec = "/usr/local/bin/internxt login -x -e ".$this->internxt_email." -p '".$this->internxt_password."' ".R;
		exec($exec,$output,$result);
		
			if(str_contains(implode($output),'Succesfully logged in')) {
				/*** Successful Login ***/

                        	$this->doWriteLog("Successfully logged into Internxt Account");
				success('Logged into Internxt Account ✓');

			}else{

				/*** Login Failure ***/

                        $this->doWriteLog("Failed to log into Internxt Account");

		$exec = "/usr/local/bin/internxt login -x -e ".$this->internxt_email." -p '".$this->internxt_password."' ".R;
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


public function startWebDav() {
/*** 
 * Enable  WebDav via Internxt CLI 
 * ***/

$exec = "/usr/local/bin/internxt webdav enable ".R;
exec($exec,$output,$result);
        /*** Attempt Start of WebDav ***/

                if(str_contains(implode($output),'online'))
                {

                        $this->doWriteLog("WebDav successfully enabled");
                        success("WebDav Service enabled ✓");
		}else{
			/*** Enabling WebDav Failed ***/
                        $this->doWriteLog("WebDav Not enabled");

                        error('Oh dear! Unable to enable WebDav');
                die();

		}

} // end startWebdav



public function stopWebDav() {
/*** 
 * Disable  WebDav via Internxt CLI 
 * ***/


$exec = "/usr/local/bin/internxt webdav disable ".R;
exec($exec,$output,$result);
        /*** Attempt Stop of WebDav Service ***/
		if(intval($result) == 0)
                {

                        $this->doWriteLog("WebDav successfully disabled");
                        success('Webdav Service stopped ✓');
		}else{
			/*** Stopped WebDav Failed ***/

                        $this->doWriteLog("WebDav NOT disabled - ".implode($output));
                        error('Oh dear! Unable to stop WebDav Service');

		}

} // end stopWebdav


public function getWebDavStatus() { 
/*** 
 * Get  WebDav Status 
 * @return string
 * ***/

	$exec = "/usr/local/bin/internxt webdav status ".R;

	exec($exec,$output,$result);
	if($result == 0)
	{
		if(str_contains(implode($output),"status: online"))
		{
			return true;
                        $this->doWriteLog("Webdav Status: Online");
		}else{
                        $this->doWriteLog('Webdav Status: Offline');
			return false;
		}
	}else{
		echo "-1";
                        $this->doWriteLog('Webdav Status: Unavailable'.implode($output));
	}
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
ExecStart=/usr/bin/rclone mount --config=".$this->rclone_config." --timeout 5h --allow-other --allow-non-empty --no-check-certificate --buffer-size 1G --cache-tmp-upload-path=/tmp/rclone/upload --cache-chunk-path=/tmp/rclone/chunks --cache-workers=16 --cache-writes --vfs-cache-mode full --cache-dir=/tmp/rclone/vfs --cache-db-path=/tmp/rclone/db --no-modtime --drive-use-trash --stats=0 --log-file ".$this->rclone_log." --log-level INFO --checkers=16 --bwlimit=180M --rc-addr=localhost:5575 --gid ".$this->guid." --uid ".$this->uid." --dir-cache-time=10s --poll-interval=10s --cache-info-age=60m --rc ".$this->rclone_remote.":/ ".$this->mountpoint."
ExecStop=/usr/bin/fusermount -uz ".$this->mountpoint."
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

	} // end disableServiceFile



	
public function doStatus() {
/*** 
 * get Systemd Service status
 * ***/


	$exec = "/usr/bin/systemctl status ".$this->service_file.R;
	exec($exec,$output,$result);
	if(str_contains(implode($output),'internpt-manager.service not found')){
		finaltext('Status: Service is NOT active because it is NOT installed');
		info('Execute internpt-manager install to activate');
	}elseif(str_contains(implode($output),'Active: active (running)')){
		finaltext('Status: Service is active');
	}elseif( str_contains(implode($output),'(code=exited, status=143)')){
		finaltext('Status: Service is stopped');
	} else{
		finaltext('Status: Service Status unavailable');
		info('Has the Internpt Manager been intialised?');
	}

} // end of doStatus



	
public function getRemoteStatus() {
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
	}elseif(str_contains(implode($output),'Active: active (running)')){
		// active
		$r = 1;
	}elseif( str_contains(implode($output),'(code=exited, status=143)')){
		// stopped
		$r = 2;
	} else{
		// unavailable
		$r = 3;
	}
	echo $r;

} // end of doStatus


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
			die();
		}else{
			$this->doWriteLog("Internpt Service Manager File");

			$exec= "/usr/bin/cat /proc/mounts | grep ".$this->mountpoint." | wc -l"; 

			while( exec($exec,$mo,$mr) ) {
				if(implode($mo) == 1) break;
				info(chr(8).".");
				sleep(1);
			}
			$this->doWriteLog("Internxt Drive has Mounted");



				
			//$index = 0;
			//$animation = array('⢿', '⣻','⣽','⣾','⣷','⣯','⣟⡿');
			info("Mounting Internxt Drive...");
				
			$exec = "ls -1 ".$this->mountpoint." | wc -l; echo ".R;

			//while( $dir = scandir($this->mountpoint)) {
			while(exec($exec,$output,$result)) {
				if(count(implode($output))>0) break;
				info("\r.");
			}
			success("Mounted Internxt Drive ✓");

			$this->doWriteLog("Internxt Drive has Started Successfully");
			finaltext("Internxt Drive has started and is ready to be used ✓");
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


			success("Internpt Service Manager has been sucessfully stopped ✓");
		}
} // end stopService




public function doUnmount() {
/*** 
 * Unmount mount point
 * ***/

		exec('/usr/bin/umount -f '.$this->mountpoint.R,$output,$result);
		if($result == 0) {
			$this->doWriteLog("Successfully Unmouted Drive");

		}else{
			$this->doWriteLog("Unable to Unmout Drive - Error Code: ".$result. ' output: '.implode($output));


					if(str_contains(implode($output), 'target is busy')) {

						info("The mount point is busy.");
					        info("Close all files/applications/terminals using the directory '".$this->mountpoint."' and call this command again.");

					} elseif(str_contains(implode($output), 'not mounted')){

						if($this->action != 'install')
						       	info("The mount point is not mounted, and so cannot be unmounted. No further action needed. ✓");
						return;

					}

		}

		if($this->action == 'uninstall' || $this->action == 'stop') { 
			finaltext(ucfirst($this->action)." request NOT completed");
			die();
		}
                        	error("Oh dear! Unable to unmount Internxt Drive from ".$this->mountpoint);

			

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

printstars();
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

(1) PHP version >= 8 must be installed
(2) Rclone must be installed. For best results, choose the most recent version available.
(3) As root, follow the instructions here (https://help.internxt.com/en/articles/9720556-how-to-install-the-internxt-cli) to download the Internxt CLI
(4) As root, create a rclone WebDav remote following the instructions here ()
(5) The directory upon which the Internxt Drive should be mounted should be created. This should be done by the non-root user who needs to access the Internxt Drive. WARNING: Do not use ~/'Internxt Drive' to avoid conflicting with the official applications. 
(6) As root, in the file /etc/fuse.conf, remove the # symbol at the beginning of the line containing 'allow_use_other' 
(7) As root, create the file /root/.internpt/credentials.json. Enter the key information - execute   ".__FILE__." credentials   for more information
");
printstars();
}// end doHelp


public static function doCredentials(){
/*** 
 * Print Help about the Credentials file
 * ***/


	printstars();
	info('The credentials file contains all the information that this script needs to initialise and manage your Internxt Drive connection.

It should be created here:

/root/.internpt/credentials.json

The format it should use is as follows (use your own values as appropriate):

{
        "username":"jess",
        "uid":"1000",
        "gid":"1000",
        "mountpoint":"/home/jess/Internxt",
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
 internxt_email - the email address associated with your Internxt Account
 internxt_password - the password associated with your Internxt Account
 internpt_log - the directory and log file to be used where messages from the '.__FILE__.' script are to be recorded - NOTE: best practice would suggest a per user log file 
 rclone_remote - the name of the rclone remote connecting to the Internxt WebDav Server 
 rclone_log - the directory and log file to be used where messages from rclone for the above remote are to be recorded - NOTE: best practice would suggest a per user log file 
 rclone_config - the rclone directory and config file setting out the details of the remote ');
printstars();


} // end doCredentials



} // end InternxtConnection










?>
