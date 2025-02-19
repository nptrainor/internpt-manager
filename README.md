# internpt-manager
A PHP Script for Linux which mounts an Internxt Drive to your filesystem using Rclone, Systemd, and Internxt CLI.

This is a personal project and is in no way connected to or endorsed by Internxt.

What I needed:
I needed a simple script to connect my Internxt Drive to my linux computer.

Those apps provided by the company at the time did not fit completely with my requirements.

I wanted a script that would:

1) work in the background
2) be easily controllable
3) be easily scriptable
4) work with Wayland or X11
5) give me smooth access to my Internxt Drive.

This script allows me, and hopefully you, to do this.

Install

1) Install the Internxt CLI on to your computer by following these instructions (https://help.internxt.com/en/articles/9720556-how-to-install-the-internxt-cli). You need to do this as root.
2) Install Rclone and set up an Internxt remote. The linux user who wants to use that remote should do this. So, if Jess wants to access her Internxt files on her computer, then she should use Rclone to create a webdav remote to Internxt. See how to do this here: https://help.internxt.com/en/articles/9766579-how-to-connect-internxt-drive-to-rclone-through-webdav.
3) Download the internpt-manager.php file above.
4) Everything else needs to be done as root (in order to ensure easy access to systemd).
5) Move the internpt-manager.php to /usr/local/bin (for example).
6) chown root:root /usr/local/bin/internpt-manager.php
7) chmod 0700 /usr/local/bin/internpt-manager.php
8) Create the following directory and file: /root/.internpt/credentials.json
9) The credentials file gives the script all the information it needs. This is what it should look like (amend as necessary):

{

        "username":"jess",
        
        "uid":"1000",
        
        "gid":"1000",
        
        "mountpoint":"/home/jess/Internxt",
        "internxt_email":"jess@example.com",
        
        "internxt_password":"my excellent password by jess",
        
        "internpt_log":"/var/log/internpt-manager/jess-combined.log",
        
        "rclone_remote":"Internxt",
        
        "rclone_log":"/var/log/rclone/jess-internpt.log",
        
        "rclone_config":"/home/jess/.config/rclone/rclone.conf"
        
}

Where:

a) username - the username of the owner and user of the Internxt Drive directory

b) uid - the user ID used for the mount point directory and its associated contents (for example, Jess' UID in /etc/passwd is 1000)

c) gid - the group Id used for the mount point directory and its associated contents (for example, Jess' GID in /etc/group is 1000)

d) mountpoint - the place where the Internxt Drive is to be mounted. To avoid conflicting with the official Internxt apps do NOT use "~/Internxt Drive".

e) internxt_email - the email address associated with your Internxt Account

f) internxt_password - the password associated with your Internxt Account

g) internpt_log - the directory and log file to be used where messages from the /usr/local/bin/internpt-manager.php script are to be recorded - NOTE: best practice would suggest a per user log file. Created automatically on install.

h) rclone_remote - the name of the rclone remote connecting to the Internxt WebDav Server which Jess has already set up.

i) rclone_log - the directory and log file to be used where messages from rclone for the above remote are to be recorded - NOTE: best practice would suggest a per user log file. Created by rclone

j) rclone_config - the rclone directory and config file setting out the details of the remote 


 Install (continued)

 10) Now set the permissions on the credentials file - chmod 0700 -Rf /root/.internpt
 11) Create the mountpoint - so Jess will do this: mkdir /home/jess/Internxt
 12) Now, returning to being root, you can install the script by issuing the following command: /usr/local/bin/internpt-manager.php install
 13) A series of messages will be relayed as the scripts works it way through the process.
 14) When it says that the Internxt Drive is avaiable, then you will be able access your files. Initially, there may be a few seconds delay (maybe 10 seconds).
 15) You can then send and receive files directly through rsync or whatever is your preferred file mover of choice.

Management

After the initial install procedure, you can control the internpt-manager processes with the following commands:

internpt-manager [command]

where command is one of:

install - We've just done that!

uninstall - removes all the files used by the internpt-manager

stop - stops the rclone mount by stopping the systemd unit. Unmounts the Internxt Drive folder (eg. for Jess, it unmounts: /home/jess/Internxt)

restart - stop and then start

status - relates the status of the systemd rclone mount. One of: Status: Service is NOT active because it is NOT installed / Status: Service is active / Status: Service is stopped / Status: Service Status unavailable

remote_status - relates the status of the systemd rclone mount as a single digit. Inactive: 0; Active: 1; Stopped: 2; Unavailable: 3

credentials - prints help on setting up the credentials file (as above)

webdav_status - returns status of Internxt WebDav service on your device true (online) / false (offline) / -1 (unavailable) 

check - checks whether the rclone mount is running but the webdav service is offline/unavailable (it has happened!) - restarts Internxt WebDav service on your device

help - prints a great big screen of help

getTerminalWidth - internal use only

     

Requirements:

1)PHP v7+ -you more than likely can get away with a lower version, but why would you?
2) Posix for PHP - I install the "php-process" module and this sorted it out
3) Rclone
4) Internxt CLI
5) a linux distro using Systemd

Gotchas:

I have noticed that sometimes the Internxt WebDav service seems to time out. This leads to the Internxt Drive not working.

I have setup a cron for root as follows which uses the 'check' command above to sort this out.

*/4 * * * * /usr/local/bin/internpt-manager check

There may be others, we'll see. If you find any, get in touch, and I will see how I can remediate or mitigate them.

Please feel free to post any issues you find.
