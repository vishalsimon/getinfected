<?php
// getinfected.php is the initial teacher virus PHP infection script that is used to install the core Teacher Virus files.
// Created: May 2015
// Contributors: Harry Longworth
// License: Apache 2.0
// TO DO:
// - multi lingual version?

// file needs permission 755
// Issues with CURL:
// - doesn't work out of the box with standard RPI
// - file permissions not set
// so try copy first and then CURL
//Edited by Vishal for testing

$debug=1;

if ($debug) {
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
} 

// ERROR HANDLING try below maybe?
// SOURCE: http://stackoverflow.com/questions/1475297/phps-white-screen-of-death

// ------------------------
// Declare Helper Functions
// -------------------------

// RRMDIR: Recursively remove subdirectories function 
// SOURCE: taken http://php.net/manual/en/function.rmdir.php 
function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
} // END RRMDIR

//-------
// prompt for IP address as alternative infector

function promptForIP() {
    // Prompt for IP of alternative device and reload page
    
    $thisurl = $_SERVER["SCRIPT_NAME"];
    // reload page script:
    echo "<script>
    function buttonClick() {
        var address = document.getElementById('address').value;     
        window.location ='$thisurl?ip='+address;
    } 
    </script>";
    
    echo "<h1>Try Alternate Source?</h1>
    <p>Enter IP address or DNS of infected device</p>
    <p><b>Tip:</b> You can find the IP address of an infected device in the admin page of Teacher Virus.</p>
    <p>Address of Infected Device:</p>
    <p><input id='address' type='text' name='address' required></p>
    <p><button type='button' onclick='buttonClick();'>Go!</button></p>
    ";
    
    exit("<hr>");
    
} // END promptForIP

//----------
//Make a new directory with optional error messages
function makeDIR($directory,$debugtxt=0) {
    
    // Create infect directory if it doesn't exist:
    if (file_exists($directory)) {
        if ($debugtxt) { echo "<p>Directory <b>$directory</b> already exists </p>"; }
        $result = true; // Return true as success is when the directory has either been created or already exists
    } else {
        // Make the new temp sub_folder for unzipped files
        if (!mkdir($directory, 0755, true)) {
            if ($debugtxt) { echo "<p>Error: Could not create folder <b>$directory</b> - check file permissions";}
            $result= false;
        } else { 
            if ($debugtxt) { echo "Folder <b>$directory</b> Created <br>";}  
            $result = true;
        } // END mkdir
    } // END if file exists
    return $result;
} // END makeDIR 


//---------
// Move Directory

function moveDIR($dir,$dest="") {
    $debug = 1;
    $result=true;
    
    if($debug) { echo "<h2>Moving directory</h2><p> From:<br> $dir <br>To: $dest</p>";}

    $path = dirname(__FILE__);
    $files = scandir($dir);
    
    foreach($files as $file) {
        if (substr( $file ,0,1) != ".") {
            $pathFile = $dir.'/'.$file;
            if (is_dir($pathFile)) {
                if($debug) { echo "<p><b>Directory:</b> $pathFile</p>"; }

                $newDir = $dest."/".$file;
                
                if (!moveDIR($pathFile,$newDir)) {
                    $result = false;
                }
                
            } else {
                echo "<p>$pathFile is a file</p>";
 
                // $currentFile = realpath($file); // current location
                $currentFile = $pathFile;

                $newFile = $dest."/".$file;
    
                if (!file_exists($dest)) {
                    makeDIR($dest);
                }
                // if file already exists remove it
                if (file_exists($newFile)) {
                    if($debug) { echo "<p>File $newFile already exists - Deleting</p>"; }
                    unlink($newFile);
                } else {
                    if($debug) { echo "<p>File $newFile doesn't exist yet</p>"; }
                }
        
                // Move via rename
                // rename(oldname, newname)
                if (rename($currentFile , $newFile)) {
                    if($debug) { echo "<p>Moved $currentFile to $newFile</p>"; }
                } else {
                    if($debug) { echo "<p>Failed to move $currentFile to $newFile</p>"; }
                    $result = false;
                } // END rename 
                
            } // END if dir or file
        } // end if no dot
    } // END foreach
    return $result;
} // END moveDIR

// -------------
// REDIRECT PAGE


