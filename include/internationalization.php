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
	/* index.php Buttons */
	$gIntButtonMergeLabel		=	"Klicke hier<br/>um die<br/>Dateien zusammenzuf&uuml;gen";
	$gIntButtonCsvLabel			=	"Klicke hier<br/>um in ein CSV zu wandeln<br/>(beta)";
	$gIntButtonShrinkLabel		= 	"Klicke hier<br/>um die PDFs zu verkleinern.";
	$gIntButtonCleanupLabel		=	"Klicke hier<br/>um<br/>den Vorgang abzubrechen";
	/* mergepdf Strings */
	$gIntMergeConvertMore		=	"<br/>Wenn Du noch mehr Dateien konvertieren möchtest, klicke <a href=\"./index.php\">hier</a>.<br/>";
	$gIntMergeNotEnoughFiles	=	"Du hast weniger als zwei Dateien ausgew&auml;hlt. Versuchs nochmal! (Error -5)";
	$gIntMergeNoPDF				=	"Dies ist kein PDF und kann nicht verarbeitet werden. (Error -42)";
	$gIntMergeOK				=	"Okidoki";
	$gIntMergeNoFiles			=	"Es wurden keine Dateien hochgeladen. Nichtmal der session-upload-Ordner existiert. (Error -23)";
	$gIntMergeCheckingFiles		=	"&Uuml;berpr&uuml;fe %d Dateien…<br/>";
	$gIntMergeCheckingFile		=	"&Uuml;berpr&uuml;fe \"%s\"…";
	$gIntMergeDownloadHere		=	"Hier kannst Du das Ergebnis herunterladen: <a href=\"%s\">%s</a><br/>";
	/* pdf2csv Strings */
	$gIntCsvNotEnoughFiles		=	"Keine Datei hochgeladen. Lade bitte genau ein PDF hoch! (Error -666)";
	$gIntCsvTooManyFiles		=	"Bitte lade <b>nur eine Datei</b> hoch!";
	$gIntCsvNoFiles				=	$gIntMergeNoFiles;
	$gIntCsvCheckingFiles		=	$gIntMergeCheckingFiles;
	$gIntCsvCheckingFile		=	$gIntMergeCheckingFile;
	$gIntCsvNoPDF				=	$gIntMergeNoPDF;
	$gIntCsvOK					=	$gIntMergeOK;
	$gIntCsvDownloadHere		=	"Hier kannst Du das Ergebnis herunterladen: <a href=\"%s\" download>%s</a><br/>";
	$gIntCsvConvertMore			=	$gIntMergeConvertMore;
	/* PDF Schrumpfen */
	$gIntShinkConvertMore		=	"<br/>Wenn Du noch mehr Dateien schrumpfen möchtest, klicke <a href=\"./index.php\">hier</a>.<br/>";
	$gIntShinkNotEnoughFiles	=	"Du hast keine Datei ausgew&auml;hlt. Versuchs nochmal! (Error -5)";
	$gIntShinkNoPDF				=	"Dies ist kein PDF und kann nicht verarbeitet werden. (Error -42)";
	$gIntShinkOK				=	"Okidoki";
	$gIntShinkNoFiles			=	"Es wurden keine Dateien hochgeladen. Nichtmal der session-upload-Ordner existiert. (Error -23)";
	$gIntShinkCheckingFiles		=	"&Uuml;berpr&uuml;fe %d Dateien…<br/>";
	$gIntShinkCheckingFile		=	"&Uuml;berpr&uuml;fe \"%s\"…";
	$gIntShinkDownloadHere		=	"Hier kannst Du das Ergebnis herunterladen: <a href=\"%s\">%s</a><br/>";
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
	/* index.php Buttons */
	$gIntButtonMergeLabel		=	"Click here<br/>to merge files";
	$gIntButtonCsvLabel			=	"Click here<br/>to convert to CSV<br/>(beta)";
	$gIntButtonShrinkLabel		= 	"Click here<br/>to reduce filesize.)";
	$gIntButtonCleanupLabel		=	"Click here<br/>to cancel";
	/* mergepdf Strings */
	$gIntMergeConvertMore		=	"<br/>To convert more, click <a href=\"./index.php\">here</a>.<br/>";
	$gIntMergeNotEnoughFiles	=	"You selected less that two files. Try again. (Error -5)";
	$gIntMergeNoPDF				=	"This file is no PDF and can not be processed. (Error -42)";
	$gIntMergeOK				=	"Okidoki";
	$gIntMergeNoFiles			=	"No files have been uploaded. Actually, not even the session-upload-folder exists. (Error -23)";
	$gIntMergeCheckingFiles		=	"Checking %d files…<br/>";
	$gIntMergeCheckingFile		=	"Checking \"%s\"…";
	$gIntMergeDownloadHere	 	=	"Download the merged file: <a href=\"%s\">%s</a><br/>";
	/* pdf2csv Strings */
	$gIntCsvNotEnoughFiles		=	"No files uploaded. Please upload exactly one PDF! (Error -666)";
	$gIntCsvTooManyFiles		=	"Please upload <b>only one file</b>!";
	$gIntCsvNoFiles				=	$gIntMergeNoFiles;
	$gIntCsvCheckingFiles		=	$gIntMergeCheckingFiles;
	$gIntCsvCheckingFile		=	$gIntMergeCheckingFile;
	$gIntCsvNoPDF				=	$gIntMergeNoPDF;
	$gIntCsvOK					=	$gIntMergeOK;
	$gIntCsvDownloadHere		=	"Download the CSV-file: <a href=\"%s\" download>%s</a><br/>";;
	$gIntCsvConvertMore			=	$gIntMergeConvertMore;
	/* PDF Schrumpfen */
	$gIntShinkConvertMore		=	"<br/>To convert more, click <a href=\"./index.php\">here</a>.<br/>";
	$gIntShinkNotEnoughFiles	=	"Du hast keine Datei ausgew&auml;hlt. Versuchs nochmal! (Error -5)";
	$gIntShinkNoPDF				=	"Dies ist kein PDF und kann nicht verarbeitet werden. (Error -42)";
	$gIntShinkOK				=	"Okidoki";
	$gIntShinkNoFiles			=	"Es wurden keine Dateien hochgeladen. Nichtmal der session-upload-Ordner existiert. (Error -23)";
	$gIntShinkCheckingFiles		=	"&Uuml;berpr&uuml;fe %d Dateien…<br/>";
	$gIntShinkCheckingFile		=	"&Uuml;berpr&uuml;fe \"%s\"…";
	$gIntShinkDownloadHere		=	"Hier kannst Du das Ergebnis herunterladen: <a href=\"%s\">%s</a><br/>";
}
// add other languages here

?>
