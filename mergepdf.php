<?php
/* https://startutorial.com/view/dropzonejs-php-how-to-build-a-file-upload-form */

/* Includes */
require_once('etc/globals.php');
require_once('include/commonFunctions.php');
require_once('include/commonLogging.php');
require_once('include/commonFiles.php');

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

include('header.php'); // insert header incl. <body>-tag

echo("
	<br/>
");
/* Kuck mal, welche Files hier für diese Session liegen */
$gUploadFolder = "$gFolderUploadName/$gSessionID";
say("gUploadFolder: $gUploadFolder", __FILE__, __FUNCTION__, __LINE__, 2);
$gFileOutName="$gSessionID.pdf";
$gFileOutPath="$gUploadFolder/$gFileOutName";
if (file_exists($gUploadFolder))
{
	$gFilesInArray=scandir($gUploadFolder);

	/* Die ersten zwei Einträge sind "." und ".." */
	array_shift($gFilesInArray);
	array_shift($gFilesInArray);
	$gFilesInArrayCount=count($gFilesInArray);

	/* Checke mal, obs überhaupt zwei oder mehr sind… */
	if ($gFilesInArrayCount<2)
	{
		echo("<font color=\"red\">You selected less that two files. Try again. (Error -5)</font><br/>");
		$gFailed=true;
	}

	/* Checke mal, ob das alles PDFs sind! */
	if (!$gFailed)
	foreach ($gFilesInArray as $lFileNum => $lFileName)
	{
		if ($lFileName!="." && $lFileName!="..")
		{
//			echo("<font size=\"1\">");
//			echo("&Uuml;berpr&uuml;fe \"$lFileName\"…");
			$lFileNameParts = explode('.', $lFileName);
			$lFileNameSuffix = end($lFileNameParts);
//			echo("$lFileNameSuffix");
			if (strtoupper($lFileNameSuffix)!="PDF")
			{
//				echo("<font color=\"red\">&rarr; This file is no PDF and can not be processed. (Error -42)</font>");
				$gFailed=true;
			}
			else
			{
//				echo("<font color=\"green\">&rarr; Okidoki</font>");
			}
//			echo("</font><br/><br/>");
		}
	}

	/* Merge die PDFs */
	if (!$gFailed)
	{
		foreach ($gFilesInArray as $lFileNum => &$lFileName)
		{
			$lFileName = str_replace(' ', '\ ', $lFileName); //escape spaces for command line
			$lFileName = "$gUploadFolder/$lFileName";
		}

		$gFilesInString=implode(" ", $gFilesInArray);
		$lCmd="pdfunite $gFilesInString  \"$gFileOutPath\"";
		say("lCmd: $lCmd", __FILE__, __FUNCTION__, __LINE__, 2);
		shell_exec($lCmd);
	} // if not failed

	/* Lösche die Infiles */
	if (0)	include('cleanupinc.php');
	if (1)
	{
		/* Lösche die Infiles */
		//if (!$gFailed)
		foreach ($gFilesInArray as $lFileNum => &$lFileName)
		{
			shell_exec("rm $lFileName");
		}
	}

	if (!$gFailed)
	{
		echo("Hier kannst Du das Ergebnis herunterladen: <a href=\"$gFileOutPath\">$gFileOutName</a><br/>");
	}
} // if upload-session-folder-exists
else
{
	echo("<font color=\"red\">No files have been uploaded. Actually, not even the session-upload-folder exists. (Error -23)</font><br/>");
}

echo($gConvertMore);

include("footer.php");


?>

