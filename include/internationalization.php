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
	$gButtonMergeLabel		=	"Klicke hier<br/>um die<br/>Dateien zusammenzuf&uuml;gen";
	$gButtonCleanupLabel	=	"Klicke hier<br/>um<br/>den Vorgang abzubrechen";
	$gConvertMore			=	"<br/>Wenn Du noch mehr Dateien konvertieren möchtest, klicke <a href=\"./index.php\">hier</a>.<br/>";
	$gMergeNotEnoughFules	=	"Du hast weniger als zwei Dateien ausgew&auml;hlt. Versuchs nochmal! (Error -5)";
	$gMergeNoPDF			=	"Dies ist kein PDF und kann nicht verarbeitet werden. (Error -42)";
	$gIntMergeNoFiles		=	"Es wurden keine Dateien hochgeladen. Nichtmal der session-upload-Ordner existiert. (Error -23)";
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
	$gConvertMore			=	"<br/>To convert more, click <a href=\"./index.php\">here</a>.<br/>)";
	$gMergeNotEnoughFules	=	"You selected less that two files. Try again. (Error -5)";
	$gMergeNoPDF			=	"This file is no PDF and can not be processed. (Error -42)";
	$gIntMergeNoFiles		=	"No files have been uploaded. Actually, not even the session-upload-folder exists. (Error -23)";
}
// add other languages here

?>
