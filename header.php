<?php
echo("
<html>
	<head>   
		<meta charset=\"utf-8\">
		<title>$gSiteName</title>
		<link href=\"dropzone.css\" type=\"text/css\" rel=\"stylesheet\" />
		<script src=\"dropzone.min.js\"></script>
");
if (strlen($gCustomHeaderLines)>0) { echo($gCustomHeaderLines); }
echo("
	</head>
	<body>
		<img style=\"float: right; margin: 0px 0px 15px 15px;\" src=\"$gSiteLogo\" height=\"60\" />
		<center><h1>$gSiteName</h1></center>
");
?>
