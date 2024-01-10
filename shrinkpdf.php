<?php

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

	/* Ignore all .dot-files */
	$gFilesInArrayTmp=array();
	foreach($gFilesInArray as $lFilesInArrayKey => $lFilesInArrayValue)
	{
		if ($lFilesInArrayValue[0]!='.') // only if not hidden file (or directory)
		{
			array_push($gFilesInArrayTmp, $lFilesInArrayValue);
		}
	}
	$gFilesInArray=$gFilesInArrayTmp;
	
	/* Sort'em - https://www.php.net/manual/en/function.sort.php */
	say("gFilesInArray (unsorted):", __FILE__, __FUNCTION__, __LINE__, 2);
	sayArray($gFilesInArray, __FILE__, __FUNCTION__, __LINE__, 2);
	sort($gFilesInArray, SORT_NATURAL | SORT_FLAG_CASE);
	say("gFilesInArray (sorted):", __FILE__, __FUNCTION__, __LINE__, 2);
	sayArray($gFilesInArray, __FILE__, __FUNCTION__, __LINE__, 2);

	/* Checke mal, obs überhaupt ein oder mehr sind… */
	$gFilesInArrayCount=count($gFilesInArray);
	if ($gFilesInArrayCount<1)
	{
		echo("<font color=\"red\">$gIntMergeNotEnoughFiles</font><br/>");
		$gFailed=true;
	}

	/* Checke mal, ob das alles PDFs sind! */
	echo("<font size=\"1\">");
	printf($gIntMergeCheckingFiles, count($gFilesInArray));
	if (!$gFailed)
	foreach ($gFilesInArray as $lFileNum => $lFileName)
	{
		if ($lFileName!="." && $lFileName!="..")
		{
			printf($gIntMergeCheckingFile, $lFileName);
			$lFileNameParts = explode('.', $lFileName);
			$lFileNameSuffix = end($lFileNameParts);
			echo("$lFileNameSuffix");
			if (strtoupper($lFileNameSuffix)!="PDF")
			{
				echo("<font color=\"red\">&rarr;$gIntMergeNoPDF</font>");
				$gFailed=true;
			}
			else
			{
				echo("<font color=\"green\">&rarr;$gIntMergeOK</font>");
			}
			echo("<br/>");
		}
	}
	echo("</font><br/><br/>");

	/* Shrink the PDFs */
	if (!$gFailed)
	{
		foreach ($gFilesInArray as $lFileNum => &$lFileName)
		{
			$lFileName = str_replace(' ', '\ ', $lFileName); //escape spaces for command line
			$lFileName = "$gUploadFolder/$lFileName";
		}

HIER GEHTS WEITER MIT GHOSTSCRIPT!

#		$gFilesInString=implode(" ", $gFilesInArray);
#		$lCmd="pdfunite $gFilesInString  \"$gFileOutPath\"";
#		say("lCmd: $lCmd", __FILE__, __FUNCTION__, __LINE__, 2);
#		shell_exec($lCmd);
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
		printf($gIntMergeDownloadHere, $gFileOutPath, $gFileOutName);
	}
} // if upload-session-folder-exists
else
{
	echo("<font color=\"red\">$gIntMergeNoFiles</font><br/>");
}

echo($gIntMergeConvertMore);

include("footer.php");


?>

