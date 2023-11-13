<?php
/* https://startutorial.com/view/dropzonejs-php-how-to-build-a-file-upload-form */

/* Includes */
require_once('etc/globals.php');
require_once('include/commonFunctions.php');
require_once('include/commonLogging.php');
require_once('include/commonFiles.php');
require_once('include/internationalization.php');

/* Init */
//session_start(); /* https://www.php.net/manual/en/function.session-start.php */
//setlocale (LC_ALL, $gLocaleWeb); # Also see: locale -a

/* Defaults */
$errArray           =   array();
$gSessionID			=	session_id();

/* Some Debugging */
say("gSessionID: $gSessionID", __FILE__, __FUNCTION__, __LINE__, 2);
say("_REQUEST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_REQUEST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_POST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_POST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_FILES:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_FILES, __FILE__, __FUNCTION__, __LINE__, 2);

// https://mehimali.com.np/code-to-recursively-delete-files-and-folder-older-than-x-days/
$dir = 'uploads/';
$now   = time();
$dir = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
$dir = new \RecursiveIteratorIterator($dir,\RecursiveIteratorIterator::CHILD_FIRST);
$deleteFiles =[];
$message = "There is no file to delete";
if($dir)
{
    foreach ($dir as $file)
    {
       /* Comparing the current time with the time when file was created */
        if ($file && $now - filemtime($file) >= 60 * 60 * 24 * 1 && strpos($file, "README")===false )
        { // 1 days
            array_push($deleteFiles, $file);
            $file->isDir() ? rmdir($file) : unlink($file);
 //           $message = "All the following files are deleted.<br/>";
        } 
    }
}else
{
    
}

if (0)
{
	echo $message; 
	if($deleteFiles)
	{ 
		var_dump(implode('<br/>',$deleteFiles)); 
	}

}


?>