function displayRedirect() {

    echo "
        <!DOCTYPE HTML>
        <html lang='en-US'>
        <head>
        <meta charset='UTF-8'>
        <meta http-equiv='refresh' content='1;url=play'>
        <script type='text/javascript'>
            window.location.href = 'play';
        </script>
        <title>Loading Teacher Virus</title>
        </head>
        <body>
            <!-- Note: don't tell people to `click` the link, just tell them that it is a link. -->
            <p>If you are not redirected automatically, follow the <a href='play'>link</a><p>
        </body>
        </html>
        ";
} // END displayRedirect

//-----------
// CHECK for Play Dir
// -----------

// Check play dir exists or not
if (file_exists('play')) {
    // if play folder exists then Teacher Virus is already installed and we don't want to allow script to run again so
    displayRedirect();

} else {
    if ($debug) { echo "<h1>Start <b>Teacher Virus</b> infection!</h1>";}
    if ($debug) { echo "<p>Directory <b>play</b> doesn't exist so continue with Teacher Virus infection<p>"; }
    // play folder doesn't exist
    // Check if ip param is set to either an IP address or a url (i.e. without http:// infront)    
    // $ip="10.1.1.38" or "test.teachervirus.org"
    
    if(isset($_GET["ip"])&&(!empty($_GET["ip"]))) {
        $ip= $_GET["ip"];
        echo "<p>Address has been provided as: $ip</p>";
    } else {
        $ip="no";
        echo "<p> IP Address not provided</p>";
    } // end IP is set check
    
} //  END play check
         
//----------------------------------    
// Download OATSEA-teachervirus.zip 
// ------------------------------------
if ($debug) { echo "<h2>Attempting to Download Teacher Virus</h2>"; }

$infect='infect';
// default destination for downloaded zipped files

// Create infect directory if it doesn't exist:
if (!makeDIR($infect,true)) { 
        // failed to make directory so exit
        exit("<h3>Infection Failed!</h3>");
}

// Github repository details for Teacher Virus core  
$username="OATSEA";
$repo="teachervirus";

$download_filename = $username."-".$repo.".zip";
$infectdir = $infect.'/'; // infect directory with trailing slash for URL use

$zipfile = $infectdir.$download_filename;

