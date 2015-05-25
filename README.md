# getinfected

##About
**getinfected** is the initial teacher virus PHP infection script that is used to install the core teachervirus files.  

If consists of a single file getinfected.php - we're using a single file in order to make the initial copy/move into HTDOC as simple as possible.

Once this file is run the basic Teacher Virus infection should be complete and ready to install payloads as required.

**NOTE: ** Teacher Virus is only viral from a philosophical perspective - it can not infect a device without the consent of the administrator/owner of the device (installation action required).

##For Android:
The getinfected.php file will be included in the android version as part of the teachervirus.apk   In the android version the first fun of the app will copy this file to the htdocs folder of the local webserver and open it.  Once the installation is complete the app will then default to running from the /play location (via a webview).

##For Other Devices
Teacher Virus operates on any devices with a compatible webserver (HTML/PHP/SQLite) and sufficient rights

For installation on these devices the getinfected.php file needs to be placed in the root of the webserver's public folder (i.e. htdocs) and opened. If there is no "play" folder then it will install. 

##Assumptions
To keep things simple we make the following assumptions at this point:
* getinfected.php will be in the root folder of the webserver.

##Getting Technical 
The getinfected.php script does the following (in basically this order):

* checks to see if there is a 'play' directory.  
  - If yes: Displays alert advising that it looks like there is already an installation of teachervirus - if you want to reinstall remove the play directory and then provides link to 'play' directory rather than install. 
  - If no: commences install:
* Checks to see if the 'ip' param exists:
  - if yes: then download step will use that source (expects just IP address) instead of github.
* Creates a folder called 'infect' in the same folder as the getinfected script 
  - checks folder is created if not provides error message suggesting possibility of insufficient rights.
* checks if github server or ip is accessible [Curl?] if not then provides error message advising unable to contact source of infection. 
  - prompts user to enter alternative infectious device IP address (and advice on how to find that) and reloads page with that setting as the ip param 
* Downloads OATSEA-teachervirus.zip into the 'infect' folder either from OATSEA/teachervirus through github zipball/master or from ip address (file is left in this folder so that it is there to then infect others).

* unzips downloaded infect/OATSEA-teachervirus.zip into a unique temporary folder
* moves (via rename) files into the same location as the getinfected script (should be root).
 - checks if folders & files already exist & deletes them before attempting to move [ not working - needs investigation ]
* moves infected.php.zip, android.apk and droidPHP.apk into 'infect' folder 
* redirects to 'admin' folder for initial configuration (i.e. set password and other various configurations)

