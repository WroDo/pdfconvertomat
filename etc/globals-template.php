<?php 

/* Globals */
$gRootPath          =   "/srv/www/htdocs/pdfconvertomat"; /* The root of all evil :) */
$gFileLog           =   "$gRootPath/log/#log.log"; /* # Makes CVS ignore the file */
$gFilesPath         =   "$gRootPath/files"; /* In diesem Ordner landen alle Uploads und Mail-Attachments */
$gLogLevel          =   2;              /* As usual: 0 = only errors, 1 = warnings, 2 = everything, 3 = even more (floods the log :) ) */
$gLocaleWeb         =   "en_US.utf8";   /* See your LAMP's locale -a */
$gFileLogMaxSize    =   1024*1024*42;   /* 42MB :) */ 
$gMaxFilenameLength =	64;
$gSiteLogo			=	"images/companylogo.png";
$gCustomHeaderLines	=	"";
$gFolderUploadName  =	"uploads";
$gSiteLanguage		=	"de"; 


if ($gSiteLanguage=="de")
{ // german
	$gSiteName				=	"PDF-Konvertier-O-Mat";
	$gShortIntro			=	"<br/>
	Und so gehts:<br/>
	<br/>
	1. Lege die zu konvertierenden Dateien in der <font color=\"LightPink\">pinken</font> Fl&auml;che ab.<br/>
	<br/>
	2. Klicke einen der Buttons</br>
	<br/>
	3. Lade das Ergebnis herunter</br>
";
	$gButtonMergeLabel		=	"Klicke hier<br/>um die<br/>Dateien zusammenzuf&uuml;gen";
	$gButtonCleanupLabel	=	"Klicke hier<br/>um<br/>den Vorgang abzubrechen";
	$gConvertMore			=	"<br/>Wenn Du noch mehr Dateien konvertieren möchtest, klicke <a href=\"./index.php\">hier</a>.<br/>";
}
elseif ($gSiteLanguage=="en")
{ 
	$gSiteName				=	"PDF-Convert-O-Mat";
	$gShortIntro			=	"<br/>
	This is how it works:<br/>
	<br/>
	1. Drop the file on the <font color=\"LightPink\">pink</font> area.<br/>
	<br/>
	2. Click one of the buttons.</br>
	<br/>
	3. Download the resulting file(s).</br>
";
	$gButtonMergeLabel		=	"Click here<br/>to merge files";
	$gButtonCleanupLabel	=	"Click here<br/>to cancel";
	$gConvertMore			=	"<br/>To convert more, click <a href=\"./index.php\">here</a>.<br/>";
}
// add other languages here


?>