// Download file if OATSEA-teachervirus.zip doesn't already exist
if (file_exists($zipfile)) {
    if ($debug) { 
        echo "<p>The Teacher Virus files have already been downloaded to: $zipfile</p>
        <p>This infection will use the existing file rather than downloading a new version of Teacher Virus.</p>
        <p><b>Hint:</b> If you want to download a new version of Teacher Virus you will need to:</br>
        * delete the file: <b>$zipfile</b>.</br>
        * remove the <b>play</b> folder if it exists</br>
        * refresh/re-open <b>getinfected.php</b></p>"; 
    } // END Debug
} else {
    if ($ip=="no") {
        // Download from github zipball/master as no IP address set
        $geturl="https://github.com/$username/$repo/zipball/master/";
        // Issues with curl required use of format config above (e.g. no ' for some reason)

    } else {
        // as IP address has been set attempt download from IP address
        $geturl="http://$ip/$zipfile";
        
    }
    
    // TRY DOWNLOAD via copy
    if ($debug) { echo "<h2>Download Files</h2>
       <p>Will attempt to download via copy from <b>$geturl</b></p> ";}
    
    // ** TO DO ** catch warnings
    // get following error on MAC: 
    // Warning: copy(): SSL operation failed with code 1.
    $copyflag = copy($geturl,$zipfile);

    if ($copyflag === TRUE) {
        echo "<h3>Download Succeeded</h3><p>Files downloaded using <b>Copy</b> instead</p>";
    } else { 
        // try CURL    
    
        if ($debug) { echo "<p>Will attempt to download via CURL from <b>$geturl</b></p> ";}
    
        // USE CURL to Download ZIP
        // Code Attribution:  
        // http://stackoverflow.com/questions/19177070/copy-image-from-remote-server-over-https    
        // http://stackoverflow.com/questions/18974646/download-zip-php
        // http://stackoverflow.com/questions/11321761/using-curl-to-download-a-zip-file-isnt-working-with-follow-php-code
    
        set_time_limit(0); //prevent timeout
        
        $fp = fopen($zipfile, 'w+'); // or perhaps 'wb'?
        if (!$fp) { 
            exit("<h3><b>ERROR! Teacher Virus download failed</h3> 
            <p>Unable to open temporary file: <b>$zipfile</b>!</p>
            <p>File permission issue maybe?
            "); 
        }

        // ** TO DO ** add catch exception for curl not installed (e.g. RPI)
        $ch = curl_init();
        
        // CURL settings from Reference: http://php.net/manual/en/function.curl-setopt.php
        
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Don't use!
        curl_setopt($ch, CURLOPT_URL, $geturl);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50); // or 5040? - ** TO DO: Further testing required to optimise setting
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // was 2 try 0
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        // curl_setopt($ch, CURLOPT_SSLVERSION, 4); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Check connection status
        $curl_error_result = curl_error($ch);
    
        // Check if there were curl errors
        if ($curl_error_result) {
            $curlFlag=0; // Any contents means "true" - i.e. There's an error message so there were errors
        } else {
            $curlFlag=1; // false means all good - there were no errors 
        }
    
        $downloadResult=0;
        if (($http_status==200)&&(file_exists($zipfile))&&($curlFlag)) {
            if ($debug) {
                echo "<p> HTTP Status of: $http_status (200 is good)</p>";          
                echo "<p> Zip file successfully downloaded to $zipfile</p>";
            }  
            $downloadResult=1;    
        } else {
            if ($debug) {
                // There was a problem downloading
                echo "<h3>Curl Download Failed!</h3>
                    <p>Error Downloading Teacher Virus via CURL</p>";
                echo "<p> HTTP Status of: $http_status (200 is good)</p>";
                echo "<p> CURL error: ".curl_error($ch)." ...</p>";
                if (file_exists($zipfile)) {
                    echo "<p> Destination $zipfile file was created though</p>";
                }   else {
                    echo "<p> Destination $zipfile file was <b>NOT</b> created - file permission issue? </p>";
                }
            
            } // END debug
        
        } // END http_status and file exists check
    
        curl_close($ch);
        fclose($fp);
    
        if (!$downloadResult) {
            // As download failed delete empty zip file!
            if ($debug) { echo "<h2>Download with CURL failed</h2>";}
            echo "<h3>Infection Failed!</h3><p>Couldn't download with either copy or curl</p>";
            unlink($zipfile);
            promptForIP();
        } // If Download failed using CURL 
    }// END else CURL
} // END Download if zipfile doesn't already exists


// ---------------------
// UNZIP downloaded file
// ---------------------
                         
// Code Attribution: 
// http://stackoverflow.com/questions/8889025/unzip-a-file-with-php

if ($debug) {echo "<h2>Attempting to Unzip</h2><p>Zipped file:  $zipfile </p>";}

// get the absolute path to $file - not used as using location of script instead
// $path = pathinfo(realpath($zipfile), PATHINFO_DIRNAME);

// Create full temp sub_folder path
$temp_unzip_path = uniqid('unzip_temp_', true)."/";

if($debug) { echo "Temp Unzip Path is: ".$temp_unzip_path."<br>"; }
 
// Make the new temp sub_folder for unzipped files
if (!mkdir($temp_unzip_path, 0755, true)) {
    exit("<h2>Error - Infection Failed!</h2><p> Could not create unzip folder: $temp_unzip_path</p><p>File security or permissions issue?");
} else { 
    if($debug) { echo "<p>Temp unzip Folder Created! <br>"; }
}

umask(0);
$zip = new ZipArchive;
$zipFlag = $zip->open($zipfile);
if ($zipFlag === TRUE) {
  // extract it to the path we determined above
  $zip->extractTo($temp_unzip_path);
  // $zip->extractTo($path);
  $zip->close();
    if($debug) { echo "<h3>Unzip Successful!</h3><p> $zipfile extracted to $temp_unzip_path </p>"; }
} else {
  exit("<h2>Infection Failed!</h2><p> couldn't open $zipfile </p>");
}

// -------------------------    
// Determine Subfolder Name
// ------------------------- 

// GitHub puts all files in an enclosing folder that has a changing suffix every time.
// It does this to indicate commits.
// As a result we can't assume the name of the folder.
// and need to determine the name of the subfolder
        
if($debug) { echo "<h2>Determine Github subfolder</h2><p>Starting from folder: $temp_unzip_path </p>"; }
$subfolder='notset';

$files = scandir($temp_unzip_path);

$tally=0;
foreach($files as $file) {
    $tally++;
    // if($debug) {echo "Filename: $file";}
    if (substr( $file ,0,1) != ".") {
        $subfolder=$temp_unzip_path.$file; 
    } // END if not .
        
} // END foreach

// if($debug) { echo "<p><b>Tally:</b> $tally </p>";}
if($debug) { echo "<p>Subfolder is : $subfolder </p>";}
 

// ----------
// Move Files To Root 
// ----------
// move unzipped files to the same directory as the script (should be root)
// Warning/TEST! it probably won't move hidden files?

if($debug) { echo "<H2>Moving Files</h2>"; }

// $startingloc = $temp_unzip_path.'/'.$subfolder;
$startingloc = $subfolder;

if($debug) { echo "<p>Files being moved from: $startingloc </p>"; }

$tally2=0;

$subfolder = realpath($subfolder);
if($debug) { echo "<p>Real Path is : $subfolder </p>"; }

if($debug) { echo "<p>Is subfolder directory readable?".is_readable($subfolder)."</p>";}
    
$directory_iterator = new RecursiveDirectoryIterator($subfolder,FilesystemIterator::SKIP_DOTS);

$fileSPLObjects =  new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::SELF_FIRST,RecursiveIteratorIterator::CATCH_GET_CHILD);

