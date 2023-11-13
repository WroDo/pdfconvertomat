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
$gSessionID			=	session_id();
$gFailed			=	false;

/* Some Debugging */
say("gSessionID: $gSessionID", __FILE__, __FUNCTION__, __LINE__, 2);
say("_REQUEST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_REQUEST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_POST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_POST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_FILES:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_FILES, __FILE__, __FUNCTION__, __LINE__, 2);

/* Kuck mal, welche Files hier für diese Session liegen */
$gUploadFolder = "$gFolderUploadName/$gSessionID";
$gFileOutName="$gSessionID.pdf";
$gFileOutPath="$gUploadFolder/$gFileOutName";
if (file_exists($gUploadFolder))
{
	$gFilesInArray=scandir($gUploadFolder);

	/* Die ersten zwei Einträge sind "." und ".." */
	array_shift($gFilesInArray);
	array_shift($gFilesInArray);
	$gFilesInArrayCount=count($gFilesInArray);

	/* Lösche die Infiles */
	//if (!$gFailed)
	foreach ($gFilesInArray as $lFileNum => &$lFileName)
	{
		unlink("$gUploadFolder/$lFileName");
	}

	/* Lösche Session-Ordner */
	//if (!$gFailed)
	rmdir("$gUploadFolder");
}

?>
