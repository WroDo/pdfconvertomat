<?php
/* https://startutorial.com/view/dropzonejs-php-how-to-build-a-file-upload-form */

/* Includes */
require_once('etc/globals.php');
require_once('include/commonFunctions.php');
require_once('include/commonLogging.php');
require_once('include/commonFiles.php');
require_once('include/internationalization.php');

/* Init */
session_start(); /* https://www.php.net/manual/en/function.session-start.php */
//setlocale (LC_ALL, $gLocaleWeb); # Also see: locale -a

/* Defaults */
$errArray           =   array();
$gSessionID         =       session_id();

/* Some Debugging */
say("gSessionID: $gSessionID", __FILE__, __FUNCTION__, __LINE__, 2);
say("_REQUEST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_REQUEST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_POST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_POST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_FILES:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_FILES, __FILE__, __FUNCTION__, __LINE__, 2);


$ds          = DIRECTORY_SEPARATOR;  //1
 
$storeFolder = "$gFolderUploadName/$gSessionID";   //2
say("storeFolder: $storeFolder", __FILE__, __FUNCTION__, __LINE__, 2);
if (file_exists($storeFolder)===false) { mkdir($storeFolder); }
 
if (!empty($_FILES))
{
    $status=$_FILES['file']['error'];
    $tempFile = $_FILES['file']['tmp_name'];          //3             
      
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
     
    $targetFile =  $targetPath. $_FILES['file']['name'];  //5

    if ($status!=0)
    {
		say("File \"$tempFile\" reported an error ($status).", __FILE__, __FUNCTION__, __LINE__, 2);
	}
	else
	{
		move_uploaded_file($tempFile, $targetFile); //6
	}
}
else
{
        say("Empty _FILES.", __FILE__, __FUNCTION__, __LINE__, 0);
}


?>