try {
    
  foreach($fileSPLObjects as $file) {
    $tally2 ++;
	$filename= $file->getFilename();	
    // if($debug) { echo "<p>Current Filename: $filename </p>"; }
    
	if (($file->isDir())&&(substr( $filename ,0,1) != ".")) {
        // As it's a directory make sure it exists at destination:
        
        // Destination:
        $newDir = str_replace("/".$startingloc, '', realpath($file));
        
        // if directory doesn't exist then create it
        if (!makeDIR($newDir,1)) {
            if($debug) { echo "<p>Failed to create directory: $newDir</p>"; }
        }
    } else {
        // It's a file so move it
        // ** TEST: what if directory hasn't been created yet?? or does Recursive always do the directory first
        
        $currentFile = realpath($file); // current location
        $newFile = str_replace("/".$startingloc, '', realpath($file)); // Destination
        
        // if file already exists remove it
        if (file_exists($newFile)) {
            if($debug) { echo "<p>File $newFile already exists - Deleting</p>"; }
            unlink($newFile);
        }
        
        // Move via rename
        // rename(oldname, newname)
        if (rename($currentFile, $newFile)) {
            if($debug) { echo "<p>Moved <br> $currentFile <br>to  $newFile</p>"; }
        } else {
            if($debug) { echo "<p>Failed to move <br>$currentFile <br>to $newFile</p>"; }
        } // END rename 
        
    }// END is Dir or File checks
    
  } // END foreach
} // END Try
catch (UnexpectedValueException $e) {
    echo "<h2>Error Moving Files!</h2><p>There was a directory we couldn't get into!</p>";
}
if ($debug) {echo "<p>Loop Count: $tally2</p>";}

// --------------------
// HANDLE MOVE FAILURE:
// IF Tally2 is zero then move failed try alternative method based on scandir

if ($tally2==0) {
    if($debug) { echo "<h2>File Move Failed!</h2><p> - Attempting alternative approach</p>"; }

    $destination  = dirname(__FILE__);

    // if($debug) { echo "<p>Moving files from<br>  $subfolder <br> to: $destination</p>"; }

    if (moveDIR($subfolder,$destination)) {
        if($debug) { echo "<h2>Move Succeeded!</h2>"; }
    } else {
        if($debug) { "<h2>ERROR! Move Failed!</h2><p>Infection Failed</p>"; }
    } // End moveDIR check
    
} // END try alternative move approach

// DELETE TEMP     
// Recursively Delete temporary unzip location
rrmdir($temp_unzip_path);
 
// redirect page to admin page to commence configuration
// ** TO DO ***

// current test stub instead of admin page opens in new window:
if ($debug) {echo '<h2>Infection Complete!</h2><p>Check infection has worked: </p><p><a href="admin" target="_blank">Click Here for Admin Page</a></p><p>or</p><p><a href="play" target="_blank">Click Here for PLAY Page</a></p>';}

?>
