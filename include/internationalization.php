<?php
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
	$gIntButtonMergeLabel		=	"Klicke hier<br/>um die<br/>Dateien zusammenzuf&uuml;gen";
	$gIntButtonCleanupLabel		=	"Klicke hier<br/>um<br/>den Vorgang abzubrechen";
	$gIntConvertMore			=	"<br/>Wenn Du noch mehr Dateien konvertieren möchtest, klicke <a href=\"./index.php\">hier</a>.<br/>";
	$gIntMergeNotEnoughFiles	=	"Du hast weniger als zwei Dateien ausgew&auml;hlt. Versuchs nochmal! (Error -5)";
	$gIntMergeNoPDF				=	"Dies ist kein PDF und kann nicht verarbeitet werden. (Error -42)";
	$gIntMergeOK				=	"Okidoki";
	$gIntMergeNoFiles			=	"Es wurden keine Dateien hochgeladen. Nichtmal der session-upload-Ordner existiert. (Error -23)";
	$gIntMergeCheckingFiles		=	"&Uuml;berpr&uuml;fe %d Dateien…<br/>";
	$gIntMergeCheckingFile		=	"&Uuml;berpr&uuml;fe \"%s\"…";
	$gIntMergeDownloadHere		=	"Hier kannst Du das Ergebnis herunterladen: <a href=\"%s\">%s</a><br/>";
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
	$gIntButtonMergeLabel		=	"Click here<br/>to merge files";
	$gIntButtonCleanupLabel		=	"Click here<br/>to cancel";
	$gIntConvertMore			=	"<br/>To convert more, click <a href=\"./index.php\">here</a>.<br/>";
	$gIntMergeNotEnoughFiles	=	"You selected less that two files. Try again. (Error -5)";
	$gIntMergeNoPDF				=	"This file is no PDF and can not be processed. (Error -42)";
	$gIntMergeOK				=	"Okidoki";
	$gIntMergeNoFiles			=	"No files have been uploaded. Actually, not even the session-upload-folder exists. (Error -23)";
	$gIntMergeCheckingFiles		=	"Checking %d files…<br/>";
	$gIntMergeCheckingFile		=	"Checking \"%s\"…";
	$gIntMergeDownloadHere	 	=	"Download the merged file: <a href=\"%s\">%s</a><br/>";
}
// add other languages here

?>
