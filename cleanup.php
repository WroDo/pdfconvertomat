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
$gFailed			=	false;

/* Some Debugging */
say("gSessionID: $gSessionID", __FILE__, __FUNCTION__, __LINE__, 2);
say("_REQUEST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_REQUEST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_POST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_POST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_FILES:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_FILES, __FILE__, __FUNCTION__, __LINE__, 2);

include('cleanupinc.php');


$gCustomHeaderLines="<meta http-equiv=\"refresh\" content=\"0; url=./index.php\" />";
include('header.php'); // insert header incl. <body>-tag
$gCustomHeaderLines="";
?>

	<br/>

	<br/>
	Die temporären Dateien wurden gelöscht.<br/>
	<br/>
	Wenn Du noch mal von vorne beginnen möchtest, klicke <a href="./index.php">hier</a> (automatisch weiter in 0 Sekunden).<br/>

<?php
include("footer.php");
?>

